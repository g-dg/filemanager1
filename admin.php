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

if (!inGroup('root'))
{
	http_response_code(401);
	exit('This account doesn\'t have the administrator permission!<br /><a href="index.php">Back to main listing</a><br /><a href="logout.php">Log Out</a>');
}

// set CSRF token
$_SESSION['csrf_token'] = '';
for ($i = 0; $i < 32; $i++) {
	$_SESSION['csrf_token'] .= substr('0123456789abcdef', mt_rand(0, 15), 1);
}

$GLOBALS['all_users'] = dbQuery('SELECT "ID", "NAME", "GROUPS" FROM "USERS";');
$GLOBALS['all_shares'] = dbQuery('SELECT "ID", "NAME", "PATH", "GROUPS_VISIBLE", "GROUPS_ACCESS_FILES", "GROUPS_MODIFY_FILES" FROM "SHARES";');

// sort the users and shares
usort($GLOBALS['all_users'], function($a,$b){return strcasecmp($a['NAME'],$b['NAME']);});
usort($GLOBALS['all_shares'], function($a,$b){return strcasecmp($a['NAME'],$b['NAME']);});

function outputUserList()
{
	$user_ids = array();
	echo '<table><thead><tr>'.
			'<th>Username</th>'.
			'<th>Change Username</th>'.
			'<th>&nbsp;</th>'.
			'<th>Password</th>'.
			'<th>Password (again)</th>'.
			'<th>Change Password</th>'.
			'<th>&nbsp;</th>'.
			'<th>Groups</th>'.
			'<th>Update Groups</th>'.
			'<th>&nbsp;</th>'.
			'<th>Delete User</th>'.
			'<th>Log In</th>'.
			'</tr></thead><tbody>';
	foreach ($GLOBALS['all_users'] as $user)
	{
		$user_ids[] = $user['ID'];
		echo '<tr>'.
				'<td><input type="text" name="user_name_'.htmlentities($user['ID']).'" value="'.htmlentities($user['NAME']).'"></td>'.
				'<td><input type="submit" name="change_user_name_'.htmlentities($user['ID']).'" value="Change Username" onclick="return confirm(\'Proceed with username change?\');"></td>'.
				'<td>&nbsp;</td>'.
				'<td><input type="password" name="user_password1_'.htmlentities($user['ID']).'" value=""></td>'.
				'<td><input type="password" name="user_password2_'.htmlentities($user['ID']).'" value=""></td>'.
				'<td><input type="submit" name="change_user_password_'.htmlentities($user['ID']).'" value="Change Password" onclick="return confirm(\'Proceed with user password change?\');"></td>'.
				'<td>&nbsp;</td>'.
				'<td><input type="text" name="user_groups_'.htmlentities($user['ID']).'" value="'.htmlentities($user['GROUPS']).'" size="40"></td>'.
				'<td><input type="submit" name="change_user_groups_'.htmlentities($user['ID']).'" value="Update Groups" onclick="return confirm(\'Proceed with user group update?\');"></td>'.
				'<td>&nbsp;</td>'.
				'<td><input type="submit" name="delete_user_'.htmlentities($user['ID']).'" value="Delete &quot;'.htmlentities($user['NAME']).'&quot;" onclick="return confirm(\'Proceed with user deletion?\');"></td>'.
				'<td><input type="submit" name="login_as_user_'.htmlentities($user['ID']).'" value="Log in as &quot;'.htmlentities($user['NAME']).'&quot;" onclick="return confirm(\'Proceed with log in?\');"></td>'.
				'</tr>';
	}
	echo '<tr>'.
			'<td colspan="12">'.
			'<hr />'.
			'</td>'.
			'</tr>'.
			'<tr>'.
			'<th>Username</th>'.
			'<th></th>'.
			'<th>&nbsp;</th>'.
			'<th>Password</th>'.
			'<th>Password (again)</th>'.
			'<th></th>'.
			'<th>&nbsp;</th>'.
			'<th>Groups</th>'.
			'<th></th>'.
			'<th>&nbsp;</th>'.
			'<th>Create User</th>'.
			'<th></th>'.
			'</tr>'.
			'<tr>'.
			'<td><input type="text" name="create_user_name" value=""></td>'.
			'<td></td>'.
			'<td>&nbsp;</td>'.
			'<td><input type="password" name="create_user_password1" value=""></td>'.
			'<td><input type="password" name="create_user_password2" value=""></td>'.
			'<td></td>'.
			'<td>&nbsp;</td>'.
			'<td><input type="text" name="create_user_groups" value="" size="40"></td>'.
			'<td></td>'.
			'<td>&nbsp;</td>'.
			'<td><input type="submit" name="create_user" value="Create User" onclick="return confirm(\'Proceed with user creation?\');"></td>'.
			'<td></td>'.
			'</tr>';
	echo '</tbody></table><input type="hidden" name="user_ids" value="'.implode(',', $user_ids).'">'.PHP_EOL;
}

