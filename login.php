<?php
ob_start();
session_start();
require_once 'api/vendor/autoload.php';
require_once 'api/controller/database.php';
require_once 'api/controller/setting.php';
$setting    = new setting();
$variable   = $setting->getAgentVariables();
if (isset($_SESSION['biAgent_admin']) && isset($_SESSION['biAgent_session'])) {header('location: '.$variable->site.'/index');}
?>
<!DOCTYPE html>
<html lang="en" data-bs-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="description" content="Bariatric Istanbul CRM">
    <meta name="author" content="Bariatric Istanbul">
    <title>Bariatric Istanbul CRM</title>
    <link rel="stylesheet" href="https://use.typekit.net/lyu3lrq.css">
    <link rel="stylesheet" href="<?=$variable->cdn;?>/vendors/core/core.css">
    <link rel="stylesheet" href="<?=$variable->cdn;?>/vendors/flatpickr/flatpickr.min.css">
    <link rel="stylesheet" href="<?=$variable->cdn;?>/fonts/feather-font/css/iconfont.css">
    <link rel="stylesheet" href="<?=$variable->cdn;?>/vendors/flag-icon-css/css/flag-icon.min.css">
    <link rel="stylesheet" href="<?=$variable->cdn;?>/vendors/select2/select2.min.css">
    <link rel="stylesheet" href="<?=$variable->cdn;?>/vendors/flatpickr/flatpickr.min.css">
    <link rel="stylesheet" href="<?=$variable->cdn;?>/vendors/datatables.net-bs5/dataTables.bootstrap5.css">
    <link rel="stylesheet" href="<?=$variable->cdn;?>/css/demo1/style.css">
    <link rel="stylesheet" href="<?=$variable->cdn;?>/css/general.css">
    <link rel="stylesheet" href="https://cdn.bariatricistanbul.com/assets/webfont/fontawesome/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.8/css/intlTelInput.min.css" integrity="sha512-yye/u0ehQsrVrfSd6biT17t39Rg9kNc+vENcCXZuMz2a+LWFGvXUnYuWUW6pbfYj1jcBb/C39UZw2ciQvwDDvg==" crossorigin="anonymous" />
    <link rel="shortcut icon" href="https://www.bariatricistanbul.com.tr/favicon.ico">
</head>
<body>
<div class="main-wrapper">
    <div class="page-wrapper full-page">
        <div class="page-content d-flex align-items-center justify-content-center">

            <div class="row w-100 mx-0 auth-page">
                <div class="col-md-10 col-lg-8 col-xl-6 mx-auto">
                    <div class="card">
                        <div class="row">
                            <div class="col-md-4 pe-md-0">
                                <div class="auth-side-wrapper">

                                </div>
                            </div>
                            <div class="col-md-8 ps-md-0">
                                <div class="auth-form-wrapper px-4 py-5">
                                    <a href="<?=$variable->site;?>/login" class="nobleui-logo d-block mb-2">bariatric<span>istanbul</span></a>
                                    <h5 class="text-secondary fw-normal mb-4">Welcome back! Log in to your system.</h5>
                                    <div class="" id="results"></div>
                                    <form action="<?=$variable->site;?>/action/login" method="post" class="forms-sample" id="login" enctype="multipart/form-data">
                                        <div class="mb-3">
                                            <label for="username" class="form-label">Username</label>
                                            <input type="text" name="username" class="form-control" id="username" placeholder="Username">
                                        </div>
                                        <div class="mb-3">
                                            <label for="userPassword" class="form-label">Password</label>
                                            <input type="password" name="password" class="form-control" id="userPassword" autocomplete="current-password" placeholder="Password">
                                        </div>
                                        <div>
                                            <button type="submit" class="btn btn-danger me-2 mb-2 mb-md-0 text-white">Login</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

<script src="<?=$variable->cdn;?>/vendors/core/core.js"></script>
<script src="<?=$variable->cdn;?>/vendors/jquery/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/gh/jquery-form/form@4.3.0/dist/jquery.form.min.js" integrity="sha384-qlmct0AOBiA2VPZkMY3+2WqkHtIQ9lSdAsAn5RUJD/3vA5MKDgSGcdmIv4ycVxyn" crossorigin="anonymous"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.devbridge-autocomplete/1.4.11/jquery.autocomplete.min.js" integrity="sha512-uxCwHf1pRwBJvURAMD/Gg0Kz2F2BymQyXDlTqnayuRyBFE7cisFCh2dSb1HIumZCRHuZikgeqXm8ruUoaxk5tA==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
<script src="<?=$variable->cdn;?>/vendors/feather-icons/feather.min.js"></script>
<script src="<?=$variable->cdn;?>/js/app.js"></script>
<script>
    $('#login').ajaxForm({
        beforeSubmit: function(arr, $form, options) {
            $('#login button').prop('disabled', true);
            $('#login button').html('<i class="fal fa-spinner-third fa-spin"></i> <span>Processing..</span>');
        },
        success:function (e) {
            $("html, body").animate({ scrollTop: 0 }, "slow");
            if(e.status==1) {
                $("#results").addClass('alert alert-success');
                $("#results").html('<i class="fad fa-check-circle" style="--fa-primary-color: #ffffff; --fa-primary-opacity: 0.8; --fa-secondary-color: #23a500; --fa-secondary-opacity: 1;"></i> '+e.message);
                setTimeout(function () {location.reload()}, 3500);
            }else{
                $("#results").addClass('alert alert-danger');
                $("#results").html('<i class="fad fa-times-circle" style="--fa-primary-color: #ffffff; --fa-secondary-color: #db0000; --fa-secondary-opacity: 1;"></i> '+e.message);
                setTimeout(function () {location.reload()}, 3000);
            }
            $('#login button').prop('disabled', false);
            $('#login button').html('Login');
        },
        type        :'POST',
        dataType    :'json',
        clearFrom   : true
    });
</script>
</body>
</html>