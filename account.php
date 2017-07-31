<?php
require_once('version.php');

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
		<meta name="viewport" content="width=device-width"></meta>
		<title>My Account - Garnet DeGelder's File Manager <?=htmlentities(GD_FILEMANAGER_VERSION);?></title>
		<link rel="stylesheet" type="text/css" href="<?=htmlentities(pathinfo($_SERVER['SCRIPT_NAME'])['dirname'].'/style.css');?>"></link>
	</head>
	<body>
		<div class="header">
			<div class="tools">
				<ul>
					<li>
						Currently logged in as "<?=htmlentities($_SESSION['username']);?>"
					</li>
<?php if (inGroup('root')) { ?>
					<li>
						<a href="<?=htmlentities(pathinfo($_SERVER['SCRIPT_NAME'])['dirname'].'/admin.php');?>">Administration</a>
					</li>
<?php } ?>
					<li>
						<a href="<?=htmlentities(pathinfo($_SERVER['SCRIPT_NAME'])['dirname'].'/account.php');?>">My Account</a>
					</li>
					<li>
						<a href="<?=htmlentities(pathinfo($_SERVER['SCRIPT_NAME'])['dirname'].'/logout.php');?>">Log Out</a>
					</li>
				</ul>
			</div>
			<div class="title">
				My Account - Garnet DeGelder's File Manager on <?=htmlentities($_SERVER['HTTP_HOST']).PHP_EOL;?>
			</div>
		</div>
		<div class="content">
			<a href="<?=htmlentities(pathinfo($_SERVER['SCRIPT_NAME'])['dirname'] . '/');?>">&lt; Back to main listing</a>
			<br />
			<br />
<?php if (isset($_GET['msg'])) { ?>
			<div style="font-size: large;"><?=htmlentities($_GET['msg']);?></div>
			<br />
<?php } ?>
			<form action="account_backend.php" method="post" class="standard">
				<fieldset>
					<legend>Username</legend>
					<code>
						<?php echo htmlentities($_SESSION['username']).PHP_EOL;?>
					</code>
					<!-- (User ID: <?php echo $_SESSION['user_id'];?>) -->
				</fieldset>
<?php if ($_SESSION['username'] !== GD_FILEMANAGER_GUEST_USER) { ?>
				<fieldset>
					<legend>Password</legend>
					<label>
						Current Password:
					</label>
					<input type="password" name="old_password" value="" autofocus="autofocus"></input>
					<label>
						New Password: 
					</label>
					<input type="password" name="new_password1" value=""></input>
					<label>
						New Password (again): 
					</label>
					<input type="password" name="new_password2" value=""></input>
					<input type="submit" name="submit" value="Change Password" style="width: 100%;"></input>
				</fieldset><?php } ?>
				<fieldset>
					<legend>Groups</legend>
					<code>
						<?php echo htmlentities(implode(', ', $_SESSION['groups'])).PHP_EOL;?>
					</code>
				</fieldset>
			</form>
		</div>
		<div class="footer">
			Copyright &copy; 2017  Garnet DeGelder
		</div>
	</body>
</html>
