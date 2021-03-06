<?php 
if (!defined('GD_FILEMANAGER_VERSION'))
{
	http_response_code(403);
	exit('Error! No direct script access allowed!');
}

require_once('session.php');
startSession();
checkUserIP();

// authenticate the user
function authenticate()
{
	// authenticate only if the user is not yet authenticated
	if (!isset($_SESSION['user_id']))
	{
		// check if there is a username and password, it doesn't mean they are authenticated
		if (!isset($_SESSION['username']))
		{
			session_unset();
			header('Location: '.pathinfo($_SERVER['SCRIPT_NAME'])['dirname'].'/login.php?redir='.urlencode($_SERVER['REQUEST_URI']));
			exit();
		}

		// check if user exists, and if the password hash matches that in database
		$user_id = null;
		
		// while we're at it, we can get the groups as well
		$groups = array();
		
		// actually run the query
		$result_array = dbQuery('select * from "USERS" where "NAME" = \''.SQLite3::escapeString($_SESSION['username']).'\';');

		// this is no longer necessary, as the usernames are unique.
		foreach ($result_array as $row)
		{
			if ($_SESSION['username'] === $row['NAME'] &&
					$_SESSION['password_hashed'] === $row['PASSWORD_SHA512'])
			{
				$_SESSION['user_id'] = $row['ID'];
				$_SESSION['groups'] = explode(',', trim($row['GROUPS'], ','));
			}
		}
		
		// we don't want any accidental access to even a hashed password, as there is no salt or anything
		unset($_SESSION['password_hashed']);

		if (is_null($_SESSION['user_id']))
		{
			// auth failed
			session_unset();
			$_SESSION['msg'] = 'Incorrect username or password.';
			header('Location: '.pathinfo($_SERVER['SCRIPT_NAME'])['dirname'].'/login.php?redir='.urlencode($_SERVER['REQUEST_URI']));
			exit();
		}
		
		// return that the authentication was successful
		return true;
	}
}

// for checking if a user is in a group
function inGroup($check_group)
{
	foreach ($_SESSION['groups'] as $group)
	{
		if ($group === $check_group)
		{
			return true;
		}
	}
	return false;
}
