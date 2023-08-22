<?php

namespace Northplay\NorthplayApi\Controllers\Casino\API\Auth;

use App\Http\Controllers\Controller;
use Northplay\NorthplayApi\Controllers\Casino\API\Auth\LoginRequest;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Http\JsonResponse;

class AuthenticatedSessionController extends Controller
{

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): JsonResponse
    {
        $request->authenticate();
        $request->session()->regenerate();

        return $this->response_succesful_login();
    }

    public function response_succesful_login(): JsonResponse
    {
        $success_response = array(
            "success" => true,
            "message" => "Login succesful",
        );

        return response()->json(
            $success_response, 200
        );
    }
    

	public function authenticate_and_response($user)
	{
			Auth::login($user);
            return $this->response_succesful_login();
	}
    

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): JsonResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();
        $success_response = array(
            "success" => true,
            "message" => "Logout succesful",
        );

        return response()->json(
            $success_response, 200
        );
    }

    
    
}