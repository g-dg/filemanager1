<?php
if (!defined('GD_FILEMANAGER_VERSION'))
{
	http_response_code(403);
	exit('Error! No direct script access allowed!');
}

require_once('config.php');
require_once('session.php');
require_once('database.php');
require_once('auth.php');

startSession();
checkUserIP();
authenticate();

function getStandardTemplateHeader($title)
{
	$header = '<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="UTF-8">
		<meta name="viewport" content="width=device-width">
		<title>'.htmlentities($title).' - Garnet DeGelder\'s File Manager '.htmlentities(GD_FILEMANAGER_VERSION).'</title>
		<link rel="stylesheet" type="text/css" href="'.htmlentities(pathinfo($_SERVER['SCRIPT_NAME'])['dirname'].'/style.css').'">
	</head>
	<body>
		<div class="header">
			<div class="tools">
				<ul>';
	$header .= '<li>Currently logged in as "'.htmlentities($_SESSION['username']).'"</li>';
	if (inGroup('root'))
	{
		$header .= '<li><a href="'.htmlentities(pathinfo($_SERVER['SCRIPT_NAME'])['dirname'].'/admin.php').'">Administration</a></li>';
	}
	$header .= '<li><a href="'.htmlentities(pathinfo($_SERVER['SCRIPT_NAME'])['dirname'].'/account.php').'">My Account</a></li>';
	$header .= '<li><a href="'.htmlentities(pathinfo($_SERVER['SCRIPT_NAME'])['dirname'].'/about.php').'">About</a></li>';
	$header .= '<li><a href="'.htmlentities(pathinfo($_SERVER['SCRIPT_NAME'])['dirname'].'/logout.php').'">Log Out</a></li>';
	$header .= '</ul>
			</div>
			<div class="title">'.htmlentities($title).' - Garnet DeGelder\'s File Manager on '.htmlentities($_SERVER['HTTP_HOST']).'</div>
		</div>
		<div class="content">
';
	return $header;
}

function getLoginTemplateHeader()
{
	$header = '<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="UTF-8">
		<meta name="viewport" content="width=device-width">
		<title>Log In to Garnet DeGelder\'s File Manager</title>
		<link rel="stylesheet" type="text/css" href="'.htmlentities(pathinfo($_SERVER['SCRIPT_NAME'])['dirname'].'/style.css').'">
	</head>
	<body class="login">
		<div class="header">
			<div class="title">
				Log in to Garnet DeGelder\'s File Manager on '.htmlentities($_SERVER['HTTP_HOST']).'
			</div>
		</div>
		<div class="content">
';
	return $header;
}

function getStandardTemplateFooter()
{
	$footer = '
		</div>
		<div class="footer">
			Copyright &copy; 2017  Garnet DeGelder
';
	if (GD_FILEMANAGER_PROFILER_ENABLE)
	{
		$footer .= '			<br />
			Page generated in '.sprintf("%.4f", (microtime(true) - $GLOBALS['script_start_time'])).' seconds.
';
	}
	$footer .= '		</div>
	</body>
</html>
';
	return $footer;
}

function getLoginTemplateFooter()
{
	return getStandardTemplateFooter();
}
