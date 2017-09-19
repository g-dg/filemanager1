<?php
require_once('init.php');
require_once('config.php');
require_once('session.php');
require_once('template.php');
startSession();
checkUserIP();

if (isset($_SESSION['user_id'])
{
	header('Location: index.php');
	exit();
}

// set CSRF token
$_SESSION['csrf_token'] = '';
for ($i = 0; $i < 32; $i++) {
	$_SESSION['csrf_token'] .= substr('0123456789abcdef', mt_rand(0, 15), 1);
}

echo '<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="UTF-8">
		<meta http-equiv="X-UA-Compatible" content="IE=edge">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<link rel="icon" href="'.htmlentities(pathinfo($_SERVER['SCRIPT_NAME'])['dirname'].'/favicon.ico').'">
		<title>Log In to Garnet DeGelder\'s File Manager</title>
		<link rel="stylesheet" type="text/css" href="'.htmlentities(pathinfo($_SERVER['SCRIPT_NAME'])['dirname'].'/style.css').'">
	</head>
	<body class="login">
		<div class="login-container">
			<div class="header">
				<div class="title">
					Log in to Garnet DeGelder\'s File Manager on '.htmlentities($_SERVER['HTTP_HOST']).'
				</div>
			</div>
			<div class="content">
				<form action="login_backend.php" method="post" class="login">
					<input id="username" placeholder="Username" type="text" name="username" autocomplete="on" autofocus="autofocus">
					<input id="password" placeholder="Password" type="password" name="password">
					<input type="submit" name="submit" autocomplete="current-password" value="Log In">
';

if (isset($_SESSION['msg']))
{
	echo '					<div class="msg">'.htmlentities($_SESSION['msg']).'</div>
';
	unset($_SESSION['msg']);
}

if (defined('GD_FILEMANAGER_MOTD')) {
	echo '					<div class="motd">
						'.GD_FILEMANAGER_MOTD.'
					</div>
';
}

echo '					<input type="hidden" name="csrf_token" value="'.htmlentities($_SESSION['csrf_token']).'">
				</form>
			</div>
			<div class="footer">
				Garnet DeGelder\'s File Manager '.htmlentities(GD_FILEMANAGER_VERSION).'
				Copyright &copy; 2017 Garnet DeGelder
';
if (GD_FILEMANAGER_PROFILER_ENABLE)
{
	echo '			<br />
			';
	getColourMemUsed();
	echo '
			';
	getColourExecTime();
	echo '
';
}
echo '		</div>
	</body>
</html>
';
