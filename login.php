<?php
require_once('version.php');
?><!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="UTF-8"></meta>
		<meta name="viewport" content="width=device-width"></meta>
		<title>Log In to Garnet DeGelder's File Manager</title>
		<link rel="stylesheet" type="text/css" href="<?=htmlentities(pathinfo($_SERVER['SCRIPT_NAME'])['dirname'].'/style.css');?>"></link>
	</head>
	<body class="login">
		<div class="header">
			<div class="title">
				Log in to Garnet DeGelder's File Manager on <?=htmlentities($_SERVER['HTTP_HOST']);?>
			</div>
		</div>
		<div class="content">
			<form action="login_backend.php" method="post" class="standard">
				<label for="username">Username:</label>
				<input id="username" type="text" name="username" autocomplete="on" autofocus="autofocus"></input>
				<label for="password">Password:</label>
				<input id="password" type="password" name="password"></input>
				<input type="submit" name="submit" autocomplete="current-password" value="Log In"></input>
<?php
	@include('config.php');
	if (defined('GD_FILEMANAGER_MOTD')) {
		echo '<div class="motd">';
		echo GD_FILEMANAGER_MOTD;
		echo '</div>';
	}
?>
			</form>
		</div>
		<div class="footer">
			Copyright &copy; 2017  Garnet DeGelder
		</div>
	</body>
</html>
