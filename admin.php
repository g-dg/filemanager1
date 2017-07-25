<?php
define('GD_FILEMANAGER_VERSION', '1.0.1');

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

$GLOBALS['all_users'] = dbQuery('SELECT "ID", "NAME", "GROUPS" FROM "USERS";');
$GLOBALS['all_shares'] = dbQuery('SELECT "ID", "NAME", "PATH", "GROUPS_VISIBLE", "GROUPS_ACCESS_FILES", "GROUPS_MODIFY_FILES" FROM "SHARES";');

// sort the users and shares
usort($GLOBALS['all_users'], function($a,$b){return strcasecmp($a['NAME'],$b['NAME']);});
usort($GLOBALS['all_shares'], function($a,$b){return strcasecmp($a['NAME'],$b['NAME']);});

function getUserList()
{
	$user_ids = array();
	$html_listing = '<table><thead><tr>'.
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
			'<th>Log In As</th>'.
			'</tr></thead><tbody>';
	foreach ($GLOBALS['all_users'] as $user)
	{
		$user_ids[] = $user['ID'];
		$html_listing .= '<tr>'.
				'<td><input type="text" name="user_name_'.$user['ID'].'" value="'.htmlentities($user['NAME']).'"></input></td>'.
				'<td><input type="submit" name="change_user_name_'.$user['ID'].'" value="Change Username" onclick="return confirm(\'Proceed with username change?\');"></input></td>'.
				'<td>&nbsp;</td>'.
				'<td><input type="password" name="user_password1_'.$user['ID'].'" value=""></input></td>'.
				'<td><input type="password" name="user_password2_'.$user['ID'].'" value=""></input></td>'.
				'<td><input type="submit" name="change_user_password_'.$user['ID'].'" value="Change Password" onclick="return confirm(\'Proceed with user password change?\');"></input></td>'.
				'<td>&nbsp;</td>'.
				'<td><input type="text" name="user_groups_'.$user['ID'].'" value="'.htmlentities($user['GROUPS']).'" size="40"></input></td>'.
				'<td><input type="submit" name="change_user_groups_'.$user['ID'].'" value="Update Groups" onclick="return confirm(\'Proceed with user group update?\');"></input></td>'.
				'<td>&nbsp;</td>'.
				'<td><input type="submit" name="delete_user_'.$user['ID'].'" value="Delete &quot;'.htmlentities($user['NAME']).'&quot;" style="width: 100%;" onclick="return confirm(\'Proceed with user deletion?\');"></input></td>'.
				'<td><input type="submit" name="login_as_user_'.$user['ID'].'" value="Log in as &quot;'.htmlentities($user['NAME']).'&quot;" style="width: 100%;" onclick="return confirm(\'Proceed with log in?\');"></input></td>'.
				'</tr>';
	}
	$html_listing .= '<tr>'.
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
			'<td><input type="text" name="create_user_name" value=""></input></td>'.
			'<td></td>'.
			'<td>&nbsp;</td>'.
			'<td><input type="password" name="create_user_password1" value=""></input></td>'.
			'<td><input type="password" name="create_user_password2" value=""></input></td>'.
			'<td></td>'.
			'<td>&nbsp;</td>'.
			'<td><input type="text" name="create_user_groups" value="" size="40"></input></td>'.
			'<td></td>'.
			'<td>&nbsp;</td>'.
			'<td><input type="submit" name="create_user" value="Create User" style="width: 100%;" onclick="return confirm(\'Proceed with user creation?\');"></input></td>'.
			'<td></td>'.
			'</tr>';
	return $html_listing . '</tbody></table><input type="hidden" name="user_ids" value="'.implode(',', $user_ids).'"></input>'.PHP_EOL;
}

