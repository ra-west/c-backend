<?php
if (!function_exists('save_log')) {
    function save_log($type, $message, $extra_data = NULL) {
        $data = [
            'message' => $message
        ];
        return \Northplay\NorthplayApi\Models\LogModel::save_log($type, $data, $extra_data);
    }
}

if (!function_exists('app_url')) {
    function app_url() {
      return env('APP_URL');
    }
}
if (!function_exists('validate')) {
    function validate($rules, $request_input) {
        
        $validator = \Illuminate\Support\Facades\Validator::make($request_input, $rules);

        /* 
            [
                'operator_player_id' => ['required', 'min:3', 'max:100', 'regex:/^[^(\|\]`!%^&=};:?><’)]*$/'],
                'operator_key' => ['required', 'min:10', 'max:50'],
            ]
        */

        if ($validator->stopOnFirstFailure()->fails()) {
            $errorReason = $validator->errors()->first();
            abort(400, $errorReason);
        }
    }
}


if (!function_exists('generate_uid')) {
    function generate_uid($more_entropy=false) {
        $s = uniqid('', $more_entropy);
        if (!$more_entropy)
            return base_convert($s, 16, 36);
            
        $hex = substr($s, 0, 13);
        $dec = $s[13] . substr($s, 15); // skip the dot
        return base_convert($hex, 16, 36) . base_convert($dec, 10, 36);
    }
}

