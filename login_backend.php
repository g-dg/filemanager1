<?php
require_once('init.php');
require_once('config.php');
require_once('session.php');
startSession();
checkUserIP();

// check the CSRF token
if (isset($_POST['csrf_token']) && $_POST['csrf_token'] === $_SESSION['csrf_token'])
{
	unset($_SESSION['csrf_token']);
	$_SESSION['username'] = $_POST['username'];
	$_SESSION['password_hashed'] = hash('sha512', $_POST['password']);
	header('Location: '.pathinfo($_SERVER['SCRIPT_NAME'])['dirname'].'/');
	session_write_close();
	exit();
}
else
{
	unset($_SESSION['csrf_token']);
	echo 'Attempted CSRF attack detected!';
	exit();
}
