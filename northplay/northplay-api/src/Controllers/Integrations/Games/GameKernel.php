<?php
namespace Northplay\NorthplayApi\Controllers\Integrations\Games;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Client\Response;
use Illuminate\Http\Request;

class GameKernel
{
		public function providers()
		{
				return array(
						array(
							"id" => "mascot",
							"active" => true,
							"provider_name" => "Mascot Games",
						),
						array(
							"id" => "bgaming",
							"active" => true,
							"provider_name" => "BGaming",
						),
						array(
							"id" => "ryangames",
							"active" => true,
							"provider_name" => "Ryan Games",
						),
						array(
							"id" => "amatic",
							"active" => true,
							"provider_name" => "Amatic",
						),
						array(
							"id" => "netent",
							"active" => true,
							"provider_name" => "Netent",
						),
						array(
							"id" => "novomatic",
							"active" => true,
							"provider_name" => "Novomatic",
						),
						array(
							"id" => "wazdan",
							"active" => true,
							"provider_name" => "Wazdan",
						),
						array(
							"id" => "egt",
							"active" => true,
							"provider_name" => "EGT",
						),
						array(
							"id" => "pragmatic",
							"active" => true,
							"provider_name" => "Pragmatic Play",
						),
					);
		}

		public function create_session($provider, $session_id)
		{
				if($provider === "mascot") {
					$mascot_controller = new \Northplay\NorthplayApi\Controllers\Integrations\Games\Mascot\MascotKernel;
					return $mascot_controller->create_session($session_id);
				}
				if($provider === "amatic" || $provider === "netent" || $provider === "pragmatic" || $provider === "novomatic" || $provider === "egt" || $provider === "wazdan") {
					$amatic_controller = new \Northplay\NorthplayApi\Controllers\Integrations\Games\Amatic\AmaticKernel;
					return $amatic_controller->create_session($session_id);
				}
				if($provider === "bgaming" || $provider === "ryangames") {
					$bgaming_controller = new \Northplay\NorthplayApi\Controllers\Integrations\Games\Bgaming\BgamingKernel;
					return $bgaming_controller->create_session($session_id);
				}
				
				/*
				if($provider === "pragmaticplay") {
					$pragmaticplay_controller = new \Northplay\NorthplayApi\Controllers\Integrations\Games\Pragmaticplay\PragmaticplayKernel;
					return $pragmaticplay_controller->create_session($session_id);
				}
				*/
		}
	}