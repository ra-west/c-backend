<?php
namespace Northplay\NorthplayApi\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

class EverCookie
{

	public function __construct()
	{
			$this->cookie_prefix = config("northplay-api.evercookie.name");
			//cookie names for each method
	}

	public function fp(Request $request)
	{
			return view('northplay::evercookie-fingerprint');
	}

	public function stream() 
	{
			$options = [
				"fp_iframe_url" => "https://casinoapi.northplay.me/northplay/ever/fp",

			];
			return view('northplay::evercookie-stream')->with("options", $options);
	}

	public function show() 
	{
			$evercookie_data = [
				'auth' => true,
				'user_id' => 1,
				'type' => "get",
				'random_cookie' => self::v4(),
			];

			return view('northplay::evercookie-check')->with('options', $evercookie_data);
	}


	public function cookie_name($method, Request $request)
	{
			if($request->name) {
					$request->validate([
						'name' => 'string|min:1|max:255',
						]);
					return $request->name;
			}
			return $this->cookie_prefix.$method;
	}

	public static function v4() 
	{
			return sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
	
			// 32 bits for "time_low"
			mt_rand(0, 0xffff), mt_rand(0, 0xffff),
	
			// 16 bits for "time_mid"
			mt_rand(0, 0xffff),
	
			// 16 bits for "time_hi_and_version",
			// four most significant bits holds version number 4
			mt_rand(0, 0x0fff) | 0x4000,
	
			// 16 bits, 8 bits for "clk_seq_hi_res",
			// 8 bits for "clk_seq_low",
			// two most significant bits holds zero and one for variant DCE1.1
			mt_rand(0, 0x3fff) | 0x8000,
	
			// 48 bits for "node"
			mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
			);
		}

		public function etag(Request $request) 
		{
				$cookie_name = $this->cookie_name("etag", $request);
				// we don't have a cookie, so we're not setting it
				if (empty($_COOKIE[$cookie_name])) {
						// read our etag and pass back
						if (!function_exists('apache_request_headers')) {
								function apache_request_headers() {
										// Source: http://www.php.net/manual/en/function.apache-request-headers.php#70810
										$arh = array();
										$rx_http = '/\AHTTP_/';
										foreach ($_SERVER as $key => $val) {
												if (preg_match($rx_http, $key)) {
														$arh_key = preg_replace($rx_http, '', $key);
														$rx_matches = array();
														// do some nasty string manipulations to restore the original letter case
														// this should work in most cases
														$rx_matches = explode('_', $arh_key);
														if (count($rx_matches) > 0 and strlen($arh_key) > 2) {
																foreach ($rx_matches as $ak_key => $ak_val) {
																		$rx_matches[$ak_key] = ucfirst(strtolower($ak_val));
																}
																$arh_key = implode('-', $rx_matches);
														}
														$arh[$arh_key] = $val;
												}
										}
										return ($arh);
								}
						}
				
						// Headers might have different letter case depending on the web server.
						// So, change all headers to uppercase and compare it.
						$headers = array_change_key_case(apache_request_headers(), CASE_UPPER);
						if(isset($headers['IF-NONE-MATCH'])) {
								// extracting value from ETag presented format (which may be prepended by Weak validator modifier)
								$etag_value = preg_replace('|^(W/)?"(.+)"$|', '$2', $headers['IF-NONE-MATCH']);
								set_header('HTTP/1.1 304 Not Modified');
								header('ETag: "' . $etag_value . '"');
								echo $etag_value;
						}
						exit;
				}
				
				// set our etag
				header('ETag: "' . $_COOKIE[$cookie_name] . '"');
				echo $_COOKIE[$cookie_name];	
		}


		public function cache(Request $request) 
		{
			$cookie_name = $this->cookie_name("cache", $request);
			if (empty($_COOKIE[$cookie_name])) {
				header('HTTP/1.1 304 Not Modified');
				exit;
			}

			header('Content-Type: text/html');
			header('Last-Modified: Wed, 30 Jun 2010 21:36:48 GMT');
			header('Expires: Tue, 31 Dec 2030 23:30:45 GMT');
			header('Cache-Control: private, max-age=630720000');

			echo $_COOKIE[$cookie_name];
		}

		public function png(Request $request)
		{
			$cookie_name = $this->cookie_name("png", $request);

			// we don't have a cookie, user probably deleted it, force cache
			if (empty($_COOKIE[$cookie_name])) {
				header('HTTP/1.1 304 Not Modified');
				exit;
			}

			// width of 200 means 600 bytes (3 RGB bytes per pixel)
			$x = 200;
			$y = 1;

			$gd = imagecreatetruecolor($x, $y);

			$data_arr = str_split($_COOKIE[$cookie_name]);

			$x = 0;
			$y = 0;
			for ($i = 0, $i_count = count($data_arr); $i < $i_count; $i += 3) {
				$red   = isset($data_arr[$i])   ? ord($data_arr[$i])   : 0;
				$green = isset($data_arr[$i+1]) ? ord($data_arr[$i+1]) : 0;
				$blue  = isset($data_arr[$i+2]) ? ord($data_arr[$i+2]) : 0;
				$color = imagecolorallocate($gd, $red, $green, $blue);
				imagesetpixel($gd, $x++, $y, $color);
			}

			header('Content-Type: image/png');
			header('Last-Modified: Wed, 30 Jun 2012 21:36:48 GMT');
			header('Expires: Tue, 31 Dec 2030 23:30:45 GMT');
			header('Cache-Control: private, max-age=630720000');

			// boom. headshot.
			imagepng($gd);
		}


		public function digest(Request $request)
		{
			$realm = 'Restricted area';

			//user => password
			$users = array('admin' => 'mypass', 'guest' => 'guest');
			
			
			if (empty($_SERVER['PHP_AUTH_DIGEST'])) {
					header('HTTP/1.1 401 Unauthorized');
					header('WWW-Authenticate: Digest realm="'.$realm.
								 '",qop="auth",nonce="'.uniqid().'",opaque="'.md5($realm).'"');
			
					die('Text to send if user hits Cancel button');
			}
			
			
			// analyze the PHP_AUTH_DIGEST variable
			if (!($data = http_digest_parse($_SERVER['PHP_AUTH_DIGEST'])) ||
					!isset($users[$data['username']]))
					die('Wrong Credentials!');
			
			
			// generate the valid response
			$A1 = md5($data['username'] . ':' . $realm . ':' . $users[$data['username']]);
			$A2 = md5($_SERVER['REQUEST_METHOD'].':'.$data['uri']);
			$valid_response = md5($A1.':'.$data['nonce'].':'.$data['nc'].':'.$data['cnonce'].':'.$data['qop'].':'.$A2);
			
			if ($data['response'] != $valid_response)
					die('Wrong Credentials!');
			
			// ok, valid username & password
			echo 'You are logged in as: ' . $data['username'];
			
			
			// function to parse the http auth header
			function http_digest_parse($txt)
			{
					// protect against missing data
					$needed_parts = array('nonce'=>1, 'nc'=>1, 'cnonce'=>1, 'qop'=>1, 'username'=>1, 'uri'=>1, 'response'=>1);
					$data = array();
					$keys = implode('|', array_keys($needed_parts));
			
					preg_match_all('@(' . $keys . ')=(?:([\'"])([^\2]+?)\2|([^\s,]+))@', $txt, $matches, PREG_SET_ORDER);
			
					foreach ($matches as $m) {
							$data[$m[1]] = $m[3] ? $m[3] : $m[4];
							unset($needed_parts[$m[1]]);
					}
			
					return $needed_parts ? false : $data;
			}
		}
}