if (!function_exists('northplay_config_get')) {
    function northplay_config_get($key, $defaultValue = NULL, $defaultCategory = NULL)
    {
        $config_model = new \Northplay\NorthplayApi\Models\ConfigModel;
        return $config_model->get_config_value($key, $defaultValue, $defaultCategory);
    }
}
if (!function_exists('northplay_config_get')) {
    function northplay_config_update($key, $defaultValue = NULL, $defaultCategory = NULL)
    {
        $config_model = new \Northplay\NorthplayApi\Models\ConfigModel;
        return $config_model->update_config_value($key, $defaultValue, $defaultCategory);
    }
}
if (!function_exists('now_nice')) {
    function now_nice()
        {
            return Carbon\Carbon::parse(now())->format('Y-m-d H:i:s');
        }
}
if (!function_exists('morph_array')) {
    function morph_array($data)
    {
        if ($data instanceof Arrayable) {
            return $data->toArray();
        }
        return $data;
    }
}
if (!function_exists('random_first_name')) {
    function random_first_name()
        {
           $names = [
                "Ari",
                "Evan",
                "Logan",
                "Zayn",
                "Cairo",
                "Blair",
                "Zion",
                "Baker",
                "Casey",
                "Hayes",
                "Raven",
                "Kade",
                "Andy",
                "Jesse",
                "Mason",
                "Cody",
                "Reed",
                "Sage",
                "James",
                "Lane",
                "Alice",
                "Reid",
                "Lyric",
                "Ayden",
                "Cade",
                "Olive",
                "Lucca",
                "Rory",
                "Juan",
                "Angel",
                "Jade",
                "Onyx",
                "Jaden",
                "Blake",
                "Holly",
                "Aspen",
                "Jude",
                "Bryce",
                "Brynn",
                "Niko",
                "Skye",
                "Sean",
                "Asa",
                "Kyle",
                "Brady",
                "Quinn",
                "Kyrie",
                "Kyler",
                "Kiara",
                "Allie",
                "Phoenix",
                "Miracle",
                "Spencer",
                "Oaklee",
                "Addison",
                "Wynter",
                "Paxton",
                "Camden",
                "Jordyn",
                "Kalani",
                "Colson",
                "Everly",
                "Camille",
                "Brantley",
                "Jameson",
                "Tanner",
                "Parker",
                "Saylor",
                "Presley",
                "Camryn",
                "Kinsley",
                "Juniper",
                "Corbin",
                "Briggs",
                "Walker",
                "Rowan",
                "Oakley",
                "Rylie",
                "Saint",
                "Sydney",
                "Bentley",
                "Cataleya",
                "Kairo",
                "Ainsley",
                "Rhys",
                "Preston",
                "Pierce",
                "Zane",
                "Brooke",
                "Bodhi",
                "Finley",
                "Piper",
                "Devin",
                "Bristol",
                "Peyton",
                "Karson",
                "Stetson",
                "Aubrey",
                "Matias",
                "Hadley",
                "Tyler",
                "Tristan",
                "Alexis",
                "Noel",
                "Barrett",
                "Emersyn",
                "Teagan",
                "Kendall",
                "Sawyer",
                "Harper",
                "Emerson",
                "Londyn",
                "Memphis",
                "Jordan",
                "Reagan",
                "Delaney",
                "Makenzie",
                "Carson",
                "Dakota",
                "Cameron",
                "Keegan",
                "Noelle",
                "Bennett",
                "Kamryn",
                "Stevie",
                "Kennedy",
                "Skylar",
                "Kenzie",
                "Maverick",
                "Madison",
                "Andrea",
                "Brinley",
                "Hayden",
                "Kamden",
                "Hailey",
                "Evelyn",
                "Sam",
                "Ocean",
                "Baylor",
                "Salem",
                "Kylan",
                "Rayne",
                "Noe",
                "Wallace",
                "Azariah",
                "Rayan",
                "Dallas",
                "Callahan",
                "Blaine",
                "Taylor",
                "Crosby",
                "Egypt",
                "Forest",
                "Reese",
                "Monroe",
                "Joey",
                "Ellis",
                "Loyal",
                "Banks",
                "Aries",
                "Waverly",
                "Julien",
                "Duke",
                "Westley",
                "Cal",
                "Flynn",
                "Kensley",
                "Kamdyn",
                "Haley",
                "Denver",
                "Cory",
                "Drew",
                "Skyler",
                "Finnley",
                "Keaton",
                "Trace",
                "McCoy",
                "Kai",
                "Alma",
                "Justice",
                "Zaire",
                "Sevyn",
                "Baylee",
                "Makai",
                "Saige",
                "Ariel",
                "Nova",
                "Noor",
                "Kyree",
                "Marlowe",
                "Porter",
                "Kora",
                "Jamari",
                "Shiloh",
                "Miller",
                "Corey",
                "Veda",
                "Allison",
                "Gael",
                "Dakari",
                "Jadiel",
                "Chandler",
                "Brecken",
                "Beau",
                "Kasen",
                "Paisley",
                "Dariel",
                "Koda",
                "Murphy",
                "Armani",
                "Tate",
                "Ashley",
                "Karsyn",
                "Jakobe",
                "Azriel",
                "Allen",
                "Harlan",
                "Luca",
                "Dash",
                "Santana",
                "Carter",
                "Ryan",
                "Royal",
                "Callen",
                "McKinley",
                "Winter",
                "Jaxx",
                "Zahir",
                "Danny",
                "Jocelyn",
                "Brooklyn",
                "Scout",
                "Julian",
                "Aydin",
                "Bellamy",
                "Addyson",
                "Dylan",
                "Adriel",
                "Adler",
                "Ty",
                "Kye",
                "Milan",
                "Adley",
                "Avi",
                "Braylen",
                "Kinley",
                "Rylan",
                "Harlee",
                "Everest",
                "Holland",
                "Zaiden",
                "Lexie",
                "Leighton",
                "Elon",
                "Shay",
                "Quincy",
                "Rowen",
                "Arian",
                "Sutton",
                "Morgan",
                "Dior",
                "Hunter",
                "Ezra",
                "Jaylen",
                "Kolton",
                "Bailey",
                "Meredith",
                "Cadence",
                "Carmen",
                "Kieran",
                "Sasha",
                "Ira",
                "Kimberly",
                "Rene",
                "Eden",
                "Ryland",
                "Marshall",
                "Connor",
                "Cassidy",
                "Aryan",
                "Devon",
                "Mackenzie",
                "Titan",
                "Payton",
                "McKenna",
                "Royalty",
                "Canaan",
                "Azaria",
                "Colby",
                "Harley",
                "Reign",
                "Lee",
                "Lennon",
                "Jerry",
                "Kody",
                "Remi",
                "Gatlin",
           ];
           $array_size = sizeof($names);
           
           return $names[(rand(0, ($array_size - 1)))];
        
        }
    }