function outputFullShareList()
{
	$share_ids = array();
	echo '<table><thead><tr>'.
			'<th>Name</th>'.
			'<th>Change Name</th>'.
			'<th>&nbsp;</th>'.
			'<th>Path</th>'.
			'<th>Change Path</th>'.
			'<th>&nbsp;</th>'.
			'<th>Groups Visible</th>'.
			'<th>Groups Readable</th>'.
			'<th>Groups Editable</th>'.
			'<th>Update Groups</th>'.
			'<th>&nbsp;</th>'.
			'<th>Delete Share</th>'.
			'</tr></thead><tbody>';
	foreach ($GLOBALS['all_shares'] as $share)
	{
		$share_ids[] = $share['ID'];
		echo '<tr>'.
				'<td><input type="text" name="share_name_'.htmlentities($share['ID']).'" value="'.htmlentities($share['NAME']).'"></td>'.
				'<td><input type="submit" name="change_share_name_'.htmlentities($share['ID']).'" value="Change Name" onclick="return confirm(\'Proceed with share name change?\');"></td>'.
				'<td>&nbsp;</td>'.
				'<td><input type="text" name="share_path_'.htmlentities($share['ID']).'" value="'.htmlentities($share['PATH']).'"></td>'.
				'<td><input type="submit" name="change_share_path_'.htmlentities($share['ID']).'" value="Change Path" onclick="return confirm(\'Proceed with share path change?\');"></td>'.
				'<td>&nbsp;</td>'.
				'<td><input type="text" name="share_groups_visible_'.htmlentities($share['ID']).'" value="'.htmlentities($share['GROUPS_VISIBLE']).'" size="40"></td>'.
				'<td><input type="text" name="share_groups_access_'.htmlentities($share['ID']).'" value="'.htmlentities($share['GROUPS_ACCESS_FILES']).'" size="40"></td>'.
				'<td><input type="text" name="share_groups_modify_'.htmlentities($share['ID']).'" value="'.htmlentities($share['GROUPS_MODIFY_FILES']).'" size="40"></td>'.
				'<td><input type="submit" name="change_share_groups_'.htmlentities($share['ID']).'" value="Update Groups" onclick="return confirm(\'Proceed with share group update?\');"></td>'.
				'<td>&nbsp;</td>'.
				'<td><input type="submit" name="delete_share_'.htmlentities($share['ID']).'" value="Delete &quot;'.htmlentities($share['NAME']).'&quot;" onclick="return confirm(\'Proceed with share deletion?\');"></td>'.
				'</tr>';
	}
	echo '<tr>'.
			'<td colspan="12">'.
			'<hr />'.
			'</td>'.
			'</tr>'.
			'<tr>'.
			'<th>Name</th>'.
			'<th></th>'.
			'<th>&nbsp;</th>'.
			'<th>Path</th>'.
			'<th></th>'.
			'<th>&nbsp;</th>'.
			'<th>Groups Visible</th>'.
			'<th>Groups Readable</th>'.
			'<th>Groups Editable</th>'.
			'<th></th>'.
			'<th>&nbsp;</th>'.
			'<th>Create Share</th>'.
			'</tr>'.
			'<tr>'.
			'<td><input type="text" name="create_share_name" value=""></td>'.
			'<td></td>'.
			'<td>&nbsp;</td>'.
			'<td><input type="text" name="create_share_path" value=""></td>'.
			'<td></td>'.
			'<td>&nbsp;</td>'.
			'<td><input type="text" name="create_share_groups_visible" value="" size="40"></td>'.
			'<td><input type="text" name="create_share_groups_access" value="" size="40"></td>'.
			'<td><input type="text" name="create_share_groups_modify" value="" size="40"></td>'.
			'<td></td>'.
			'<td>&nbsp;</td>'.
			'<td><input type="submit" name="create_share" value="Create Share" onclick="return confirm(\'Proceed with share creation?\');"></td>'.
			'</tr>';
	echo '</tbody></table><input type="hidden" name="share_ids" value="'.implode(',', $share_ids).'">'.PHP_EOL;
}

outputStandardTemplateHeader('Administration');

echo '<a href="'.htmlentities(pathinfo($_SERVER['SCRIPT_NAME'])['dirname']).'">&lt; Back to main listing</a>
<br />
<br />
';
if (isset($_SESSION['msg'])) {
	echo '<div style="font-size: large;">'.htmlentities($_SESSION['msg']).'</div>
<br />
';
	unset($_SESSION['msg']);
}
echo '<noscript><div style="font-size: large;"><em>Note: You will not be asked for confirmation.</em></div><br /></noscript>
<form action="admin_backend.php" method="post">
	<div style="overflow: auto;">
		<fieldset>
			<legend>Users</legend>
			';
outputUserList();
echo '
		</fieldset>
	</div>
	<br />
	<div style="overflow: auto;">
		<fieldset>
			<legend>Shares</legend>
			';
outputFullShareList();
echo '
		</fieldset>
	</div>
	<input type="hidden" name="csrf_token" value="'.htmlentities($_SESSION['csrf_token']).'">
	<br />
</form>';

outputStandardTemplateFooter();
