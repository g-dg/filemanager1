<?php
require_once('init.php');

require_once('config.php');
require_once('session.php');
require_once('database.php');
require_once('auth.php');
require_once('template.php');
startSession();
checkUserIP();
authenticate();

outputStandardTemplateHeader("My Account");

$body = '<a href="'.htmlentities(pathinfo($_SERVER['SCRIPT_NAME'])['dirname'] . '/').'">&lt; Back to main listing</a>
<br />
<br />
';
if (isset($_GET['msg']))
{
	$body .= '<div style="font-size: large;">'.htmlentities($_GET['msg']).'</div>
<br />
';
}
$body .= '<form action="account_backend.php" method="post" class="standard">
	<fieldset>
		<legend>Username</legend>
		<code>
			'.htmlentities($_SESSION['username']).'
		</code>
		<!-- (User ID: '.htmlentities($_SESSION['user_id']).') -->
	</fieldset>
';
if ($_SESSION['username'] !== GD_FILEMANAGER_GUEST_USER)
{
	$body .='	<fieldset>
		<legend>Password</legend>
		<label>
			Current Password:
		</label>
		<input type="password" name="old_password" value="" autofocus="autofocus">
		<label>
			New Password: 
		</label>
		<input type="password" name="new_password1" value="">
		<label>
			New Password (again): 
		</label>
		<input type="password" name="new_password2" value="">
		<input type="submit" name="submit" value="Change Password" style="width: 100%;">
	</fieldset>';
}
	$body .= '	<fieldset>
		<legend>Groups</legend>
		<code>
			'.htmlentities(implode(', ', $_SESSION['groups'])).'
		</code>
	</fieldset>
</form>';

echo $body;

outputStandardTemplateFooter();
