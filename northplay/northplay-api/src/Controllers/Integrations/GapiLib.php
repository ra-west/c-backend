<?php
namespace Northplay\NorthplayApi\Controllers\Integrations;

class GapiLib
{
    public $apiurl;
    public $apikey;
    public $lasterror;

    public function __construct($url = '', $key = '')
    {
        $this->apiurl = $url;
        $this->apikey = $key;
        $this->lasterror = '';
    }

    function SendData($params)
    {
        $this->lasterror = '';

        //CHECK FOR ERRORS
        if($this->apikey=='')
        {
            $this->lasterror = "API KEY NOT SET";
            return false;
        }
        if($this->apiurl=='')
        {
            $this->lasterror = "API URL NOT SET";
            return false;
        }
        if(count($params)==0)
        {
            $this->lasterror = "PARAMETERS NOT SET";
            return false;
        }
        if(!$this->is_assoc($params))
        {
            $this->lasterror = "WRONG PARAMETERS VARIABLE. MUST BE ASSOCIATIVE ARRAY";
            return false;
        }
        //END CHECKING FOR ERRORS

        $joinparams = '';

        $rand = md5(time());

        foreach ($params as $key => &$val) {
            if (is_array($val)) $val = implode(',', $val);
            $enc_val = urlencode($val);
            $post_params[] = $key.'='. $enc_val;
            $joinparams = $joinparams.$enc_val;
        }

        $post_params[] = 'callid'.'='. $rand; //add random unique call identifier
        $joinparams = $joinparams.$rand; //add it to sign

        $sign = hash_hmac("sha1",$joinparams,$this->apikey);
        $post_string = implode('&', $post_params).'&sign='.$sign;

        try{
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $this->apiurl);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $post_string);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_USERAGENT, 'curl');
            curl_setopt($ch, CURLOPT_TIMEOUT, 15);

            $result = curl_exec($ch);
            if($result==false)
            {
                $this->lasterror = curl_error($ch);
                return false;
            }
        }
        catch (Exception $e)
        {
            $this->lasterror = "Exception :".$e->getMessage();
            return false;
        }

        curl_close($ch);

        return $result;
    }

    function is_assoc(array $array) {
        return (bool)count(array_filter(array_keys($array), 'is_string'));
    }
}