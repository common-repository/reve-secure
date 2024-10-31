<?php

require_once plugin_dir_path(__FILE__) . 'ReveSecureUtility.php';

class RevesecureSettingsMenu{

    function activate(){

        /* REGISTER SETTINGS */

        register_setting('Rsecure_Option_Group', 'Rsecure_Api_Key');
        register_setting('Rsecure_Option_Group', 'Rsecure_Secret_Key');

        /* ENQUEUE SCRIPTS */

        add_action('admin_enqueue_scripts', array($this, 'enqueue'));
        add_action('login_enqueue_scripts', array($this, 'login_enqueue'));

        /* SETTINGS PAGE */

        add_action('admin_menu', array($this, 'add_admin_pages'));
        add_filter("plugin_action_links_" . RSECURE_BASENAME, array($this, 'settings_link'));

        /* LOGIN AJAX */

        add_action('wp_ajax_nopriv_checkotp', array($this, 'login_check_otp_callback'));
        add_action('wp_ajax_nopriv_sendpush', array($this, 'login_send_push_callback'));
        add_action('wp_ajax_nopriv_checkpush', array($this, 'login_check_push_callback'));

        /* SETTINGS PAGE AJAX */

        add_action('wp_ajax_settings_update', array($this, 'settings_update_callback'));
        add_action('wp_ajax_update_user_data', array($this, 'update_user_data_callback'));
        add_action('wp_ajax_get_user_info', array($this, 'get_user_info_callback'));
        add_action('wp_ajax_validate', array($this, 'validate_callback'));
        add_action('wp_ajax_push_check', array($this, 'push_check_callback'));

        /* LOGIN FILTER FOR 2FA */

        add_filter('authenticate', array($this, 'Rsecure_Authenticate_User'), 10, 3);

    }

    /********************************************* UTILITY FUNCTIONS **************************************************/

    function get_client_ip() {
        $ipaddress = '';
        if (isset($_SERVER['HTTP_CLIENT_IP']))
            $ipaddress = $_SERVER['HTTP_CLIENT_IP'];
        else if(isset($_SERVER['HTTP_X_FORWARDED_FOR']))
            $ipaddress = $_SERVER['HTTP_X_FORWARDED_FOR'];
        else if(isset($_SERVER['HTTP_X_FORWARDED']))
            $ipaddress = $_SERVER['HTTP_X_FORWARDED'];
        else if(isset($_SERVER['HTTP_FORWARDED_FOR']))
            $ipaddress = $_SERVER['HTTP_FORWARDED_FOR'];
        else if(isset($_SERVER['HTTP_FORWARDED']))
            $ipaddress = $_SERVER['HTTP_FORWARDED'];
        else if(isset($_SERVER['REMOTE_ADDR']))
            $ipaddress = $_SERVER['REMOTE_ADDR'];
        else
            $ipaddress = 'UNKNOWN';
        return $ipaddress;
    }

    function login_and_set_cookie($user){
        wp_set_auth_cookie( $user->ID );
        do_action( 'wp_login', $user->user_login );
    }

    function unset_session_variables(){

        /* Destroys created session variables  */

        unset($_SESSION['revesecure_txnId']);
        unset($_SESSION['revesecure_username']);
        unset($_SESSION['revesecure_wp_username']);
    }

    function is_Rsecure_enabled(){
        if( get_option('Rsecure_Api_Key') == '' || get_option('Rsecure_Secret_Key') == ''){
            return false;
        }
        return true;
    }

    function isRsecureAlowed($user){
        $RsecureStatus = strlen($user->get('Rsecure_Status'));
        $RsecureUsername = strlen($user->get('Rsecure_Username'));
        $RsecureActivation = strlen($user->get('Rsecure_Activated'));
        if( $RsecureActivation == 0 || $RsecureStatus == 0 || $RsecureUsername == 0 || $user->get('Rsecure_Status') == "false"){
            return false;
        }
        return true;
    }

