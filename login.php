<?php
require_once('init.php');
require_once('config.php');
require_once('session.php');
require_once('template.php');
startSession();
checkUserIP();

// set CSRF token
$_SESSION['csrf_token'] = '';
for ($i = 0; $i < 32; $i++) {
	$_SESSION['csrf_token'] .= substr('0123456789abcdef', mt_rand(0, 15), 1);
}

outputLoginTemplateHeader();
echo '<form action="login_backend.php" method="post" class="standard">
	<input id="username" placeholder="Username" type="text" name="username" autocomplete="on" autofocus="autofocus">
	<input id="password" placeholder="Password" type="password" name="password">
	<input type="submit" name="submit" autocomplete="current-password" value="Log In">
';

if (isset($_SESSION['msg']))
{
	echo '<div class="msg">'.htmlentities($_SESSION['msg']).'</div>
';
	unset($_SESSION['msg']);
}

if (defined('GD_FILEMANAGER_MOTD')) {
	echo '	<div class="motd">'.GD_FILEMANAGER_MOTD.'</div>
';
}

echo '	<input type="hidden" name="csrf_token" value="'.htmlentities($_SESSION['csrf_token']).'">
</form>';

outputLoginTemplateFooter();
