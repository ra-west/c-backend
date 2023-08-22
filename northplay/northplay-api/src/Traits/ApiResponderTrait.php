<?php
namespace Northplay\NorthplayApi\Traits;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
trait ApiResponderTrait
{
      public function responder_success($main_data, $cache_data, $function = "undefined") 
      {
         $build_data = [
            "success" => true,
            "data" => $main_data,
            "cache" => $cache_data,
            "server" => [
               "time" => now_nice(),
               "function" => $function,
           ],
         ];

      return response()->json($build_data, 200);
   }

}
