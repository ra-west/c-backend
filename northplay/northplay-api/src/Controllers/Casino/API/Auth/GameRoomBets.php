<?php

namespace Northplay\NorthplayApi\Controllers\Casino\API\Auth;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Northplay\NorthplayApi\Controllers\Integrations\GatewayTrait;

class CasinoGameController extends Controller
{
    use GatewayTrait;

      public function __construct()
      {
         $this->middleware(['web', 'auth']);
      }

      public function place_bet(Request $request) 
      {

        $user_id = $request->user()->id;
        
      }