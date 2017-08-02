<?php
require_once('init.php');

require_once('config.php');
require_once('session.php');
require_once('database.php');
require_once('auth.php');
startSession();
checkUserIP();
authenticate();

if (!inGroup('root'))
{
	http_response_code(401);
	exit('This account doesn\'t have the administrator permission!<br /><a href="index.php">Back to main listing</a><br /><a href="logout.php">Log Out</a>');
}

// returns an array of ints
function sanitizeCsvInts($raw_csv_text)
{
	$raw_vals = explode(',', $raw_csv_text);
	$clean_vals = array();
	foreach ($raw_vals as $val)
	{
		if (is_numeric($val))
		{
			$clean_vals[] = (int)$val;
		}
	}
	return $clean_vals;
}

// returns a string of strings
function sanitizeCsvStrings($raw_csv_text)
{
	$raw_vals = explode(',', $raw_csv_text);
	$clean_vals = array();
	foreach ($raw_vals as $val)
	{
		if ($val !== '')
		{
			$clean_vals[] = (string)$val;
		}
	}
	return implode(',', $clean_vals);
}

$GLOBALS['error_msg'] = 'Unknown error';

function create_user($name, $password1, $password2, $groups)
{
	
	if ($password1 === $password2)
	{
		$pass_hash = hash('sha512', $password1);
		return dbExec('INSERT INTO "USERS" ("NAME", "PASSWORD_SHA512", "GROUPS") VALUES (\''.SQLite3::escapeString($name).'\', \''.SQLite3::escapeString($pass_hash).'\', \''.SQLite3::escapeString(sanitizeCsvStrings($groups)).'\');');
	}
	else
	{
		$GLOBALS['error_msg'] = 'The passwords do not match';
		return false;
	}
}
function change_user_name($id, $name)
{
	return dbExec('UPDATE "USERS" SET "NAME" = \''.SQLite3::escapeString($name).'\' WHERE "ID" = '.$id.';');
}
function change_user_password($id, $password1, $password2)
{
	if ($password1 === $password2)
	{
		$pass_hash = hash('sha512', $password1);
		return dbExec('UPDATE "USERS" SET "PASSWORD_SHA512" = \''.SQLite3::escapeString($pass_hash).'\' WHERE "ID" = '.$id.';');
	}
	else
	{
		$GLOBALS['error_msg'] = 'The passwords do not match';
		return false;
	}
}
function change_user_groups($id, $groups)
{
	return dbExec('UPDATE "USERS" SET "GROUPS" = \''.SQLite3::escapeString(sanitizeCsvStrings($groups)).'\' WHERE "ID" = '.$id.';');
}
function delete_user($id)
{
	return dbExec('DELETE FROM "USERS" WHERE "ID" = \''.$id.'\';');
}
function login_as_user($id)
{
	//TODO: modify the authentication so I don't have to do this
	// for now, mimic the login backend with a correct password hash
	$user_creds = dbQuery('SELECT "NAME", "PASSWORD_SHA512" FROM "USERS" WHERE "ID" = '.$id.';');
	session_unset();
	$_SESSION['username'] = $user_creds[0]['NAME'];
	$_SESSION['password_hashed'] = $user_creds[0]['PASSWORD_SHA512'];
	header('Location: index.php');
	exit();
}
function create_share($name, $path, $groups_visible, $groups_access, $groups_modify)
{
	return dbExec('INSERT INTO "SHARES" ("NAME", "PATH", "GROUPS_VISIBLE", "GROUPS_ACCESS_FILES", "GROUPS_MODIFY_FILES") VALUES (\''.SQLite3::escapeString($name).'\', \''.SQLite3::escapeString($path).'\', \''.SQLite3::escapeString(sanitizeCsvStrings($groups_visible)).'\', \''.SQLite3::escapeString(sanitizeCsvStrings($groups_access)).'\', \''.SQLite3::escapeString(sanitizeCsvStrings($groups_modify)).'\');');
}
function change_share_name($id, $name)
{
	return dbExec('UPDATE "SHARES" SET "NAME" = \''.SQLite3::escapeString($name).'\' WHERE "ID" = '.$id.';');
}
function change_share_path($id, $path)
{
	return dbExec('UPDATE "SHARES" SET "PATH" = \''.SQLite3::escapeString($path).'\' WHERE "ID" = '.$id.';');
}
function change_share_groups($id, $groups_visible, $groups_access, $groups_modify)
{
	return dbExec('UPDATE "SHARES" SET "GROUPS_VISIBLE" = \''.SQLite3::escapeString(sanitizeCsvStrings($groups_visible)).'\', "GROUPS_ACCESS_FILES" = \''.SQLite3::escapeString(sanitizeCsvStrings($groups_access)).'\', "GROUPS_MODIFY_FILES" = \''.SQLite3::escapeString(sanitizeCsvStrings($groups_modify)).'\' WHERE "ID" = '.$id.';');
}
function delete_share($id)
{
	return dbExec('DELETE FROM "SHARES" WHERE "ID" = '.$id.';');
}

