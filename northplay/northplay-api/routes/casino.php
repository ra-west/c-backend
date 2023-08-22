<?php

use Northplay\NorthplayApi\Controllers\Casino\API\Auth\AuthenticatedSessionController;
use Northplay\NorthplayApi\Controllers\Casino\API\Auth\EmailVerificationNotificationController;
use Northplay\NorthplayApi\Controllers\Casino\API\Auth\NewPasswordController;
use Northplay\NorthplayApi\Controllers\Casino\API\Auth\PasswordResetLinkController;
use Northplay\NorthplayApi\Controllers\Casino\API\Auth\UserRegistrationController;
use Northplay\NorthplayApi\Controllers\Casino\API\Auth\UserEmailVerifyController;
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use Northplay\NorthplayApi\Controllers\Casino\API\Auth\UserEmailController;
use Northplay\NorthplayApi\Controllers\Casino\API\MetadataController;
use Northplay\NorthplayApi\Controllers\Casino\API\Auth\UserAuthController;
use Northplay\NorthplayApi\Controllers\Casino\API\GamedataController;
use Northplay\NorthplayApi\Controllers\Casino\API\Auth\CasinoGameController;
use Northplay\NorthplayApi\Controllers\Casino\API\Auth\UserNotificationsController;
use Northplay\NorthplayApi\Controllers\Casino\API\Auth\PaymentDepositController;
use Northplay\NorthplayApi\Controllers\Casino\API\PaymentCallbackController;
use Northplay\NorthplayApi\Controllers\Casino\API\Auth\Web3LoginController;
use App\Http\Controllers\CentrifugoProxyController;
use Northplay\NorthplayApi\Controllers\Integrations\Games\Amatic\AmaticKernel;
use Illuminate\Support\Facades\Cache;
use Northplay\NorthplayApi\Controllers\Casino\RecentGamesController;
use Northplay\NorthplayApi\Controllers\Casino\API\GameInfoController;
use Northplay\NorthplayApi\Controllers\Casino\API\Auth\UserVipController;

    Route::get('/casino/auth/user', [UserAuthController::class, 'me'])
                ->middleware(['web', 'auth'])
                ->name('casino.get.meta');
    Route::get('/casino/data/vip-levels', [RecentGamesController::class, 'show_history'])->middleware(['web'])->name('casino.get.vip_levels');
    Route::get('/casino/data/recent-games', [RecentGamesController::class, 'show_history'])->middleware(['web'])->name('casino.get.recent-games');
    Route::get('/casino/data/games-row', [GamedataController::class, 'retrieve'])->middleware(['web'])->name('casino.get.gamedata');
    Route::get('/casino/data/game-info', [GameInfoController::class, 'retrieve'])->middleware(['web'])->name('casino.get.gameinfo');

    Route::get('/casino/auth/payment/generateAddress', [PaymentDepositController::class, 'generateAddress'])
                    ->middleware(['web', 'auth:sanctum'])
                    ->name('payment.generateaddress');

    Route::get('/casino/callbacks/cryptapi', [PaymentCallbackController::class, 'cryptapi'])
                    ->middleware(['web'])
                    ->name('payment.callback.cryptapi');

    Route::post('/casino/callbacks/gapilol', [AmaticKernel::class, 'callbacks'])
                    ->middleware(['web'])
                    ->name('payment.callback.gapilol');
                    
    Route::get('/casino/auth/payment/deposit', [PaymentDepositController::class, 'retrieve'])
                    ->middleware(['web', 'auth'])
                    ->name('payment.deposit');

    Route::post('/casino/auth/register', [UserRegistrationController::class, 'store'])
                    ->middleware(['web', 'guest'])
                    ->name('register');

    Route::post('/casino/auth/metamask/nonce', [Web3LoginController::class, 'generateNonce'])
                    ->middleware(['web'])
                    ->name('nonce');
                    
    Route::post('/casino/auth/metamask/login', [Web3LoginController::class, 'web3login'])
                    ->middleware(['web', 'guest'])
                    ->name('web3login');
                    
    Route::post('/casino/auth/login', [AuthenticatedSessionController::class, 'store'])
                    ->middleware(['web', 'guest'])
                        ->name('login');

    Route::post('/casino/auth/forgot-password', [PasswordResetLinkController::class, 'store'])
                    ->middleware(['web', 'guest'])
                    ->name('password.email');

    Route::post('/casino/auth/change-password', [PasswordResetLinkController::class, 'store'])
                    ->middleware(['web', 'auth'])
                    ->name('password.change.email');
                    
    Route::get('/casino/auth/notifications/all', [UserNotificationsController::class, 'all'])
                    ->middleware(['web', 'auth'])
                    ->name('casino.notifications');

    Route::get('/casino/auth/start-game', [CasinoGameController::class, 'retrieve'])
                    ->middleware(['web'])
                    ->name('casino.start_game');

    Route::post('/casino/auth/reset-password', [NewPasswordController::class, 'store'])
                    ->middleware(['web'])
                    ->name('password.store');

    Route::post('/casino/auth/email/update-email', [UserEmailController::class, 'store'])
                    ->middleware(['web', 'auth'])
                    ->name('update_email');

    Route::get('/casino/auth/verify-email/{id}/{hash}', UserEmailVerifyController::class)
                    ->middleware(['web', 'auth', 'signed', 'throttle:6,1'])
                    ->name('verification.verify');

    Route::post('/casino/auth/email/verification-notification', [EmailVerificationNotificationController::class, 'store'])
                    ->middleware(['web', 'auth', 'throttle:6,1'])
                    ->name('verification.send');

    Route::post('/casino/auth/logout', [AuthenticatedSessionController::class, 'destroy'])
                    ->middleware(['web', 'auth'])
                    ->name('logout');
