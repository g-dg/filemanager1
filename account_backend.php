<?php
require_once('init.php');

require_once('config.php');
require_once('session.php');
require_once('database.php');
require_once('auth.php');
startSession();
checkUserIP();
authenticate();

// check the CSRF token
if (isset($_POST['csrf_token']) && $_POST['csrf_token'] === $_SESSION['csrf_token'])
{
	unset($_SESSION['csrf_token']);
	if ($_SESSION['username'] !== GD_FILEMANAGER_GUEST_USER)
	{
		if (isset($_POST['old_password'], $_POST['new_password1'], $_POST['new_password2']) &&
				$_POST['new_password1'] === $_POST['new_password2'])
		{
			$user_id = $_SESSION['user_id'];
			
			$old_pass = dbQuery('SELECT "PASSWORD_SHA512" FROM "USERS" WHERE "ID" = '.$user_id.';');
			if (hash('sha512', $_POST['old_password']) !== $old_pass[0]['PASSWORD_SHA512'])
			{
				$_SESSION['msg'] = 'The current password is incorrect!';
				header('Location: account.php');
				exit();
			}
			
			$pw_hash = hash('sha512', $_POST['new_password1']);
			if (dbExec('UPDATE "USERS" SET "PASSWORD_SHA512" = \''.SQLite3::escapeString($pw_hash).'\' WHERE "ID" = '.$user_id.';'))
			{
				$_SESSION['msg'] = 'Your password has been updated successfully.';
				header('Location: account.php');
				exit();
			}
			else
			{
				$_SESSION['msg'] = 'An error occurred when updating your password. Your password is probably unchanged. If you cannot access your account, contact the administrator.';
				header('Location: account.php');
				exit();
			}
		}
		else
		{
			$_SESSION['msg'] = 'The passwords don\'t match!';
			header('Location: account.php');
			exit();
		}
	}
	else
	{
		$_SESSION['msg'] = 'The guest user cannot change the password!';
		header('Location: account.php');
		exit();
	}
}
else
{
	unset($_SESSION['csrf_token']);
	$_SESSION['msg'] = 'Attempted CSRF attack detected!';
	header('Location: account.php');
	exit();
}
