<?php
login_header();
?>
<html>
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ReveSecure | 2FA</title>
</head>
<body>
<div class="container">
    <div class="card card-container">
        <?php if (!$error) { ?>


        <form class="form-signin" action="" id="r2faForm" autocomplete="off" >

            <div id="pushApproveData">
              <span id="imagesSpan">
                  <img src="<?php echo $active__img_url; ?>" class="svgViewAll" id="svgViewAll">
                <img src="<?php echo $inactive__img_url; ?>" class="svgViewAllDeny" id="svgViewAllDeny">
               </span>
                <p id="statusText" >Push Approved</p>
                <p id="message" > Thank you for using REVE Secure.<br> Have a nice day !!</p>
            </div>
            <div class="formData">
                <label for="r2faAuth" id="authSelectLabel"> Authentication Methods <select  class="form-control" id="r2faAuthSelect" name="r2faAuthSelect">
                        <?php if (in_array("Hardware", $authMethods)) { ?>
                        <option value="H" >Hardware</option>
                        <?php } ?>
                        <?php if (in_array("Software", $authMethods)) { ?>
                        <option value="S">Software</option>
                        <?php } ?>
                        <?php if (in_array("BypassCode", $authMethods)) { ?>
                        <option value="B">Bypass Code</option>
                        <?php } ?>
                        <?php if (in_array("Push", $authMethods)) { ?>
                        <option value="P">Push</option>
                        <?php } ?>
                    </select></label>
                <input type="text" class="form-control otpGroup" id="code" name="code" placeholder="Enter your OTP." autocomplete="off" required="true">

                <?php if (in_array("Push", $authMethods)) { ?>
                <select class="form-control pushGroup" id="r2faTokenId">
                    <?php foreach($user_info_response["tokens"]["Software"] as $item){ ?>
                    <option value="<?php echo $item['TokenId'] ?>"><?php echo $item['TokenName'] ?></option>
                    <?php } ?>
                </select>
                <?php } ?>
                <span id="buttonSpan">
                      <button class="btn btn-lg btn-primary btn-block btn-signin submitBtn" type="submit" id="send" >Submit</button>
                        <button class="btn btn-lg btn-warning btn-block btn-signin " onclick="echoRedirect()" type="button" id="cancel" >Cancel</button>
                    </span>
            </div>  </form>
        <div id="pushgya">
            Attempting to send push.
        </div>
        <?php } else { ?>
        <form class="form-signin"  id="r2faForm" autocomplete="off" >
            <div class="alert alert-danger">
                <strong>Access Denied! Please contact your REVE Secure admin.</strong>
            </div>
            <button type="submit" class="btn btn-danger " onclick="echoRedirect()">Return to login</button>


        </form>
        <?php } ?>
    </div>
</div>