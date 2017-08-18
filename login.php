<?php
require_once('init.php');
require_once('config.php');
require_once('session.php');
require_once('template.php');
startSession();
checkUserIP();

outputLoginTemplateHeader();
echo '<form action="login_backend.php" method="post" class="standard">
	<label for="username">Username:</label>
	<input id="username" type="text" name="username" autocomplete="on" autofocus="autofocus">
	<label for="password">Password:</label>
	<input id="password" type="password" name="password">
	<input type="submit" name="submit" autocomplete="current-password" value="Log In">
';
@include('config.php');
if (defined('GD_FILEMANAGER_MOTD')) {
	echo '<div class="motd">'.GD_FILEMANAGER_MOTD.'</div>
';
}

echo '</form>';

outputLoginTemplateFooter();
