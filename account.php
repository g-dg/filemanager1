<?php
define('GD_FILEMANAGER_VERSION', '1.0.1');

require_once('config.php');
require_once('session.php');
require_once('database.php');
require_once('auth.php');
startSession();
checkUserIP();
authenticate();

?><!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="UTF-8"></meta>
		<title>My Account - Garnet DeGelder's File Manager <?=htmlentities(GD_FILEMANAGER_VERSION);?></title>
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
		<div style="font-size: x-large;">My Account - Garnet DeGelder's File Manager on <?=htmlentities($_SERVER['HTTP_HOST']);?></div>
		<hr />
		<a href="<?=htmlentities(pathinfo($_SERVER['SCRIPT_NAME'])['dirname']);?>">&lt; Back to main listing</a>
		<br />
		<br />
<?php if (isset($_GET['msg'])) { ?>
		<div style="font-size: large;"><?=htmlentities($_GET['msg']);?></div>
		<br />
<?php } ?>
		<form action="account_backend.php" method="post">
			<fieldset>
				<legend>Username</legend>
				<?php echo htmlentities($_SESSION['username']).PHP_EOL;?><!-- (User ID: <?php echo $_SESSION['user_id'];?>) -->
			</fieldset>
<?php if ($_SESSION['username'] !== GD_FILEMANAGER_GUEST_USER)
{
?>			<fieldset>
				<legend>Password</legend>
				<table>
					<tbody>
						<tr>
							<td>
								Current Password: 
							</td>
							<td>
								<input type="password" name="old_password" value="" autofocus="autofocus"></input>
							</td>
						</tr>
						<tr>
							<td>
								New Password: 
							</td>
							<td>
								<input type="password" name="new_password1" value=""></input>
							</td>
						</tr>
						<tr>
							<td>
								New Password (again): 
							</td>
							<td>
								<input type="password" name="new_password2" value=""></input>
							</td>
						</tr>
						<tr>
							<td colspan="2">
								<input type="submit" name="submit" value="Change Password" style="width: 100%;"></input>
							</td>
						</tr>
					</tbody>
				</table>
			</fieldset><?php } ?>
			<fieldset>
				<legend>Groups</legend>
				<?php echo htmlentities(implode(', ', $_SESSION['groups'])).PHP_EOL;?>
			</fieldset>
		</form>
	</body>
</html>
