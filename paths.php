<?php
if (!defined('GD_FILEMANAGER_VERSION'))
{
	http_response_code(403);
	exit('Error! No direct script access allowed!');
}

// used to sanitize path arrays
// removes blanks and names starting with '.'
function sanitizePathArray($dirty_path_array)
{
	$clean_path_array = array();
	foreach ($dirty_path_array as $pathpart)
	{
		if ($pathpart !== '' &&
				substr($pathpart, 0, 1) !== '.') {
			$clean_path_array[] = $pathpart;
		}
	}
	return $clean_path_array;
}

// used to switch between path arrays and path strings.
// to avoid confusion, pass around arrays and only convert them if you need to output some text
function pathArrayToString($path_array)
{
	return trim(implode('/', sanitizePathArray($path_array)), '/');
}
function pathStringToArray($path_string)
{
	return sanitizePathArray(explode('/', trim($path_string, '/')));
}
function shareStringAndPathStringToFullPathString($share, $path_string)
{
	return pathArrayToString(pathStringToArray($share . '/' . $path_string));
}
function fullPathStringToShareStringAndPathString($full_path_string)
{
	$full_path_array = pathStringToArray($full_path_string);
	$share = array_shift($full_path_array);
	if (is_null($share))
	{
		$share = '';
	}
	return array('share' => $share, 'path' => $full_path_array);
}

// get the paths into the $GLOBALS array
// for now, we have to use the "index.php" in the uri
// ensure the prefix has one slash at the end
/* Requested Paths available:
 * requested_full_path  -- no leading/trailing slashes, could be blank
 * requested_full_path_array  -- could be empty (ie. no elements)
 * requested_share  -- could be blank for none (ie. at the share listing)
 * requested_path  -- no leading/trailing slashes, could be blank for no path
 * requested_path_array  -- could be empty (ie. no elements)
 */
function setUpPaths()
{
	$GLOBALS['http_uri_prefix'] = rtrim($_SERVER['SCRIPT_NAME'], '/') . '/';
	if (isset($_SERVER['PATH_INFO']) && trim($_SERVER['PATH_INFO'], '/') != '')
	{
		$GLOBALS['requested_full_path_array'] = pathStringToArray($_SERVER['PATH_INFO']);
		$GLOBALS['requested_full_path'] = pathArrayToString($GLOBALS['requested_full_path_array']);
		
		if (count($GLOBALS['requested_full_path_array']) <= 1)
		{
			$GLOBALS['requested_path_array'] = array();
			$GLOBALS['requested_path'] = '';
			
			if (count($GLOBALS['requested_full_path_array']) === 0)
			{
				$GLOBALS['requested_share'] = '';
			}
			else
			{
				$GLOBALS['requested_share'] = $GLOBALS['requested_full_path_array'][0];
			}
		}
		else
		{
			$GLOBALS['requested_path_array'] = $GLOBALS['requested_full_path_array'];
			$GLOBALS['requested_share'] = array_shift($GLOBALS['requested_path_array']);
			$GLOBALS['requested_path'] = pathArrayToString($GLOBALS['requested_path_array']);
		}
	}
	else
	{
		$GLOBALS['requested_full_path'] = '';
		$GLOBALS['requested_full_path_array'] = array();
		$GLOBALS['requested_share'] = '';
		$GLOBALS['requested_path'] = '';
		$GLOBALS['requested_path_array'] = array();
	}
}

// returns the filesystem path, read prevously from the database.
// the path in the database might have a slash on the end,
// so we have to make it consistent
function getFsPath($share, $path_string)
{
	$share_path = rtrim(getShareData($share)['PATH'], '/') . '/';
	return $share_path . $path_string;
}

// returns 'root_directory', 'file' or 'directory',
// false when neither, and null when not readable
function getBasicFileType($share, $path_string)
{
	if ($share === '')
	{
		// the main listing
		return 'root_directory';
	}
	$fs_path = getFsPath($share, $path_string);
	if (is_readable($fs_path))
	{
		if (is_file($fs_path))
		{
			return 'file';
		}
		else if (is_dir($fs_path))
		{
			return 'directory';
		}
		else
		{
			return false;
		}
	}
	else
	{
		return null;
	}
}

// returns the file size or number of files in the folder
function getFileSize($share, $path_string)
{
	return 0; //TODO: actually code this
}

// returns the last modified time as a UNIX timestamp
function getFileModificationTime($share, $path_string)
{
	return time(); //TODO: actually code this
}

// doesn't actually urlencode it, just escapes the quotes
function urlencodeHttpPath($path_string)
{
	//return $path_string;
	//return str_replace('%', '%25', str_replace(' ', '%20', $path_string)); // it turns out this causes more problems than it's worth
	$path_array = pathStringToArray($path_string);
	$clean_path_array = array();
	foreach ($path_array as $pathpart)
	{
		// urlencode changes spaces into pluses. That is stupid and doesn't work.
		$clean_path_array[] = str_replace('+', '%20', urlencode($pathpart));
	}
	return '/' . pathArrayToString($clean_path_array);
}

// returns the directory listing array without files
// beginning with '.', or false if not a directory.
function getDirectoryListing($share, $path_string)
{
	if (!canReadShare($share))
	{
		return false;
	}
	if ($share === '' ||
			getBasicFileType($share, $path_string) !== 'directory')
	{
		return false;
	}
	$fs_path = getFsPath($share, $path_string);
	if (!is_readable($fs_path) ||
			!is_dir($fs_path))
	{
		return false;
	}
	$raw_file_listing = scandir($fs_path);
	$clean_file_listing = sanitizePathArray($raw_file_listing);
	$listing = array();
	foreach ($clean_file_listing as $filename)
	{
		$listing[] = array('name' => $filename,
				'uri' => getHttpUri($share . '/' . $path_string . '/' . $filename), //TODO: fix '//'s in the root of the shares (not critical, as it gets handled by the sanitizer)
				'basic_type' => getBasicFileType($share, $path_string . '/' . $filename),
				'size' => null,
				'last_modified' => null);
	}
	// sort the listing
	usort($listing, function($a,$b){return strcasecmp($a['name'],$b['name']);});
	return $listing;
}

// returns the http- and href-ready link for a path
// must be given a string full path
function getHttpUri($full_path_string)
{
	return urlencodeHttpPath($GLOBALS['http_uri_prefix'] . $full_path_string);
}

// returns the string name of the parent directory, ready for http and href
// give a share and path uri
function getParentHttpUri($path_string)
{
	$full_path_array = pathStringToArray($path_string);
	array_pop($full_path_array);
	return $GLOBALS['http_uri_prefix'] . pathArrayToString($full_path_array);
}
