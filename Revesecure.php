<?php
/*
 * Plugin Name: REVE Secure
 * Plugin URI: https://revesecure.com
 * Description: Two-Factor Authentication by ReveSecure
 * Version: 1.0.0
 * Author: Reve Secure
 * License: GPLv2 or later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 */

if(!defined('ABSPATH')){
    die('Unauthorised');
}
define('RSECURE_BASENAME', plugin_basename(__FILE__));


require_once plugin_dir_path(__FILE__) . 'inc/RevesecureSettingsMenu.php';

if(class_exists('RevesecureSettingsMenu')){
    $revesecure_smInstance = new RevesecureSettingsMenu();
    $revesecure_smInstance->activate();
}