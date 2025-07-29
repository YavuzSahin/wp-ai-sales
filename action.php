<?php
ob_start();
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
require 'api/vendor/autoload.php';
require 'api/controller/database.php';
require 'api/controller/setting.php';
require 'api/controller/patient.php';
require 'api/controller/whatsapp.php';
require 'api/controller/leads.php';
$db         = database::connect();

switch ($_GET['process']) {
    case'login':
        $username = $_POST['username'];
        $password = $_POST['password'];
        $passport = sha1(md5($password));

        $user = $db->table('agents')->where('username', $username)->where('password', $passport)->get();

        if (count((array)$user) <= 0) {
            $msg = ['status' => 0, 'message' => '<strong>Error Occurred!</strong><br>No accounts matching these criteria were found! Please check and try again.'];
        } else {
            $_SESSION['biAgent_admin'] = $user->id;
            $_SESSION['biAgent_type'] = $user->type;
            $_SESSION['biAgent_role'] = $user->role;
            $_SESSION['biAgent_session'] = session_id();
            $db->table('agents')->where('id', $user->id)->update(['login'=>1, 'last_login'=>date('Y-m-d H:i:s')]);
            $msg = ['status' => 1, 'message' => '<strong>Login Successful!!</strong><br>Your login process has been completed successfully. We wish you good work. <strong>' . $user->name . '</strong>'];
        }
        echo json_encode($msg);
        break;
    case'checkDateAvailability':
        $date = $_POST['date'];
        $msg = ['status' => 1, 'date' => $date, 'color' => '#eeeeee'];
        echo json_encode($msg);
        break;
}