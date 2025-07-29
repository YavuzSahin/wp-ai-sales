<?php
ob_start();
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
require_once 'api/vendor/autoload.php';
require_once 'api/controller/database.php';
require_once 'api/controller/setting.php';
$db         = database::connect();
$setting    = new setting();
$variables  = $setting->getAgentVariables();

$db->table('agents')->where('id', $_SESSION['biAgent_admin'])->update(['login'=>0]);

unlink($_SESSION['biAgent_admin']);
unlink($_SESSION['biAgent_type']);
unlink($_SESSION['biAgent_role']);
unlink($_SESSION['biAgent_session']);

session_destroy();
header('location: '.$variables->site.'/login');