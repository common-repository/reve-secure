<?php

class ReveSecureUtility{


    static function getHextoBin($hexstr){
        $n = strlen($hexstr);
        $sbin="";
        $i=0;
        while($i<$n)
        {
            $a =substr($hexstr,$i,2);
            $c = pack("H*",$a);
            if ($i==0){$sbin=$c;}
            else {$sbin.=$c;}
            $i+=2;
        }
        return $sbin;
    }

    static function getHeaders($protocol, $host,$method, $path, $date, $api_key, $secret_key, $post_data){
        $data = $date . "\n" . $method . "\n" . $host . "\n" . $path . "\n" . $post_data;
        $accesstoken = $api_key . ":" . hash_hmac('sha256', $data,  self::getHextoBin($secret_key));;
        $headers = array(
            'Authorization' => 'Basic '. base64_encode($accesstoken),
            'Date' => $date
        );
        if($post_data != ""){
           $headers['Content-Type'] = 'application/json';
        }
        return $headers;
    }
    static function make_get_request($api_key, $secret_key, $path){
        $protocol = 'https://';
        $host = 'dashboard.revesecure.com';
        $method = "GET";
        $date = "12-12-12";
        $url = $protocol . $host . $path;
        $args = array(
            'headers' => self::getHeaders($protocol, $host, $method, $path, $date, $api_key, $secret_key, "")
        );
        $resp = wp_remote_get($url, $args);
        if(is_wp_error($resp)){
            return false;
        }
        if(isset($resp['body'])){
            return $resp['body'];
        }
        return false;
    }


    static function make_post_request($api_key, $secret_key, $path, $data){
        $protocol = 'https://';
        $host = 'dashboard.revesecure.com';
        $method = "POST";
        $date = "12-12-12";
        $payload = json_encode( $data );
        $url = $protocol . $host . $path;
        $args = array(
            'headers' => self::getHeaders($protocol, $host, $method, $path, $date, $api_key, $secret_key, $payload),
            'method' => 'POST',
            'body' => $payload
        );
        $resp = wp_remote_request($url, $args);
        if(is_wp_error($resp)){
            return false;
        }
        if(isset($resp['body'])){
            return $resp['body'];
        }
        return false;
    }

    static function send_api_key_check_request($api_key, $secret_key){
        $path = "/rest/v1/validate/check/" . $api_key;
        // call request
        $resp = self::make_get_request($api_key, $secret_key, $path);
        $json_obj = json_decode($resp,true);
        return self::isBasicError($json_obj);
    }

    static function send_push($api_key, $secret_key, $login_name, $user_name, $token_id, $ip, $os, $browser, $is_mobile){
        $path = '/rest/v1/validate/user/push';
        $data = array(
            "ApiKey"=> $api_key,
            "LoginName" => $login_name,
            "UserName" => $user_name,
            "EndUserIP" => $ip,
            "TokenId" => $token_id
        );
        if(isset($os)){
            $data['OS_NAME'] = $os;
        }
        if(isset($browser)){
            $data['BROWSER_NAME'] = $browser;
        }
        if(isset($is_mobile)){
            $data['IS_MOBILE'] = $is_mobile;
        }
        $ret = self::make_post_request($api_key, $secret_key, $path, $data);
        $json_obj = json_decode($ret,true);
        if(!self::isBasicError($json_obj)){
            return false;
        }
        return $json_obj;
    }

    static function check_push_status($api_key, $secret_key, $txn_id){
        $path = '/rest/v1/validate/user/push/status';
        $data = array(
            "ApiKey"=> $api_key,
            "PushTxnId" => $txn_id
        );
        $ret = self::make_post_request($api_key, $secret_key, $path, $data);
        $json_obj = json_decode($ret,true);
        if(!self::isBasicError($json_obj)){
            return false;
        }
        return $json_obj;
    }

    static function validate_otp($api_key, $secret_key, $login_name, $user_name, $ip, $auth_type, $otp){
        $path = '/rest/v1/validate/user/otp';
        $data = array(
            "ApiKey"=> $api_key,
            "LoginName" => $login_name,
            "UserName" => $user_name,
            "EndUserIP" => $ip,
            "AuthType" => $auth_type,
            "OTP" => $otp
        );
        $ret = self::make_post_request($api_key, $secret_key, $path, $data);
        $json_obj = json_decode($ret,true);
        if(!self::isBasicError($json_obj)){
            return false;
        }
        return $json_obj;
    }

    static function isBasicError($json_obj){
        if($json_obj != null){
            $code = $json_obj["status"]["code"];
            if($code!=null){
                if(( (int)$code == 200) ){
                    return true;
                }
            }
        }
        return false;
    }

    static function send_user_info($api_key, $secret_key, $login_name, $user_name){
        $path = '/rest/v1/validate/user';
        $data = array(
            "ApiKey"=> $api_key,
            "LoginName" => $login_name,
            "UserName" => $user_name
        );
        /*
         * for both access denied and user mapped show message either not mapped or denied
         */
        $ret = self::make_post_request($api_key, $secret_key, $path, $data);
        $json_obj = json_decode($ret,true);
        if(!self::isBasicError($json_obj)){
            return false;
        }
        return $json_obj;
    }



}