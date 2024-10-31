<?php
    $is_setting_set = (strlen(get_option('Rsecure_Api_Key')) != 0 && strlen(get_option('Rsecure_Secret_Key')) !=0) ;
?>
<div class="wrap">
<h1>REVE Secure Settings</h1>
<ol>
    <li>Sign Up in ReveSecure Dashboard.</li>
    <li>Create a new service of WEB API type.</li>
    <li>Copy API key and Secret key from service details and fill up below</li>
</ol>
<?php if(!$is_setting_set){ ?>
    <div class="error notice">
        <p><b>Important : </b>Please set your API KEY and SECRET KEY.</p>
    </div>
<?php } ?>
<table class="form-table">
    <tbody>
    <tr>
        <th scope="row" class="myRow">
            <label for="apiKey">API KEY : </label>
        </th>
        <td>
            <input id="apiKey" value="<?php echo get_option('Rsecure_Api_Key') ?>" placeholder="API KEY" type="text">
        </td>
    </tr>
    <tr>
        <th scope="row" class="myRow">
            <label for="secretKey">SECRET KEY : </label>
        </th>
        <td>
            <input id="secretKey" value="<?php echo get_option('Rsecure_Secret_Key') ?>" placeholder="Secret KEY" type="text">

        </td>
    </tr>
    </tbody>
</table>
<button id="btn" class="button button-primary">Validate API</button><div class="mySpinner settingSpinner"></div>
<br>
<span id="errorMessage"></span>
<br>
<br>
<?php
$user = wp_get_current_user();
$RsecureStatus = strlen($user->get('Rsecure_Status'));
$RsecureUsername = strlen($user->get('Rsecure_Username'));
$RsecureActivated = strlen($user->get('Rsecure_Activated'));
$tableClass = ($RsecureActivated == 0) ? "hidden" : "";
$username_val = ($RsecureUsername == 0) ? "" : $user->get('Rsecure_Username');
$users = get_users( array( 'fields' => array( 'ID' , 'display_name', 'user_login') ) );
?>
    <?php if($RsecureActivated == 0 && $is_setting_set) { ?>
<table class="form-table validateTable">
    <div class="error notice">
        <p><b>Important : </b>Please set your REVE Secure username and test you 2FA to activate Second Factor for your account.</p>
    </div>

    <tbody id="authBody">
    <p>Please map your wordpress login name with revesecure login name to verify.</p>
    <tr>
        <th scope="row">
            <label>WordPress Login Name : </label>
        </th>
        <td>
            <p><?php echo $user->user_login ?></p>
        </td>
    </tr>
    <tr>
        <th scope="row">
            <label for="username">REVE Secure Username : </label>
        </th>
        <td>
            <input id="username" value="<?php echo $username_val; ?>" type="text">
        </td>
    </tr>
    </tbody>
</table>
<button id="checkBtn" class="button button-primary">Verify</button>
<label id="errorMessageVerify"></label>
<?php } ?>
<div class=" userList <?php echo $tableClass; ?>">
    <table class="wp-list-table widefat fixed striped users">
        <thead>
            <tr>
                <th width="40px" class="manage-column column-name">Sl No. </th>
                <th width="100px"class="manage-column column-name">Name</th>
                <th class="manage-column column-name">Wordpress Username</th>
                <th class="manage-column column-name">Roles</th>
                <th>REVE Secure Username</th>
                <th width="100px" >2FA Status</th>
                <th width="100px" >Action</th>
            </tr>
        </thead>
        <?php
        $counter = 1;
        foreach($users as $user){
            $user_data = get_userdata ( $user->ID);
            $roles = implode (", ", $user_data->roles);
            $user_meta = get_user_meta ( $user->ID);
            $status_state = isset($user_meta['Rsecure_Status'][0]);
            $revesecure_username_state = isset($user_meta['Rsecure_Username'][0]);
            $revesecure_activated_state = isset($user_meta['Rsecure_Activated'][0]);
            $color_class = (!$status_state || !$revesecure_username_state || !$revesecure_activated_state) ? "tableRed" : "";
            $status = (!$status_state) ? "" : ($user_meta['Rsecure_Status'][0] == "true") ? "checked" : "";
            $username =  (!$revesecure_username_state) ? "" : $user_meta['Rsecure_Username'][0];
            echo "
            <tr class='$color_class'>
                <td>$counter</td>
                <td>$user->display_name</td>
                <td>$user->user_login</td>
                <td>$roles</td>
                <td class='uname'><input type=\"text\" placeholder=\"username\" value='$username' ></td>
                <td class='check'><input type=\"checkbox\" $status ></td>
                <td><button data-id=$user->ID class='updateBtn button' >UPDATE</button></td>
            </tr>";
            $counter++;
        }
        ?>
    </table>
    <p>* The users with red background have not configured REVE Secure username or activated 2FA in their accounts.</p>
</div>

