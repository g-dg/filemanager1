<?php
if (!defined('GD_FILEMANAGER_VERSION'))
{
	http_response_code(403);
	exit('Error! No direct script access allowed!');
}


function generateMimeTypeArray()
{
	$GLOBALS['mimetypes'] = array();
	foreach (explode("\n", file_get_contents('mime.types')) as $line)
	{
		if (isset($line[0]) &&
				$line[0] !== '#' &&
				preg_match_all('#([^\s]+)#', $line, $out) &&
				isset($out[1]) &&
				($count = count($out[1])) > 1)
		{
			for ($i = 1; $i < $count; $i++)
			{
				$GLOBALS['mimetypes'][] = array('extension' => $out[1][$i], 'mimetype' => $out[1][0]);
			}
		}
	}
	return $GLOBALS['mimetypes'];
}

function getCachedOrGenerateMimeTypeArray()
{
	if (is_readable('mime_types.json'))
	{
		// set array to cached version
		$GLOBALS['mimetypes'] = json_decode(file_get_contents('mime_types.json'), true);
		if (json_last_error() !== JSON_ERROR_NONE)
		{
			generateMimeTypeArray();
		}
	}
	else
	{
		generateMimeTypeArray();
		if (is_writable('./'))
		{
			file_put_contents('mime_types.json', json_encode($GLOBALS['mimetypes']));
		}
	}
}

// returns false if no extension
function getMimeTypeFromExtension($filename)
{
	if (!isset($GLOBALS['mimetypes']))
	{
		getCachedOrGenerateMimeTypeArray();
	}
	$extension = pathinfo(basename($filename))['extension'];
	foreach($GLOBALS['mimetypes'] as $mimetypedef)
	{
		if ($mimetypedef['extension'] === $extension)
		{
			return $mimetypedef['mimetype'];
		}
	}
	return null;
}
