<?php
require_once('init.php');
require_once('template.php');

echo getLoginTemplateHeader();
echo '<form action="login_backend.php" method="post" class="standard">
	<label for="username">Username:</label>
	<input id="username" type="text" name="username" autocomplete="on" autofocus="autofocus"></input>
	<label for="password">Password:</label>
	<input id="password" type="password" name="password"></input>
	<input type="submit" name="submit" autocomplete="current-password" value="Log In"></input>
';
@include('config.php');
if (defined('GD_FILEMANAGER_MOTD')) {
	echo '<div class="motd">'.GD_FILEMANAGER_MOTD.'</div>
';
}

echo '</form>';

echo getLoginTemplateFooter();
