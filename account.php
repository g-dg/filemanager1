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

// set CSRF token
$_SESSION['csrf_token'] = '';
for ($i = 0; $i < 32; $i++) {
	$_SESSION['csrf_token'] .= substr('0123456789abcdef', mt_rand(0, 15), 1);
}

outputStandardTemplateHeader("My Account");

echo '<a href="'.htmlentities(pathinfo($_SERVER['SCRIPT_NAME'])['dirname'] . '/').'">&lt; Back to main listing</a>
<br />
<br />
';
if (isset($_GET['msg']))
{
	echo '<div style="font-size: large;">'.htmlentities($_GET['msg']).'</div>
<br />
';
}
echo '<form action="account_backend.php" method="post" class="standard">
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
	echo '	<fieldset>
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
	echo '	<fieldset>
		<legend>Groups</legend>
		<code>
			'.htmlentities(implode(', ', $_SESSION['groups'])).'
		</code>
	</fieldset>
	<input type="hidden" name="csrf_token" value="'.htmlentities($_SESSION['csrf_token']).'">
</form>';

outputStandardTemplateFooter();
