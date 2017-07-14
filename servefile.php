<?php
if (!defined('GD_FILEMANAGER_VERSION'))
{
	http_response_code(403);
	exit('Error! No direct script access allowed!');
}

function serveFile($share, $file_path)
{
	if (canReadShare($share))
	{
		//TODO: write my own library for this
		require_once('sendfile.php');
		$path = getFsPath($share, $file_path);
		$sendfile = new sendfile();
		$sendfile->throttle(0.0, 1048576);
		require_once('mime_types.php');
		$sendfile->contentType(getMimeTypeFromExtension($file_path));
		// end the session to avoid session lock
		session_write_close();
		$sendfile->send($path, false);
	}
}
