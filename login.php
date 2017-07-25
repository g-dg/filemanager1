<?php
define('GD_FILEMANAGER_VERSION', '1.0.2');
?><!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="UTF-8"></meta>
		<title>Log In to Garnet DeGelder's File Manager</title>
	</head>
	<body>
		<div style="position: absolute; left: 50%; transform: translate(-50%, 0);top: 5%; margin-bottom: 20px; max-width: 480px;">
			<form action="login_backend.php" method="post" >
				<div style="font-size: x-large;">Log In to Garnet DeGelder's File Manager</div>
				<hr />
				<table>
					<tr>
						<td><label for="username">Username:</label></td>
						<td>
							<input id="username" type="text" name="username" autocomplete="on" autofocus="autofocus"></input>
						</td>
					</tr>
					<tr>
						<td><label for="password">Password:</label></td>
						<td>
							<input id="password" type="password" name="password"></input>
						</td>
					</tr>
					<tr>
						<td></td>
						<td>
							<input type="submit" name="submit" autocomplete="current-password" value="Log In"></input>
						</td>
					</tr>
				</table>
				<hr />
<?php
	@include('config.php');
	if (defined('GD_FILEMANAGER_MOTD')) {
		echo GD_FILEMANAGER_MOTD;
	}
?>
			</form>
		</div>
	</body>
</html>
