<?php

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
    exit;
}
delete_option('Rsecure_Api_Key');
delete_option('Rsecure_Secret_Key');
delete_option('Rsecure_wp_username');
delete_option('Rsecure_username');
delete_option('Rsecure_TxnId');
$users = get_users( array( 'fields' => array( 'ID' ) ) );
foreach($users as $user_id){
    delete_user_meta($user_id->ID, 'Rsecure_Status');
    delete_user_meta($user_id->ID, 'Rsecure_Username');
    delete_user_meta($user_id->ID, 'Rsecure_Activated');
}