    function get_user_info($username, $api_key, $secret_key){

        $resp = ReveSecureUtility::send_user_info($api_key, $secret_key, $username, $username );
        $out = array();
        if($resp == false){
            $out['status'] = "failure";
        }else{
            $out['status'] = "success";
            $out['userLinked'] = ($resp['data']['R2faStatus'] == "Denied") ? false : true;
            $out['authMethods'] = $resp['data']['AuthMethods'];
            $out['isBypass'] = ($resp['data']['R2faStatus'] == "Bypass") ? true : false;
            if($resp['data']['R2faStatus'] != "Denied") {
                $tokens['Software'] = array();
                $tokens['Hardware'] = array();
                foreach ($resp['data']['Tokens'] as $token) {
                    if (intval($token['TokenBlocked']) == 0) {
                        array_push($tokens[$token['TokenType']], $token);
                    }
                }
                $out['tokens'] = $tokens;
            }

        }

        return json_encode($out);
    }

    /********************************************** ENQUEUE FUNCTIONS *************************************************/

    function login_enqueue($hook){
        /* This logic prevents unnecessary injection of scripts and style in main login page. We only need them on our second factor page */

        if (version_compare(PHP_VERSION, '5.4.0', '<')) {
            if(session_id() == '') {
                return;
            }
        }else{
            if (session_status() == PHP_SESSION_NONE) {
                return;
            }
        }

        /* enqueue style */

        $plugin_url_css = plugins_url( 'resources/css/loginStyle.css', dirname(__FILE__) );
        wp_enqueue_style('bootstrap-css', '//maxcdn.bootstrapcdn.com/bootstrap/3.3.0/css/bootstrap.min.css');
        wp_enqueue_style('RsecureStyle', $plugin_url_css, array('bootstrap-css'));

        /* enqueue script */

        $plugin_url_js = plugins_url( 'resources/js/loginScript.js', dirname(__FILE__) );
        wp_register_script('jquery-js', '//cdnjs.cloudflare.com/ajax/libs/jquery/3.2.1/jquery.min.js', null, null, true);
        wp_register_script('bootstrap-js', '//maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js', null, null, true);

        wp_enqueue_script('jquery-js');
        wp_enqueue_script('bootstrap-js', array('jquery-js'));
        wp_enqueue_script('RsecureScript', $plugin_url_js, array('jquery-js', 'bootstrap-js'));

        wp_localize_script( 'RsecureScript', 'ajax_object', array(
            'ajax_url' => admin_url( 'admin-ajax.php' ),
            'nonce' => wp_create_nonce( "revesecure-nonce" )
        ));
    }

    function enqueue($hook){
        if('toplevel_page_Revesecure_plugin' != $hook){
            return;
        }
        $plugin_url_css = plugins_url( 'resources/css/style.css', dirname(__FILE__) );
        $plugin_url_js = plugins_url( 'resources/js/script.js', dirname(__FILE__) );
        wp_enqueue_style('RsecureStyle', $plugin_url_css);
        wp_enqueue_script('RsecureScript', $plugin_url_js);
        wp_localize_script( 'RsecureScript', 'ajax_object', array(
            'ajax_url' => admin_url( 'admin-ajax.php' ),
            'nonce' => wp_create_nonce( "revesecure-nonce" )
        ));
    }

    /****************************************** SETTINGS PAGE FUNCTIONS ***********************************************/

    function settings_link($links){
        $setings_link = '<a href="admin.php?page=Revesecure_plugin">Settings</a>';
        array_push($links, $setings_link);
        return $links;
    }

    function add_admin_pages(){
        $icon_path = plugins_url('templates/img/secureLogo.png', dirname(__FILE__) );
        add_menu_page(
            'Rsecure Settings',
            'REVE Secure',
            'manage_options', 'Revesecure_plugin', array($this, 'admin_index'), $icon_path, 110);
    }

