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
	if (isset($_GET['redir']))
	{
		header('Location: '.$_GET['redir']);
	}
	else
	{
		header('Location: index.php');
	}
	exit();
}
else
{
	unset($_SESSION['csrf_token']);
	$_SESSION['msg'] = 'Possible attempted CSRF attack detected!';
	header('Location: login.php');
	exit();
}
