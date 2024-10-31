jQuery(document).ready(function( $ ) {
    var tokens = {};

    $('#btn').click(function(){
        $('#errorMessage').empty();
        var apiKey = $('#apiKey').val().trim();
        var secretKey = $('#secretKey').val().trim();
        if(apiKey == "" || secretKey == ""){
            return;
        }
        $('.settingSpinner').css({ 'display' : 'inline-block'});
        var data = {
            apiKey : apiKey,
            secretKey : secretKey,
            action : 'settings_update',
            security : ajax_object.nonce
        };
        $.ajax({
            url : ajax_object.ajax_url,
            type : 'POST',
            data : data,
            success: function (msg) {
                $('.settingSpinner').hide();
                var message = "<b>";
                var obj = JSON.parse(msg);
                if(obj.status == "success"){
                    message += "API key validation success.";
                    if(obj.userRegistered == false) {
                        window.location.reload();
                    }

                }else if(obj.status == "fail"){
                    message += "Please check you API key and Secret Key.";
                }else{
                    message += "Please try again";
                }
                message += '</b>';
                $('#errorMessage').append(message);
            },
            error : function (err) {
                $('#errorMessage').append('Please try again.');
                $('.settingSpinner').hide();
            }
        });
    });

    $('.updateBtn').click(function (e) {
        // check for sanity
        var status = $(this).parent().siblings('.check').children().is(':checked');
        var username = $(this).parent().siblings('.uname').children().val().trim();
        var id = $(this).attr('data-id');
        if(username == ""){
            return;
        }
        var data = {
            id : $(this).attr('data-id'),
            username : username,
            status : status,
            action : 'update_user_data',
            security : ajax_object.nonce
        };
        $.ajax({
            url : ajax_object.ajax_url,
            type : 'POST',
            data : data,
            success: function (msg) {
                window.location.reload();
            },
            error : function (err) {
            }
        });
    });

    $('#checkBtn').click(function () {
        $('#errorMessageVerify').empty();
        $('#errorMessageVerify').html('Fetching Auth methods...').show();

        /* Update ReveSecure username */
        var username = $('#username').val().trim();
        if(username == ""){
            return;
        }
        var userData = {
            username : username,
            action : 'update_user_data',
            security : ajax_object.nonce
        };
        $.ajax({
            url : ajax_object.ajax_url,
            type : 'POST',
            data : userData,
            success: function (msg) {
                info_request();
            },
            error : function (err) {
            }
        });
    });

    function info_request(){
        var data = {
            action : 'get_user_info',
            security : ajax_object.nonce
        };
        $.ajax({
            url : ajax_object.ajax_url,
            type : 'POST',
            data : data,
            success: function (msg) {
                /*
                 * UserLinked  : true / false,
                 * AuthMethods : int
                 * DeviceList  : [{ DeviceName: name, TokenId: id }]
                 */
                var obj = JSON.parse(msg);
                if(obj.status == "success"){
                    if(obj.userLinked == true){
                        if(obj.tokens.Software.length == 0 && obj.tokens.Hardware.length == 0){
                            /* No tokens */
                            $('#errorMessageVerify').html('Please download ReveSecure app from App store for software token or ask your admin to assign a hardware token!');
                        }else{
                            $('#errorMessageVerify').empty();
                            tokens = obj.tokens;
                            var holder = '<tr><th scope="row"><label for="authSelect">Authentication Methods : </label></th><td><select id="authSelect"><option value="">Select Auth Method</option>';
                            holder+= (obj.tokens.Software.length > 0) ? '<option value="S">Software</option>' : '';
                            holder+= (obj.tokens.Software.length > 0) ? '<option value="P">Push</option>' : '';
                            holder+= (obj.tokens.Hardware.length > 0) ? '<option value="H">Hardware</option>' : '';
                            holder+='</select></td></tr>';
                            $('#authBody').append(holder);
                            $('#checkBtn').remove();
                            $('table.validateTable').after('<button id="verifyBtn" class="button button-primary">Test 2FA</button>');
                        }
                    }else{
                        /* User not mapped */
                        $('#errorMessageVerify').html('Please contact your admin.');
                    }

                } else{
                    $('#errorMessageVerify').html('Please try again!');
                }
            },
            error : function (err) {
                $('#errorMessageVerify').html('Please try again!');
            }
        });
    }

    $('body').on('change', '#authSelect', function (e) {
        var selectedVal = $('#authSelect').val();
        var holder = "";
        $('#deviceTr').remove();
        $('#errorMessageVerify').html('');
        if(selectedVal == "P"){
            // show select
            holder+='<tr id="deviceTr"><th scope="row"><label for="deviceSelect">Select Device : </label></th><td><select id="deviceSelect">';
            for(var i=0; i<tokens.Software.length; i++){
                var obj = tokens.Software[i];
                holder+='<option value="' + obj.TokenId + '" >' + obj.TokenName + '</option>';
            }
            holder+='</select></td></tr>';
            // holder+='<button class="otpGroup" id="verifyBtn">Send Push</button>';
        }else if(selectedVal == ""){
            // hide inputs

        }else{
            // show input
            holder+='<tr id="deviceTr"><th scope="row"><label for="otp">Enter OTP : </label></th><td><input type="text" id="otp" placeholder="Enter OTP"/>';
            // holder+='<button class="otpGroup" id="verifyBtn">Verify</button>';
        }
        $('#authBody').append(holder);

    });

    $('body').on('click', '#verifyBtn', function (e) {
        var authMethod = $('#authSelect').val().trim();
        var data = {
            security : ajax_object.nonce
        };
        data.action = "validate";
        if(authMethod == "P"){
            data.tokenId = $('#deviceSelect').val();
        }else if(authMethod == "S" || authMethod == "H"){
            var otp = $('#otp').val().trim();
            if(otp == ""){
                // handle error message
                return;
            }
            data.otp = otp;
        }else{
            return;
        }
        data.method = authMethod;
        $('#errorMessageVerify').html('Checking...');
        $.ajax({
            url : ajax_object.ajax_url,
            type : 'POST',
            data : data,
            success: function (msg) {
                var obj = JSON.parse(msg);
                if(obj.status == "success"){
                    if(authMethod == "S" || authMethod == "H"){
                        $('#loadingMsg').append('<span class="otpGroup msgGroup" >Success!</span>');
                        window.location.reload();
                        //$('.validateDiv').hide();
                        //$('.userList').show();
                    }else{
                        $('#errorMessageVerify').html('Waiting for push approval...');
                        checkPushRequest();
                    }
                }else{
                    if(authMethod == "S" || authMethod == "H") {
                        $('#errorMessageVerify').html('Incorrect OTP...');
                    }else{
                        $('#errorMessageVerify').html('Push send failed. Please try again.');
                    }
                }

            },
            error: function (err) {
            }
        });

    });

    function checkPushRequest(){
        var data = {
            security : ajax_object.nonce
        };
        data.action = "push_check";
        $.ajax({
            url : ajax_object.ajax_url,
            type : 'POST',
            data : data,
            success: function (msg) {
                var obj = JSON.parse(msg.trim());
                $('#errorMessageVerify').html('');
                if(obj.status == "success"){
                    $('#errorMessageVerify').html('Success!');
                    window.location.reload();
                    //$('.validateDiv').hide();
                    //$('.userList').show();
                }else{
                    $('#errorMessageVerify').html('Push check failed!');
                }
            },
            error: function (err) {

            }
        });
    }

});