    function admin_index(){
        $plugins_path = plugin_dir_path(dirname(__FILE__));
        require_once $plugins_path . 'templates/admin.php';
    }

    /*********************************** LOGIN (2FA PAGE) AJAX CALLBACK FUNCTIONS *************************************/

    function login_check_otp_callback(){
        $nonce_check = check_ajax_referer( 'revesecure-nonce', 'security', true );
        if(!$nonce_check){
            wp_die();
        }
        session_start();
        $ip = $this->get_client_ip();
        $api_key = get_option('Rsecure_Api_Key');
        $secret_key = get_option('Rsecure_Secret_Key');
        $user_name = $_SESSION['revesecure_username'];
        $wp_username = $_SESSION['revesecure_wp_username'];
        $method = sanitize_text_field($_POST['r2faAuthSelect']);
        if($method != "H" && $method != "S" && $method != "B"){
            echo "failure";
        }else{
            $otp = sanitize_text_field($_POST['code']);
            $resp = ReveSecureUtility::validate_otp($api_key, $secret_key, $user_name, $user_name, $ip, $method, $otp);
            $user = get_user_by( 'login', $wp_username);
            if($resp['data']['AuthResult'] == "OTP Valid"){
                $this->login_and_set_cookie($user);
                echo "success";
            } else {
                echo "failure";
            }
        }
        $this->unset_session_variables();
        die();
    }

    function login_send_push_callback(){
        $nonce_check = check_ajax_referer( 'revesecure-nonce', 'security', true );
        if(!$nonce_check){
            wp_die();
        }
        $ip = $this->get_client_ip();
        session_start();
        $token_id = sanitize_text_field($_POST['tokenId']);
        $os = sanitize_text_field($_POST['os']);
        $is_mobile = sanitize_text_field($_POST['mobile']);
        $browser = sanitize_text_field($_POST['browser']);
        $api_key = get_option('Rsecure_Api_Key');
        $secret_key = get_option('Rsecure_Secret_Key');
        $user_name = $_SESSION['revesecure_username'];
        $resp = ReveSecureUtility::send_push($api_key, $secret_key, $user_name, $user_name, $token_id, $ip,$os,$browser,$is_mobile);
        if($resp == false){
            $this->unset_session_variables();
            $out = "failure";
        }else{
            $out = "success";
            $_SESSION['revesecure_txnId'] = $resp['data']['PushTxnId'];
        }
        echo $out;
        die();
    }

    function login_check_push_callback(){
        $nonce_check = check_ajax_referer( 'revesecure-nonce', 'security', true );
        if(!$nonce_check){
            wp_die();
        }
        $api_key = get_option('Rsecure_Api_Key');
        $secret_key = get_option('Rsecure_Secret_Key');
        session_start();
        $txn_id = $_SESSION['revesecure_txnId'];

        for($i=0; $i < 30; $i++){
            // check status
            $resp = ReveSecureUtility::check_push_status($api_key, $secret_key,  $txn_id);
            if($resp == false){
                $out = "failure";
            }else{
                if($resp['data']['PushState'] == "PUSH_TXN_UNKNOWN" || $resp['data']['PushState'] == "PUSH_SEND_ERROR" || $resp['data']['PushState'] == "PUSH_DENY" || $resp['data']['PushState'] == "PUSH_APPROVE"){
                    if($resp['data']['PushState'] == "PUSH_APPROVE"){
                        $wp_username = $_SESSION['revesecure_wp_username'];
                        $user = get_user_by( 'login', $wp_username);
                        $this->login_and_set_cookie($user);
                        $out = "success";
                    }else if ($resp['data']['PushState'] == "PUSH_TXN_UNKNOWN"){
                        $out = "expired";
                    }else if($resp['data']['PushState'] == "PUSH_DENY"){
                        $out = "denied";
                    }else{
                        $out = "failure";
                    }
                    break;
                }
            }
            // sleep
            sleep(1);
        }
        $this->unset_session_variables();
        echo $out;
        die();

    }

