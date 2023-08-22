<?php

namespace Northplay\NorthplayApi\Controllers\Casino\API\Auth;

use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use Illuminate\Auth\Events\Verified;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\RedirectResponse;
use Northplay\NorthplayApi\Models\UserNotificationsModel;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class UserNotificationsController extends Controller
{
		protected $notifications;
		
		public function __construct()
		{
				$this->notifications = new UserNotificationsModel;
			
			
		}
    public function all(Request $request)
		{
			$user_id = $request->user()->id;
			$notifications = $this->notifications->where("user_id", $user_id)->orderBy('created_at', 'desc')->get();
			$this->notifications->where("read_at", null)->where("user_id", $user_id)->update([
				"read_at" => now()
			]);			

			$notifications_count = $notifications->count();
			
			$notification_data = [
				 "notifications" => $notifications,
				 "notifications_count" => $notifications_count,
			];
			
			$data = [
				 "success" => true,
				 "data" => $notification_data,
			];
			
			return response()->json($data, 200);
		}
		
    public function recent($user_id)
		{
			$count = $this->notifications->where("user_id", $user_id)->count();
			$latest = [];
			$unread = [];
			if($count > 0) {
				$recent = $this->notifications->where("user_id", $user_id)->orderBy('created_at', 'desc')->get();
				$latest = $recent->first();
				$unread = $recent->where("read_at", null)->count();

			}
			
			$data = [
				"latest" => $latest,
				"unread" => $unread,
			];
			return $data;
		}
		
		public function send($user_id, $title, $short_message, $long_message, $type, $action)
		{
			$notification_data = [
					"user_id" => $user_id,
					"title" => $title,
					"short_message" => $short_message,
					"long_message" => $long_message,
					"type" => $type,
					"action" => $action,
					"read_at" => null,
					"created_at" => now(),
					"updated_at" => now(),
			];
			
			$notification_transaction = $this->notifications->insert($notification_data);
			return $notification_transaction;
		}
}