if (!function_exists('now')) {
function now($timezone = null)
    {
       return Carbon\Carbon::now();
    }
}
if (!function_exists('now_nice')) {
function now_nice()
    {
        return Carbon\Carbon::parse(now())->format('Y-m-d H:i:s');
    }
}
if (!function_exists('is_alphanumeric')) {
    function is_alphanumeric($input)
    {
        return preg_match("/^[A-Za-z0-9]*$/", $input);
    }
}
if (!function_exists('is_uuid')) {
function is_uuid($uuid)
    {
        if (!is_string($uuid) || (preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-4[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/', $uuid) !== 1)) {
            return false;
        } else {
            return true;
        }
    }
}
if (!function_exists('encrypt_string')) {
function encrypt_string($plaintext, $password = NULL)
    {
    if($password === NULL) {
        $password = config('jwt.secret');
    }
    $method = "AES-256-CBC";
    $key = hash('sha256', $password, true);
    $iv = openssl_random_pseudo_bytes(16);

    $ciphertext = openssl_encrypt($plaintext, $method, $key, OPENSSL_RAW_DATA, $iv);
    $hash = hash_hmac('sha256', $ciphertext . $iv, $key, true);

    return base64_encode($iv . $hash . $ciphertext);
    }
}
if (!function_exists('decrypt_string')) {
    function decrypt_string($string, $password = NULL)
    {
        $ivHashCiphertext = base64_decode($string);
        if($password === NULL) {
            $password = config('jwt.secret');
        }
    $method = "AES-256-CBC";
    $iv = substr($ivHashCiphertext, 0, 16);
    $hash = substr($ivHashCiphertext, 16, 32);
    $ciphertext = substr($ivHashCiphertext, 48);
    $key = hash('sha256', $password, true);

    if (!hash_equals(hash_hmac('sha256', $ciphertext . $iv, $key, true), $hash)) return null;

    return openssl_decrypt($ciphertext, $method, $key, OPENSSL_RAW_DATA, $iv);
    }
}

if (!function_exists('generate_sign')) {
    function generate_sign(string $token, string $pwd = NULL)
    {
        $timestamp = time();
        if($pwd === NULL) {
            $pwd = config('jwt.secret');
        }
        $encryption_key = $pwd.'-'.$timestamp; //Consider timestamp the randomizing salt, can be replaced by any randomizing key/regex
        $generate_sign = hash_hmac('md5', $token, $encryption_key);
        $concat_sign_time = $generate_sign.'-'.$timestamp;
        return $concat_sign_time;
    }
}
if (!function_exists('verify_sign')) {
    function verify_sign(string $signature, string $token, string $pwd = NULL)
    {
        if($pwd === NULL) {
            $pwd = config('jwt.secret');
        }
        try {
            $explode_signature = explode('-', $signature);
            $timestamp = $explode_signature[1];
            $encryption_key =  $pwd.'-'.$timestamp;
            $generate_sign = hash_hmac('md5', $token, $encryption_key);
            $concat_sign_time = $generate_sign.'-'.$timestamp;
            if($signature === $concat_sign_time) { // verify signature is same outcome
                return true;
            }
        } catch (\Exception $exception) {
            return false;
        }
        return false; //signature not matching, returning false
    }
}
if (!function_exists('replaceInFile')) {
function replaceInFile($search, $replace, $path)
{
    file_put_contents($path, str_replace($search, $replace, file_get_contents($path)));
}
}
if (!function_exists('replaceInBetweenInFile')) {
function replaceInBetweenInFile($a, $b, $replace, $path)
{
    $file_get_contents = file_get_contents($path);
    $in_between = in_between($a, $b, $file_get_contents);
    if($in_between) {
        $search_string = stripcslashes($a.$in_between.$b);
        $replace_string = stripcslashes($a.$replace.$b);
        file_put_contents($path, str_replace($search_string, $replace_string, file_get_contents($path)));
        return true;
    }
    return true;
}
}
if (!function_exists('in_between')) {
function in_between($a, $b, $data)
{
    preg_match('/'.$a.'(.*?)'.$b.'/s', $data, $match);
    if(!isset($match[1])) {
        return false;
    }
    return $match[1];
}
}