    /************************************* SETTINGS PAGE AJAX CALLBACK FUNCTIONS **************************************/

    function settings_update_callback (){
        $nonce_check = check_ajax_referer( 'revesecure-nonce', 'security', true );
        if(!$nonce_check){
            wp_die();
        }
        $apiKey = sanitize_text_field($_POST['apiKey']);
        $secretKey = sanitize_text_field($_POST['secretKey']);
        $out['userRegistered'] = true;
        if(ReveSecureUtility::send_api_key_check_request($apiKey,$secretKey)){
            update_option('Rsecure_Api_Key', $apiKey);
            update_option('Rsecure_Secret_Key', $secretKey);
            $out['status'] = "success";
        } else {
            $out['status'] = "fail";
        }
        $user = wp_get_current_user();
        $RsecureStatus = strlen($user->get('Rsecure_Status'));
        if($RsecureStatus == 0){
            $out['userRegistered'] = false;
        }
        echo json_encode($out);
        die();
    }

    function update_user_data_callback (){
        $nonce_check = check_ajax_referer( 'revesecure-nonce', 'security', true );
        if(!$nonce_check){
            wp_die();
        }
        $status = sanitize_text_field($_POST['status']);
        $id = sanitize_text_field($_POST['id']);
        $username = sanitize_text_field($_POST['username']);
        if($id == null){
            $user = wp_get_current_user();
            $id = $user->ID;
        }
        if($status != null) {
            update_user_meta($id, 'Rsecure_Status', $status);
        }
        if($username != null) {
            update_user_meta($id, 'Rsecure_Username', $username);
        }
        die();
    }

    function get_user_info_callback(){
        error_log("user info called");
        $nonce_check = check_ajax_referer( 'revesecure-nonce', 'security', true );
        if(!$nonce_check){
            wp_die();
        }
        $user = wp_get_current_user();
        $user_meta = get_user_meta ( $user->ID);
        $username =  $user_meta['Rsecure_Username'][0];
        $api_key = get_option('Rsecure_Api_Key');
        $secret_key = get_option('Rsecure_Secret_Key');
        echo $this->get_user_info($username, $api_key, $secret_key);
        die();
    }

    function validate_callback(){
        $nonce_check = check_ajax_referer( 'revesecure-nonce', 'security', true );
        if(!$nonce_check){
            wp_die();
        }
        $user = wp_get_current_user();
        $ip = $this->get_client_ip();
        $user = wp_get_current_user();
        $user_meta = get_user_meta ( $user->ID);
        $user_name =  $user_meta['Rsecure_Username'][0];
        $api_key = get_option('Rsecure_Api_Key');
        $secret_key = get_option('Rsecure_Secret_Key');
        $method = sanitize_text_field($_POST['method']);
        $out = array();
        if($method == "P"){
            $token_id = sanitize_text_field($_POST['tokenId']);
            // send push
            $resp = ReveSecureUtility::send_push($api_key, $secret_key, $user_name, $user_name, $token_id, $ip,null,null,null);
            if($resp == false){
                $out['status'] = "failure";
            }else{
                $out['status'] = "success";
                session_start();
                $_SESSION['revesecure_txnId'] = $resp['data']['PushTxnId'];
                update_option('Rsecure_TxnId', $resp['data']['PushTxnId']);
            }
        }else if($method == "S" || $method == "H"){
            $otp = sanitize_text_field($_POST['otp']);
            // validate otp
            $resp = ReveSecureUtility::validate_otp($api_key, $secret_key, $user_name, $user_name, $ip, $method, $otp);
            if($resp == false){
                $out['status'] = "failure";
            }else{
                if($resp['data']['AuthResult'] == "OTP Valid"){
                    update_user_meta($user->ID, 'Rsecure_Status', "true");
                    update_user_meta($user->ID, 'Rsecure_Activated', "true");
                    $out['status'] = "success";
                }else{
                    $out['status'] = "failure";
                }
            }
        }
        echo json_encode($out);
        die();
    }

