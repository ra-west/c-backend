<?php

namespace Northplay\NorthplayApi\Controllers\Casino\API\Auth;

use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use DB;
use Illuminate\Support\Facades\Cache;

class UserEmailController extends Controller
{
    /**
     * Send a new email verification notification.
     */
    public function store(Request $request): JsonResponse|RedirectResponse
    {
        $request->validate([
            'email' => ['required', 'email'],
        ]);
        if ($request->user()) {
            $new_email = strtolower($request->email);
            $get_time_lock = Cache::get("timer_user_emailupdate_".$request->user()->id);
            if($get_time_lock) {
                $seconds_left = $get_time_lock - time();
                    return response()->json(['status' => 'recently-changed-timeout']);
            }
            $already_existing = \App\Models\User::where('email', $new_email)->first();
            if($already_existing) {
                return response()->json(['status' => 'new-email-used-already']);
            }
            DB::table('users')->where("id", $request->user()->id)->update([
                "email" => $new_email,
                "email_verified_at" => null,
             ]);

            Cache::put("timer_user_emailupdate_".$request->user()->id, time(), 15);
            return response()->json(['status' => 'email-changed']);

        } else {
            return response()->json(['status' => 'not-authenticated']);
        }

    }
}
