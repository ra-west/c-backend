<?php

namespace Northplay\NorthplayApi\Controllers\Casino\API\Auth;
use App\Http\Controllers\Controller;
use App\Models\User;

class UserVipController extends Controller
{
	use \Northplay\NorthplayApi\Controllers\Casino\API\CasinoTrait;

	public function __construct()
	{
		$this->user_model = new \App\Models\User;
	}

	public function get_balance($user_id, $sym) {
		return $this->get_user_balance($user_id, $sym);
	}
	
	public function user_vip_check($user_id) {
		$select_user = $this->user_model->where("id", $user_id)->first();

		$current_vip_level = $this->user_storage_get($user_id, "vip_level");
		save_log("viplevel", $current_vip_level);
		if(!$current_vip_level) {
			$set_current_vip = $this->user_storage_set($user_id, "vip_level", 0, "vip", 0);
			$current_vip_level = 0;
		} else {
			$current_vip_level = $current_vip_level['storage_value'];
		}
		$next_vip_level = $this->levels()->where("id", $current_vip_level+1)->first();
		$next_vip_points_required = $next_vip_level['points'];
		$current_vip_points = $select_user->vip_points;
		if($current_vip_points > $next_vip_points_required) {
			$set_current_vip = $this->user_storage_set($user_id, "vip_level", ($current_vip_level+1), "vip", 0);
			$this->sendNotification(
				$user_id,
				"Loyalty Level Increased",
				"Your Loyalty level has been increased. You have a reward waiting for you",
				"Your Loyalty level has been increased. You have a reward waiting for you",
				"bonus",
				"none"
			);
		}
	}

	public function levels() {
			return collect(config('northplay-api.loyalty_levels'));
	
	}
}