function processAdminInput()
{
	if (!isset($_POST['user_ids'], $_POST['share_ids']))
	{
		header('Location: admin.php?msg='.urlencode('The form was not recieved properly. Nothing has been changed. Please try again.'));
		exit();
	}
	else
	{
		// find which button was clicked
		$user_ids = sanitizeCsvInts($_POST['user_ids']);
		$share_ids = sanitizeCsvInts($_POST['share_ids']);
		
		// check the create user button
		if (isset($_POST['create_user']))
		{
			if (isset($_POST['create_user_name'],
					$_POST['create_user_password1'],
					$_POST['create_user_password2'],
					$_POST['create_user_groups']))
			{
				if ($_POST['create_user_name'] !== '')
				{
					return create_user($_POST['create_user_name'],
							$_POST['create_user_password1'],
							$_POST['create_user_password2'],
							$_POST['create_user_groups']);
				}
				$GLOBALS['error_msg'] = 'Username cannot be blank';
				return false;
			}
			return false;
		}
		
		// check the modify user buttons
		foreach ($user_ids as $user_id)
		{
			if (isset($_POST['change_user_name_'.$user_id]))
			{
				if (isset($_POST['user_name_'.$user_id]))
				{
					if ($_POST['user_name'] !== '')
					{
						return change_user_name($user_id,
								$_POST['user_name_'.$user_id]);
					}
					$GLOBALS['error_msg'] = 'Username cannot be blank';
					return false;
				}
				return false;
			}
			else if (isset($_POST['change_user_password_'.$user_id]))
			{
				if (isset($_POST['user_password1_'.$user_id],
						$_POST['user_password2_'.$user_id]))
				{
					return change_user_password($user_id,
							$_POST['user_password1_'.$user_id],
							$_POST['user_password2_'.$user_id]);
				}
				return false;
			}
			else if (isset($_POST['change_user_groups_'.$user_id]))
			{
				if (isset($_POST['user_groups_'.$user_id]))
				{
					return change_user_groups($user_id,
							$_POST['user_groups_'.$user_id]);
				}
				return false;
			}
			else if (isset($_POST['delete_user_'.$user_id]))
			{
				return delete_user($user_id);
			}
			else if (isset($_POST['login_as_user_'.$user_id]))
			{
				return login_as_user($user_id);
			}
		}
		
		// check the create share button
		if (isset($_POST['create_share']))
		{
			if (isset($_POST['create_share_name'],
					$_POST['create_share_path'],
					$_POST['create_share_groups_visible'],
					$_POST['create_share_groups_access'],
					$_POST['create_share_groups_modify']))
			{
				if ($_POST['create_share_name'] !== '' &&
						$_POST['create_share_path'] !== '')
				{
					return create_share($_POST['create_share_name'],
						$_POST['create_share_path'],
						$_POST['create_share_groups_visible'],
						$_POST['create_share_groups_access'],
						$_POST['create_share_groups_modify']);
				}
				$GLOBALS['error_msg'] = 'Share name and path cannot be blank';
				return false;
			}
			return false;
		}
		
		// check the modify share buttons
		foreach ($share_ids as $share_id)
		{
			if (isset($_POST['change_share_name_'.$share_id]))
			{
				if (isset($_POST['share_name_'.$share_id]))
				{
					if ($_POST['share_name_'.$share_id] !== '')
					{
						return change_share_name($share_id,
								$_POST['share_name_'.$share_id]);
					}
					$GLOBALS['error_msg'] = 'Share name cannot be blank';
					return false;
				}
				return false;
			}
			else if (isset($_POST['change_share_path_'.$share_id]))
			{
				if (isset($_POST['share_path_'.$share_id]))
				{
					if ($_POST['share_path_'.$share_id] !== '')
					{
						return change_share_path($share_id,
								$_POST['share_path_'.$share_id]);
					}
					else
					{
						$GLOBALS['error_msg'] = 'Share path cannot be blank';
						return false;
					}
				}
				return false;
			}
			else if (isset($_POST['change_share_groups_'.$share_id]))
			{
				if (isset($_POST['share_groups_visible_'.$share_id],
						$_POST['share_groups_access_'.$share_id],
						$_POST['share_groups_modify_'.$share_id]))
				{
					return change_share_groups($share_id,
							$_POST['share_groups_visible_'.$share_id],
							$_POST['share_groups_access_'.$share_id],
							$_POST['share_groups_modify_'.$share_id]);
				}
				return false;
			}
			else if (isset($_POST['delete_share_'.$share_id]))
			{
				return delete_share($share_id);
			}
		}
		
		return false;
	}
}

if (processAdminInput() === true)
{
	// reload shares
	$GLOBALS['shares'] = dbQuery('select * from "SHARES";');
	
	header('Location: admin.php?msg='.urlencode('The last action completed successfully.'));
	exit();
}
else
{
	header('Location: admin.php?msg='.urlencode('An error occurred performing the last action, nothing has been changed. ('.$GLOBALS['error_msg'].')'));
	exit();
}
