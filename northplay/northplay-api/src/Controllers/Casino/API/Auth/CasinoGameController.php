<?php

namespace Northplay\NorthplayApi\Controllers\Casino\API\Auth;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Northplay\NorthplayApi\Controllers\Integrations\GatewayTrait;
use Illuminate\Support\Facades\Cache;
use Northplay\NorthplayApi\Controllers\Casino\API\CasinoTrait;

class CasinoGameController extends Controller
{
    use GatewayTrait, CasinoTrait;

      public function __construct()
      {
         $this->middleware(['web']);
         $this->entry_controller = new \Northplay\NorthplayApi\Controllers\Integrations\EntryController;
         $this->games_model = collect($this->gamelist());
         
         $this->currency = null;
         $this->slug = null;
         $this->mode = null;
         $this->game_id = null;
         $this->game_data = null;
         $this->debit_currency = null;
         $this->preloader_theme = null;
         $this->user_id = null;

      }

      public function settings($key) {
        $default_category = "session_settings";

        if($key === "currency_mode") {
            $default_setting = json_encode(array("REAL", "DEMO"));
        }
        if($key === "currency_require_login") {
            $default_setting = json_encode(array("USD", "EUR"));
        }
        if($key === "play_currency") {
            $default_setting = json_encode(array("USD", "EUR", "DEMO"));
        }
        if($key === "preloader_themes") {
            $default_setting = json_encode(array("BLACK", "DARKBLUE"));
        }

        $get_config = $this->get_config_value("session_setting_".$key, $default_setting, $default_category);
        return json_decode($get_config, true);

      }

      public function validate_request(Request $request)
      {
            validate([
                'slug' => ['required', 'string', 'min:3', 'max:100'],
                'currency' => ['required', 'string', 'max:15'],
                'mode' => ['required', 'string', 'max:5'],
                'debit_currency' => ['required', 'string', 'max:15'],
                'preloader_theme' => ['string', 'max:15'],
            ], $request->all());

            $this->mode = strtoupper($request->mode);

            if(!in_array($this->mode, $this->settings("currency_mode"))) {
                abort(400, "Mode you entered is not accepted");
            }
            $this->currency = strtoupper($request->currency);

            if(auth()->guest()) {
                if(!in_array($this->currency, $this->settings("currency_require_login"))) {
                    abort(403, "Please login to play games.");
                }
            }

            if(!in_array($this->currency, $this->settings("play_currency"))) {
                abort(400, "Currency not accepted.");
            }

            $this->slug = $request->slug;
            $this->game_data = $this->games_model->where("slug", $this->slug)->first();
            if(!$this->game_data) {
                abort(400, "Game not found.");
            }


            if(auth()->user()) {
                $this->user_id = auth()->user()->id;
            }

            $this->game_id = $this->game_data->id;
            
            $preloader_settings = $this->settings("preloader_themes");
            $this->preloader_theme = $preloader_settings[0];
            if($request->preloader_theme) {
                if(in_array($request->preloader_theme, $preloader_settings)) {
                    $this->preloader_theme = strtolower($request->preloader_theme);
                 }
            }

            $this->debit_currency = $request->debit_currency;
    }

      public function retrieve(Request $request)
      {
            $this->validate_request($request);
            $cache_lock_id = $this->user_id !== 0 ? $this->user_id : $request->ip;
            $cache_lock = Cache::get($cache_lock_id.$this->game_id.'_cache_lock');
            if(!$cache_lock) {
                $entry_token = $this->entry_controller->create_entry_session($this->user_id, strtolower($this->mode), $this->game_id, $this->currency, $this->debit_currency);
                $data = [
                    "success" => true,
                    "session_url" => app_url()."/game-gateway/entry?entry_token=".$entry_token."&preloader_theme=".$this->preloader_theme,
                    "session_details" => array(
                        "preloader_theme" => $this->preloader_theme,
                        "currency" => $this->currency,
                        "mode" => $this->mode,
                        "preloader_theme" => $this->preloader_theme,
                    ),
                    "game_details" => $this->game_data,

                ];
                if($this->user_id) {
                    Cache::set($cache_lock_id.$this->game_id.'_cache_lock', $data, now()->addSeconds(3));
                }
            } else {
                $data = $cache_lock;
            }
            
            return response()->json($data, 200);
            
    
      }
}