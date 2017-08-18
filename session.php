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
//TODO: Write my own session handler that works better for this (doesn't have to log out the other user)
function checkUserIP()
{
	if (isset($_SESSION['user_ip']))
	{
		if ($_SERVER['REMOTE_ADDR'] !== $_SESSION['user_ip'])
		{
			session_unset();
			session_regenerate_id(true);
			session_write_close();
			setcookie(session_name(), '', time() - 1);
			http_response_code(401);
			die();
		}
	}
	else
	{
		$_SESSION['user_ip'] = $_SERVER['REMOTE_ADDR'];
	}
}
