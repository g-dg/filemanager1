<?php
require_once('init.php');
require_once('config.php');
require_once('session.php');
startSession();
checkUserIP();

session_unset();
//session_regenerate_id(true);
//setcookie(session_name(), '', time() - 1);
$_SESSION['msg'] = 'User logged out.';
header('Location: '.pathinfo($_SERVER['SCRIPT_NAME'])['dirname'].'/login.php');
exit();