function getFullShareList()
{
	$share_ids = array();
	$html_listing = '<table><thead><tr>'.
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
		$html_listing .= '<tr>'.
				'<td><input type="text" name="share_name_'.$share['ID'].'" value="'.htmlentities($share['NAME']).'"></input></td>'.
				'<td><input type="submit" name="change_share_name_'.$share['ID'].'" value="Change Name" onclick="return confirm(\'Proceed with share name change?\');"></input></td>'.
				'<td>&nbsp;</td>'.
				'<td><input type="text" name="share_path_'.$share['ID'].'" value="'.htmlentities($share['PATH']).'"></input></td>'.
				'<td><input type="submit" name="change_share_path_'.$share['ID'].'" value="Change Path" onclick="return confirm(\'Proceed with share path change?\');"></input></td>'.
				'<td>&nbsp;</td>'.
				'<td><input type="text" name="share_groups_visible_'.$share['ID'].'" value="'.htmlentities($share['GROUPS_VISIBLE']).'" size="40"></input></td>'.
				'<td><input type="text" name="share_groups_access_'.$share['ID'].'" value="'.htmlentities($share['GROUPS_ACCESS_FILES']).'" size="40"></input></td>'.
				'<td><input type="text" name="share_groups_modify_'.$share['ID'].'" value="'.htmlentities($share['GROUPS_MODIFY_FILES']).'" size="40"></input></td>'.
				'<td><input type="submit" name="change_share_groups_'.$share['ID'].'" value="Update Groups" onclick="return confirm(\'Proceed with share group update?\');"></input></td>'.
				'<td>&nbsp;</td>'.
				'<td><input type="submit" name="delete_share_'.$share['ID'].'" value="Delete &quot;'.htmlentities($share['NAME']).'&quot;" style="width: 100%;" onclick="return confirm(\'Proceed with share deletion?\');"></input></td>'.
				'</tr>';
	}
	$html_listing .= '<tr>'.
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
			'<td><input type="text" name="create_share_name" value=""></input></td>'.
			'<td></td>'.
			'<td>&nbsp;</td>'.
			'<td><input type="text" name="create_share_path" value=""></input></td>'.
			'<td></td>'.
			'<td>&nbsp;</td>'.
			'<td><input type="text" name="create_share_groups_visible" value="" size="40"></input></td>'.
			'<td><input type="text" name="create_share_groups_access" value="" size="40"></input></td>'.
			'<td><input type="text" name="create_share_groups_modify" value="" size="40"></input></td>'.
			'<td></td>'.
			'<td>&nbsp;</td>'.
			'<td><input type="submit" name="create_share" value="Create Share" style="width: 100%;" onclick="return confirm(\'Proceed with share creation?\');"></input></td>'.
			'</tr>';
	return $html_listing . '</tbody></table><input type="hidden" name="share_ids" value="'.implode(',', $share_ids).'"></input>'.PHP_EOL;
}

?><!DOCTYPE html>
<html>
	<head>
		<title>Administration - Garnet DeGelder's File Manager <?=htmlentities(GD_FILEMANAGER_VERSION);?></title>
	</head>
	<body>
		<div style="text-align: right; float: right;">
			Currently logged in as "<?=htmlentities($_SESSION['username']);?>"
			|
<?php if (inGroup('root')) { ?>
			<a href="<?=htmlentities(pathinfo($_SERVER['SCRIPT_NAME'])['dirname'].'/admin.php');?>">Administration</a>
			|
<?php } ?>
			<a href="<?=htmlentities(pathinfo($_SERVER['SCRIPT_NAME'])['dirname'].'/account.php');?>">My Account</a>
			|
			<a href="<?=htmlentities(pathinfo($_SERVER['SCRIPT_NAME'])['dirname'].'/logout.php');?>">Log Out</a>
		</div>
		<div style="font-size: x-large;">Administration - Garnet DeGelder's File Manager on <?=htmlentities($_SERVER['HTTP_HOST']);?></div>
		<hr />
		<a href="<?=htmlentities(pathinfo($_SERVER['SCRIPT_NAME'])['dirname']);?>">&lt; Back to main listing</a>
		<br />
		<br />
<?php if (isset($_GET['msg'])) { ?>
		<div style="font-size: large;"><?=htmlentities($_GET['msg']);?></div>
		<br />
<?php } ?>
		<noscript><div style="font-size: large;"><em>Note: You will not be asked for confirmation.</em></div><br /></noscript>
		<form action="admin_backend.php" method="post">
			<div style="overflow: auto;">
				<fieldset>
					<legend>Users</legend>
					<?=getUserList();?>
				</fieldset>
			</div>
			<br />
			<div style="overflow: auto;">
				<fieldset>
					<legend>Shares</legend>
					<?=getFullShareList();?>
				</fieldset>
			</div>
			<br />
		</form>
	</body>
</html>
