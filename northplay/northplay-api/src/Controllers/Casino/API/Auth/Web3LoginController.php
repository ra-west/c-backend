<?php

namespace Northplay\NorthplayApi\Controllers\Casino\API\Auth;

use Illuminate\Http\Request;
use kornrunner\Keccak;
use Elliptic\EC;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Cache;
use Illuminate\Http\Response;
use App\Models\User;

class Web3LoginController extends \App\Http\Controllers\Controller
{

    public function generateNonce(Request $request)
    {
        $user_model = new \App\Models\User;
        $public_address = $request->publicAddress;
        
        $nonce = Cache::get("metamask_nonce_".$public_address);

        if(!$nonce) {
            $nonce = md5($request->publicAddress.Str::random());
            Cache::set("metamask_nonce_".$public_address, $nonce, now()->addMinutes(15));
        }

        $response_data = array(
            "nonce" => $nonce
        );
        
        return response()->json($response_data, 200);
    }

    public function web3login(Request $request): string
    {

        $public_address = $request->publicAddress;
        $signed_nonce = $request->signedNonce;
        $nonce = Cache::get("metamask_nonce_".$public_address);
        if(!$nonce) {
            abort(400, "No saved nonce found for specified public address.");
        } else {
            Cache::pull("metamask_nonce_".$public_address);
        }

        $verifySign = $this->verifySignature(
            $nonce,
            $signed_nonce, 
            $public_address
        );

        if($verifySign) {
            $select_user = User::where("metamask_address", $public_address)->first();
            if(!$select_user) {
                return $this->create_account($public_address);
            } else {
                $auth_controller = new \Northplay\NorthplayApi\Controllers\Casino\API\Auth\AuthenticatedSessionController;
                return $auth_controller->authenticate_and_response($select_user);
            }
            
        } else {
            abort(400, "Signed message is incorrect.");
        }
    }

    public function generate_unique_name()
    {
            $name = random_first_name().'#'.rand(1000, 999999);
            $select_user = \App\Models\User::where("name", $name)->first();
            if($select_user) {
                return $this->generate_unique_name();
            } else {
                return $name;
            }
    }

    public function create_account($public_address)
    {
            $register_controller = new UserRegistrationController;
            $name = $this->generate_unique_name();
            $email = $public_address."@web3_".rand(1000, 999999999);
            return $register_controller->create($name, $email, Str::random(32), $public_address);
    }

    public function message(): string
    {
        $nonce = Str::random();
        $message = "Sign this message to confirm you own this wallet address. This action will not cost any gas fees.\n\nNonce: " . $nonce;

        session()->put('sign_message', $message);

        return $message;
    }

    public function verify(Request $request): string
    {
        $result = $this->verifySignature(session()->pull('sign_message'), $request->input('signature'), $request->input('address'));
        // If $result is true, perform additional logic like logging the user in, or by creating an account if one doesn't exist based on the Ethereum address
        return ($result ? 'OK' : 'ERROR');
    }

    protected function verifySignature(string $message, string $signature, string $address): bool
    {
        $hash = Keccak::hash(sprintf("\x19Ethereum Signed Message:\n%s%s", strlen($message), $message), 256);
        $sign = [
            'r' => substr($signature, 2, 64),
            's' => substr($signature, 66, 64),
        ];
        $recid = ord(hex2bin(substr($signature, 130, 2))) - 27;

        if ($recid != ($recid & 1)) {
            return false;
        }

        $pubkey = (new \Elliptic\EC('secp256k1'))->recoverPubKey($hash, $sign, $recid);
        $derived_address = '0x' . substr(Keccak::hash(substr(hex2bin($pubkey->encode('hex')), 1), 256), 24);

        return (Str::lower($address) === $derived_address);
    }
}