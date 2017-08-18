<?php
if (!defined('GD_FILEMANAGER_VERSION'))
{
	http_response_code(403);
	exit('Error! No direct script access allowed!');
}

// get session id from various places, needed as cookies don't transfer when copy-pasting URIs

function startSession()
{
	if (!isset($_COOKIE[session_name()]) && isset($_GET[session_name()]))
	{
		session_id($_GET[session_name()]);
	}
	
	// start the session
	session_start();
}

// check the IP address, used for detecting shared links and forged sessions
function checkUserIP()
{
	if (isset($_SESSION['user_ip']))
	{
		if ($_SERVER['REMOTE_ADDR'] !== $_SESSION['user_ip'])
		{
			setcookie(session_name(), '', time() - 3600);
			http_response_code(401);
			die('Possible session hijack attempt detected!');
		}
	}
	else
	{
		$_SESSION['user_ip'] = $_SERVER['REMOTE_ADDR'];
	}
}