    function push_check_callback(){
        $nonce_check = check_ajax_referer( 'revesecure-nonce', 'security', true );
        if(!$nonce_check){
            wp_die();
        }
        session_start();
        $user = wp_get_current_user();
        $txn_id = $_SESSION['revesecure_txnId'];
        $api_key = get_option('Rsecure_Api_Key');
        $secret_key = get_option('Rsecure_Secret_Key');
        $out['status'] = "failure";
        for($i=0; $i < 30; $i++){
            // check status
            $resp = ReveSecureUtility::check_push_status($api_key, $secret_key,  $txn_id);
            if($resp == false){
                $out['status'] = "failure";
            }else{
                if($resp['data']['PushState'] == "PUSH_TXN_UNKNOWN" || $resp['data']['PushState'] == "PUSH_SEND_ERROR" || $resp['data']['PushState'] == "PUSH_DENY" || $resp['data']['PushState'] == "PUSH_APPROVE"){
                    if($resp['data']['PushState'] == "PUSH_APPROVE"){
                        update_user_meta($user->ID, 'Rsecure_Status', "true");
                        update_user_meta($user->ID, 'Rsecure_Activated', "true");
                        $out['status'] = "success";
                    }else{
                        $out['status'] = "failure";
                    }
                    break;
                }
            }
            // sleep
            sleep(1);
        }
        $this->unset_session_variables();
        echo json_encode($out);
        die();

    }

    /****************************************** LOGIN FILTER FUNCTIONS ************************************************/

    function Rsecure_Authenticate_User($user="", $username="", $password=""){
        if (is_a($user, 'WP_User')) {
            return $user;
        }

        if (!$this->is_Rsecure_enabled()){
            return;
        }

        if (strlen($username) > 0) {
            // primary auth
            $user = new WP_User(0, $username);
            if (!$user) {

                return;
            }


            remove_action('authenticate', 'wp_authenticate_username_password', 20);
            $user = wp_authenticate_username_password(NULL, $username, $password);
            if (!is_a($user, 'WP_User')) {
                // on error, return said error (and skip the remaining plugin chain)
                return $user;
            } else {
                if(!$this->isRsecureAlowed($user)){
                    return $user;
                }
                session_start();
                $_SESSION['revesecure_username'] = $user->get('Rsecure_Username');
                $_SESSION['revesecure_wp_username'] = sanitize_user($username);
                $this->Rsecure_start_second_factor($user);
            }
        }
    }

    function Rsecure_start_second_factor($user, $redirect_to=NULL){
        if (!$redirect_to){
            $redirect_to = isset( $_POST['redirect_to'] ) ? $_POST['redirect_to'] : admin_url();
        }
        $api_key = get_option('Rsecure_Api_Key');
        $secret_key = get_option('Rsecure_Secret_Key');
        $username = $_SESSION['revesecure_username'];
        $user_info_response = json_decode($this->get_user_info($username, $api_key, $secret_key), true);
        if(!$user_info_response['isBypass']){
            wp_logout();
            $this->Rsecure_sign_request($user, $redirect_to, $user_info_response);
            exit();
        }
    }

    function Rsecure_sign_request($user, $redirect, $user_info_response) {
        $error = false;
        if($user_info_response['status'] == 'failure' || !$user_info_response['userLinked'] ){
            $error = true;
        }
        $authMethods = $user_info_response["authMethods"];
        $active__img_url = plugins_url( 'templates/img/active.svg', dirname(__FILE__) );
        $inactive__img_url = plugins_url( 'templates/img/inactive.svg', dirname(__FILE__) );
        require_once plugin_dir_path(dirname(__FILE__)) . 'templates/second.php';
    }

}