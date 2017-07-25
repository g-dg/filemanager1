<?php
define('GD_FILEMANAGER_VERSION', '1.0.1');

require_once('config.php');
require_once('session.php');
require_once('database.php');
require_once('auth.php');
startSession();
checkUserIP();
authenticate();

if ($_SESSION['username'] !== GD_FILEMANAGER_GUEST_USER)
{
	if (isset($_POST['old_password'], $_POST['new_password1'], $_POST['new_password2']) &&
			$_POST['new_password1'] === $_POST['new_password2'])
	{
		$user_id = $_SESSION['user_id'];
		
		$old_pass = dbQuery('SELECT "PASSWORD_SHA512" FROM "USERS" WHERE "ID" = '.$user_id.';');
		if (hash('sha512', $_POST['old_password']) !== $old_pass[0]['PASSWORD_SHA512'])
		{
			header('Location: account.php?msg='.urlencode('The current password is incorrect!'));
			exit();
		}
		
		$pw_hash = hash('sha512', $_POST['new_password1']);
		if (dbExec('UPDATE "USERS" SET "PASSWORD_SHA512" = \''.SQLite3::escapeString($pw_hash).'\' WHERE "ID" = '.$user_id.';'))
		{
			header('Location: account.php?msg='.urlencode('Your password has been updated successfully.'));
		}
		else
		{
			header('Location: account.php?msg='.urlencode('An error occurred when updating your password. Your password probably is unchanged. If you cannot access your account, contact the administrator.'));
		}
	}
	else
	{
		header('Location: account.php?msg='.urlencode('The passwords don\'t match!'));
		exit();
	}
}
else
{
	header('Location: account.php?msg='.urlencode('The guest user cannot change the password!'));
	exit();
}
