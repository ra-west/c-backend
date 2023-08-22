<?php

namespace Northplay\NorthplayApi;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use Northplay\NorthplayApi\Commands\NorthplayApiCommand;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Cache;
use Spatie\LaravelPackageTools\Commands\InstallCommand;
use Northplay\NorthplayApi\Commands\NorthplayTestCacheDriver;
use Northplay\NorthplayApi\Commands\NorthplaySeederCommand;
use Northplay\NorthplayApi\Commands\ScoobiedogGamesImport;
use Northplay\NorthplayApi\Commands\NorthplayTestPoker;
use Northplay\NorthplayApi\Commands\NorthplayForceExchangeRateUpdate;
use Northplay\NorthplayApi\Commands\NorthplayWebsocketMessage;
use Northplay\NorthplayApi\Commands\NorthplayWebsocketChannelInfo;
use Northplay\NorthplayApi\Commands\NorthplayAddBalance;
use Northplay\NorthplayApi\Commands\GatewayImportSoftswiss;
use Northplay\NorthplayApi\Commands\GatewayProcessSoftswiss;
use Northplay\NorthplayApi\Commands\GatewayTransferGames;
use Northplay\NorthplayApi\Commands\GatewayImportGapi;
use Northplay\NorthplayApi\Commands\GatewayAddGame;
use Northplay\NorthplayApi\Commands\GatewayToggleGame;
use Northplay\NorthplayApi\Commands\GatewayGamesBackup;

class NorthplayApiServiceProvider extends PackageServiceProvider
{
    
    /**
     * configurePackage
     *
     * @param  mixed $package
     * @return void
     */
    public function configurePackage(Package $package): void
    {
        //SERVICE PROVIDER -PRODUCTION-
        $package
            ->name('northplay-api')
            ->hasConfigFile()
            ->hasRoutes(['api', 'web'])
            ->hasViews('northplay')
            ->hasMigrations(['create_payment_transactions', 'create_gamebuffer', 'create_cryptapi', 'modify_users', 'create_user_balances', 'create_game_rooms', 'create_user_storage', 'create_user_notifications','create_user_balances_transactions', 'create_gateway_entry_sessions', 'create_softswiss_game_tag_table', 'create_softswiss_game_table', 'create_user_external_auth', 'create_currency', 'create_config', 'create_email_log', 'create_gateway_parent_sessions', 'create_datalogger', 'create_games_results', 'create_games', 'create_mp_groups', 'create_mp_rooms'])
            ->hasCommands(GatewayImportGapi::class, GatewayGamesBackup::class, GatewayToggleGame::class, GatewayAddGame::class, NorthplayTestCacheDriver::class, GatewayTransferGames::class, GatewayImportSoftswiss::class, GatewayProcessSoftswiss::class, NorthplayAddBalance::class, NorthplayWebsocketChannelInfo::class, NorthplayWebsocketMessage::class, NorthplayTestPoker::class, ScoobiedogGamesImport::class, NorthplayForceExchangeRateUpdate::class, NorthplaySeederCommand::class, NorthplayApiCommand::class)
            ->hasInstallCommand(function(InstallCommand $command) {
                $command
                    ->publishConfigFile()
                    ->publishAssets()
                    ->publishMigrations()
                    ->askToRunMigrations()
                    ->copyAndRegisterServiceProviderInApp()
                    ->endWith(function(InstallCommand $command) {
                    $install_controller = new \Northplay\NorthplayApi\Controllers\InstallController;
                    $install_controller->install();
                    $command->info('Seeded database');
                });
            });
            $this->loadMiddlewares();
    }

    /**
     * install_config_seed
     * Seeds config values to database from config/northplay-api.php
     *
     * @return void
     */
    public function install_config_seed() {
            $db_seed = config('northplay-api.db_seed');
            foreach($db_seed as $key=>$config_category) {
                    foreach($config_category as $subkey=>$sub_value) {
                            \Northplay\NorthplayApi\Models\ConfigModel::insert([
                                    "key" => $subkey,
                                    "value" => $sub_value,
                                    "category" => $key,
                                    "created_at" => now(),
                                    "updated_at" => now(),
                                    "extra_data" => "[]",
                            ]);
                        echo $key.".".$subkey."=".$sub_value;
                        echo "\n";
                    }
            }
    }


    /**
     * loadMiddlewares
     * Add any middlewares to be loaded
     *
     * @return void
     */
    public function loadApiResponses() {
        Response::macro('errorApi', function ($value, $code) {
            return Response::json(array('data' => $value, 'status' => 'error', 'code' => $code), $code);
        });
        Response::macro('successApi', function ($value, $code) {
            return Response::json(array('data' => $value, 'status' => 'success', 'code' => $code), $code);
        });
    }


    /**
     * loadMiddlewares
     * Add any middlewares to be loaded
     *
     * @return void
     */
    public function loadMiddlewares() {
        $kernel = app(\Illuminate\Contracts\Http\Kernel::class);
        $kernel->pushMiddleware(\Northplay\NorthplayApi\Middleware\GateKeeper::class);
    }
}
