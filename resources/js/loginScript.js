var bypass = false;


function echoRedirect(){
    window.location.href = "wp-login.php";
}

/* Device Info */
(function(window) {
    {
        var unknown = '-';

        // screen
        var screenSize = '';
        if (screen.width) {
            width = (screen.width) ? screen.width : '';
            height = (screen.height) ? screen.height : '';
            screenSize += '' + width + " x " + height;
        }

        // browser
        var nVer = navigator.appVersion;
        var nAgt = navigator.userAgent;
        var browser = navigator.appName;
        var version = '' + parseFloat(navigator.appVersion);
        var majorVersion = parseInt(navigator.appVersion, 10);
        var nameOffset, verOffset, ix;

        // Opera
        if ((verOffset = nAgt.indexOf('Opera')) != -1) {
            browser = 'Opera';
            version = nAgt.substring(verOffset + 6);
            if ((verOffset = nAgt.indexOf('Version')) != -1) {
                version = nAgt.substring(verOffset + 8);
            }
        }
        // Opera Next
        if ((verOffset = nAgt.indexOf('OPR')) != -1) {
            browser = 'Opera';
            version = nAgt.substring(verOffset + 4);
        }
        // Edge
        else if ((verOffset = nAgt.indexOf('Edge')) != -1) {
            browser = 'Microsoft Edge';
            version = nAgt.substring(verOffset + 5);
        }
        // MSIE
        else if ((verOffset = nAgt.indexOf('MSIE')) != -1) {
            browser = 'Microsoft Internet Explorer';
            version = nAgt.substring(verOffset + 5);
        }
        // Chrome
        else if ((verOffset = nAgt.indexOf('Chrome')) != -1) {
            browser = 'Chrome';
            version = nAgt.substring(verOffset + 7);
        }
        // Safari
        else if ((verOffset = nAgt.indexOf('Safari')) != -1) {
            browser = 'Safari';
            version = nAgt.substring(verOffset + 7);
            if ((verOffset = nAgt.indexOf('Version')) != -1) {
                version = nAgt.substring(verOffset + 8);
            }
        }
        // Firefox
        else if ((verOffset = nAgt.indexOf('Firefox')) != -1) {
            browser = 'Firefox';
            version = nAgt.substring(verOffset + 8);
        }
        // MSIE 11+
        else if (nAgt.indexOf('Trident/') != -1) {
            browser = 'Microsoft Internet Explorer';
            version = nAgt.substring(nAgt.indexOf('rv:') + 3);
        }
        // Other browsers
        else if ((nameOffset = nAgt.lastIndexOf(' ') + 1) < (verOffset = nAgt.lastIndexOf('/'))) {
            browser = nAgt.substring(nameOffset, verOffset);
            version = nAgt.substring(verOffset + 1);
            if (browser.toLowerCase() == browser.toUpperCase()) {
                browser = navigator.appName;
            }
        }
        // trim the version string
        if ((ix = version.indexOf(';')) != -1)
            version = version.substring(0, ix);
        if ((ix = version.indexOf(' ')) != -1)
            version = version.substring(0, ix);
        if ((ix = version.indexOf(')')) != -1)
            version = version.substring(0, ix);

        majorVersion = parseInt('' + version, 10);
        if (isNaN(majorVersion)) {
            version = '' + parseFloat(navigator.appVersion);
            majorVersion = parseInt(navigator.appVersion, 10);
        }

        // mobile version
        var mobile = /Mobile|mini|Fennec|Android|iP(ad|od|hone)/.test(nVer);

        // cookie
        var cookieEnabled = (navigator.cookieEnabled) ? true : false;

        if (typeof navigator.cookieEnabled == 'undefined' && !cookieEnabled) {
            document.cookie = 'testcookie';
            cookieEnabled = (document.cookie.indexOf('testcookie') != -1) ? true : false;
        }

        // system
        var os = unknown;
        var clientStrings = [
            {s: 'Windows 10', r: /(Windows 10.0|Windows NT 10.0)/},
            {s: 'Windows 8.1', r: /(Windows 8.1|Windows NT 6.3)/},
            {s: 'Windows 8', r: /(Windows 8|Windows NT 6.2)/},
            {s: 'Windows 7', r: /(Windows 7|Windows NT 6.1)/},
            {s: 'Windows Vista', r: /Windows NT 6.0/},
            {s: 'Windows Server 2003', r: /Windows NT 5.2/},
            {s: 'Windows XP', r: /(Windows NT 5.1|Windows XP)/},
            {s: 'Windows 2000', r: /(Windows NT 5.0|Windows 2000)/},
            {s: 'Windows ME', r: /(Win 9x 4.90|Windows ME)/},
            {s: 'Windows 98', r: /(Windows 98|Win98)/},
            {s: 'Windows 95', r: /(Windows 95|Win95|Windows_95)/},
            {s: 'Windows NT 4.0', r: /(Windows NT 4.0|WinNT4.0|WinNT|Windows NT)/},
            {s: 'Windows CE', r: /Windows CE/},
            {s: 'Windows 3.11', r: /Win16/},
            {s: 'Android', r: /Android/},
            {s: 'Open BSD', r: /OpenBSD/},
            {s: 'Sun OS', r: /SunOS/},
            {s: 'Linux', r: /(Linux|X11)/},
            {s: 'iOS', r: /(iPhone|iPad|iPod)/},
            {s: 'Mac OS X', r: /Mac OS X/},
            {s: 'Mac OS', r: /(MacPPC|MacIntel|Mac_PowerPC|Macintosh)/},
            {s: 'QNX', r: /QNX/},
            {s: 'UNIX', r: /UNIX/},
            {s: 'BeOS', r: /BeOS/},
            {s: 'OS/2', r: /OS\/2/},
            {s: 'Search Bot', r: /(nuhk|Googlebot|Yammybot|Openbot|Slurp|MSNBot|Ask Jeeves\/Teoma|ia_archiver)/}
        ];
        for (var id in clientStrings) {
            var cs = clientStrings[id];
            if (cs.r.test(nAgt)) {
                os = cs.s;
                break;
            }
        }

        var osVersion = unknown;

        if (/Windows/.test(os)) {
            osVersion = /Windows (.*)/.exec(os)[1];
            os = 'Windows';
        }

        switch (os) {
            case 'Mac OS X':
                osVersion = /Mac OS X (10[\.\_\d]+)/.exec(nAgt)[1];
                break;

            case 'Android':
                osVersion = /Android ([\.\_\d]+)/.exec(nAgt)[1];
                break;

            case 'iOS':
                osVersion = /OS (\d+)_(\d+)_?(\d+)?/.exec(nVer);
                osVersion = osVersion[1] + '.' + osVersion[2] + '.' + (osVersion[3] | 0);
                break;
        }

        // flash (you'll need to include swfobject)
        /* script src="//ajax.googleapis.com/ajax/libs/swfobject/2.2/swfobject.js" */
        var flashVersion = 'no check';
        if (typeof swfobject != 'undefined') {
            var fv = swfobject.getFlashPlayerVersion();
            if (fv.major > 0) {
                flashVersion = fv.major + '.' + fv.minor + ' r' + fv.release;
            }
            else {
                flashVersion = unknown;
            }
        }
    }

    window.jscd = {
        screen: screenSize,
        browser: browser,
        browserVersion: version,
        browserMajorVersion: majorVersion,
        mobile: mobile,
        os: os,
        osVersion: osVersion,
        cookies: cookieEnabled,
        flashVersion: flashVersion
    };
}(this));
$(document).ready(function() {
    console.log("bypass = ");
    console.log(bypass);
    $('.pushGroup').hide();
    if (bypass === false) {
        $('body').show();
    }


    $('#r2faAuthSelect').on('change', function() {
        if (this.value == "P") {
            $('.submitBtn').html("Send");
            $('.pushGroup').show();
            $('.otpGroup').hide();
        } else {
            if (this.value == "B") {
                $('#code').attr("placeholder", "Enter your Bypass Code.");
            } else {
                $('#code').attr("placeholder", "Enter your OTP.");
            }
            $('.submitBtn').html("Submit");
            $('.pushGroup').hide();
            $('.otpGroup').show();
        }
    });



    function showStatusMessage(type, text){
        var hrefLocation = "";
        var selector = "";
        var textColor = "";
        if(type == "success"){
            hrefLocation = "wp-admin/";
            selector = "#svgViewAll";
            textColor = "#6BC452";
        }else{
            hrefLocation = "wp-login.php";
            selector = "#svgViewAllDeny";
            textColor = "#D25627";
        }
        $('.formData').hide();
        $('#pushApproveData').show();
        $('.text-left').hide();
        $(selector).addClass('iconChnage');
        // $('.card-container').css({'background-color' : '#E5F4E1'});
        $(selector).addClass('iconChnage');$('#statusText').html(text);
        $('#statusText').css({'color' : textColor});
        $('p#statusText, p#message').fadeIn(1000);
        window.location.href = hrefLocation;
    }






    $('.submitBtn').click(function(e) {
        console.log($('#r2faAuthSelect').val());
        e.preventDefault();
        $('#pushgya').hide();
        if ($('#r2faAuthSelect').val().trim() === "P") {
            $('#r2faAuthSelect').prop('disabled', true);
            $('#r2faTokenId').prop('disabled', true);
            $('#pushgya').html('Attempting to send push.');
            $('#pushgya').css({'background-color': '#dff0d8', 'color': '#3c763d'});
            $('#pushgya').animate({
                height: 'toggle'
            }, 300, function() {
            });
            var tokenId = $("#r2faTokenId").val();
            if (tokenId !== undefined) {
                $('.submitBtn').html("Please wait...");
                $('.submitBtn').attr('disabled', true);
                var ajaxFn = function() {
                    $.ajax({
                        url: ajax_object.ajax_url,
                        type: "POST",
                        data: {
                            action: 'checkpush',
                            security : ajax_object.nonce
                        },
                        success: function(response) {
                            $('#pushgya').hide();
                            var response = response.trim();
                            if(response == "success"){
                                showStatusMessage("success", "Push Approved");
                            }else if(response == "expired"){
                                showStatusMessage("failed", "Push Expired");
                            }else if(response == "denied"){
                                showStatusMessage("failed", "Push Denied");
                            }else{
                                showStatusMessage("failed", "Push Failure");
                            }
                        },
                        error: function() {
                            $('#pushgya').hide();
                            showStatusMessage("failed", "Push Error");
                        }
                    });
                };
                $.ajax({
                    url: ajax_object.ajax_url,
                    type: "POST",
                    data: {
                        tokenId: tokenId,
                        os: jscd.os + ' ' + jscd.osVersion,
                        mobile: jscd.mobile,
                        browser: jscd.browser + ' ' + jscd.browserMajorVersion,
                        action: 'sendpush',
                        security : ajax_object.nonce
                    },
                    success: function(response) {
                        if (response.trim() == 'success') {
                            $('#pushgya').html("Push Notification has been sent.");
                            $('#pushgya').css({'background-color': '#dff0d8', 'color': '#3c763d'});
                            ajaxFn();
                        } else {
                            $('#pushgya').html("Failed to send Push Notification.");
                            $('#pushgya').css({'background-color': '#fbe9eb', 'color': '#e34c5e'});
                        }
                    }
                });
            }
        } else {
            $.ajax({
                url: ajax_object.ajax_url,
                type: "POST",
                data: {
                    r2faAuthSelect: $('#r2faAuthSelect').val(),
                    code: $('#code').val(),
                    action: 'checkotp',
                    security : ajax_object.nonce
                },
                success: function(response) {
                    var hrefLocation = "";
                    var selector = "";
                    var textColor = "";
                    var text = "";
                    if(response == "success"){
                        showStatusMessage("success", "Correct OTP");
                    }else{
                        showStatusMessage("failure", "Incorrect OTP");
                    }
                }
            });
        }
    });

});