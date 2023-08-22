<?php

namespace Northplay\NorthplayApi\Controllers\Casino\API\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\Support\Facades\Cache;
use Northplay\NorthplayApi\Controllers\Casino\API\CasinoTrait;

class UserRegistrationController extends Controller
{
    use CasinoTrait;
    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */

    public function store(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:50'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        if(!is_alphanumeric($request->name)) {
            abort(400, "Only alphanumeric characters without spaces are allowed for name.");
        }
        
        return $this->create($request->name, $request->email, $request->password);
    }

    public function create($name, $email, $password, $metamask_public_address = NULL)
    {
        $select_email = User::where("email", $email)->first();
        if($select_email) {
            abort(400, "User with this email already exist.");
        }
        $select_name = User::where("name", $name)->first();

        if($select_name) {
            abort(400, "User with this name already exist.");
        }
        
        $user = User::create([
            'name' => $name,
            'email' => $email,
            'password' => Hash::make($password),
            'metamask_address' => $metamask_public_address,
        ]);

        if($metamask_public_address !== NULL) {
            $metamask_message = "You can change the random nickname (".$name.") we've assigned to you. Also, you can set and verify an e-mail address to add extra recovery options";
            $this->sendNotification(
                $user->id,
                "Metamask Registration",
                $metamask_message,
                $metamask_message,
                "account",
                "/user/profile"
            );
        } else {
            $this->sendNotification(
                $user->id,
                "Email Verification",
                "Please make sure to verify your e-mail in order to be able to recover your e-mail.",
                "Please make sure to verify your e-mail in order to be able to recover your e-mail.",
                "account",
                "/user/profile/verify-email"
            );
        }



        event(new Registered($user));
        $auth_controller = new \Northplay\NorthplayApi\Controllers\Casino\API\Auth\AuthenticatedSessionController;
        return $auth_controller->authenticate_and_response($user);
    }






    
}
