<?php
if (!defined('GD_FILEMANAGER_VERSION'))
{
	http_response_code(403);
	exit('Error! No direct script access allowed!');
}

// The router figures out the type of file, and calls the file server or listing server
function route() {
	if ($GLOBALS['requested_share'] === '')
	{
		// main share listing
		require_once('listing.php');
		serveShareListing();
	}
	else
	{
		if (getBasicFileType($GLOBALS['requested_share'], $GLOBALS['requested_path']) === 'directory')
		{
			// directory listing
			require_once('listing.php');
			serveDirectoryListing($GLOBALS['requested_share'], $GLOBALS['requested_path']);
		}
		else if (getBasicFileType($GLOBALS['requested_share'], $GLOBALS['requested_path']) === 'file')
		{
			// file
			if (isset($_GET['download']))
			{
				require_once('download.php');
				sendFile($GLOBALS['requested_share'], $GLOBALS['requested_path']);
			}
			else
			{
				require_once('servefile.php');
				serveFile($GLOBALS['requested_share'], $GLOBALS['requested_path']);
			}
		}
		else
		{
			require_once('notfound.php');
			serveNotFoundMessage();
		}
	}
}
