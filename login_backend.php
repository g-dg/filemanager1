<?php
require_once('init.php');
require_once('config.php');
require_once('session.php');
startSession();
checkUserIP();

$_SESSION['username'] = $_POST['username'];
$_SESSION['password_hashed'] = hash('sha512', $_POST['password']);
header('Location: '.pathinfo($_SERVER['SCRIPT_NAME'])['dirname'].'/');
session_write_close();
exit();
