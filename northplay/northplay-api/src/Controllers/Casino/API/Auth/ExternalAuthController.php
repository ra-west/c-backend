<?php

namespace Northplay\NorthplayApi\Controllers\Casino\API\Auth;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Northplay\NorthplayApi\Controllers\Casino\WebsocketController;

class ExternalAuthController extends Controller
{
      public function __construct()
      {
         $this->client = new WebsocketController;
         $this->middleware(['web', 'auth']);
         $this->external_auth = new \Northplay\NorthplayApi\Models\UserExternalAuthModel;
      }

      public function insert($user_id, $token, $type, $external_id = NULL) {
         $this->external_auth->insert([
            "user_id" => $user_id,
            "auth_key" => $token,
            "external_id" => $external_id,
            "type" => $type,
            "created_at" => now_nice(),
            "updated_at" => now_nice(),
         ]);
      }

      public function websocket_details(Request $request) {
         $user = $request->user();
         $websocket_auth = $this->retrieve_websocket_connection_key($user->id, "websocket_auth");
         $websocket_auth_channel = $this->retrieve_websocket_userchannel($user->id, "websocket_auth_channel");


         $response_data = array(
            "auth" => array(
               "auth_key" => $websocket_auth['auth_key'],
               "external_id" => $websocket_auth['external_id'],
            ),
            "auth_channel" => array(
               "auth_key" => $websocket_auth_channel['auth_key'],
               "external_id" => $websocket_auth_channel['external_id'],
            )
         );
         return $response_data;
      }

      /**
      * Retrieve authenticated key (for websocket e.g.)
      *
      * @return \Illuminate\Http\JsonResponse
      */ 
      public function retrieve_websocket_connection_key($user_id, $type) {
         $auth_key = $this->external_auth->where("type", $type)->where("user_id", $user_id)->first();
         if(!$auth_key) {
            $token = $this->client->connection_token($user_id);
            $this->insert($user_id, $token, $type);
            $auth_key = $this->external_auth->where("type", $type)->where("user_id", $user_id)->first();
         }
         return $auth_key;
      }

      /**
      * Retrieve authenticated channel (for websocket e.g.)
      *
      * @return \Illuminate\Http\JsonResponse
      */ 
      public function retrieve_websocket_userchannel($user_id, $type) {
         $auth_channel = $this->external_auth->where("type", $type)->where("user_id", $user_id)->first();
         if(!$auth_channel) {
            $channel_id = $this->client->auth_channel_name($user_id);
            $subscribe_token = $this->client->subscribe_token($user_id, $channel_id);
            $this->insert($user_id, $subscribe_token, $type, $channel_id);
            $auth_channel = $this->external_auth->where("type", $type)->where("user_id", $user_id)->first();
         }
         return $auth_channel;
      }

}
