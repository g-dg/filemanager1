<?php
if (!defined('GD_FILEMANAGER_VERSION'))
{
	http_response_code(403);
	exit('Error! No direct script access allowed!');
}

require_once('template.php');

/*
Listing arrays have:
'name' => displayed name
'uri' => full, http- and href-ready uri
'basic_type' => 'directory' || 'file'  (shares are classifed as directories)

as of 1.5:
'size' => filesize (bytes) / number of files in directory
'last_modified' => last modified time (as int UNIX timestamp)
*/

// sort functions
function sortNameAsc($listing)
{
	// A-Z
	usort($listing, function($a,$b){return strcasecmp($a['name'],$b['name']);});
	return $listing;
}
function sortNameDesc($listing)
{
	// Z-A
	usort($listing, function($a,$b){return strcasecmp($b['name'],$a['name']);});
	return $listing;
}
function sortDateAsc($listing)
{
	// earlier to later
	usort($listing, function($a,$b){return $b['name'] - $a['name'];});
	return $listing;
}
function sortDateDesc($listing)
{
	// later to earlier
	usort($listing, function($a,$b){return $a['name'] - $b['name'];});
	return $listing;
}
function sortSizeAsc($listing)
{
	// less to greater
	usort($listing, function($a,$b){return $b['name'] - $a['name'];});
	return $listing;
}
function sortSizeDesc($listing)
{
	// greater to less
	usort($listing, function($a,$b){return $a['name'] - $b['name'];});
	return $listing;
}

function sortListing($listing, $field, $order)
{
	switch ($field)
	{
		case 'name':
			switch ($order)
			{
				case 'asc':
					return sortNameAsc($listing);
					break;
				case 'desc':
					return sortNameDesc($listing);
					break;
				default:
					return $listing;
			}
			break;
		case 'last-modified':
			switch ($order)
			{
				case 'asc':
					return sortDateAsc($listing);
					break;
				case 'desc':
					return sortDateDesc($listing);
					break;
				default:
					return $listing;
			}
			break;
		case 'size':
			switch ($order)
			{
				case 'asc':
					return sortSizeAsc($listing);
					break;
				case 'desc':
					return sortSizeDesc($listing);
					break;
				default:
					return $listing;
			}
			break;
		default:
			return $listing;
	}
}

function prettifyFileSize($size)
{
	if ($size < (2**10))
	{
		return $size;
	}
	if ($size < (2**20))
	{
		return sprintf("%01.1fK", ($size / (2**10)));
	}
	if ($size < (2**30))
	{
		return sprintf("%01.1fM", ($size / (2**20)));
	}
	return sprintf("%01.1fG", ($size / (2**30)));
}

function outputListing($body)
{
	echo getStandardTemplateHeader('/'.$GLOBALS['requested_full_path']) . $body . getStandardTemplateFooter();
}

function serveShareListing()
{
	$shares = getShareList();
	$listing = '<table><thead>'.
			'<tr>'.
			'<th></th>'.
			'<th>Name</th>'.
			'<th>Last Modified</th>'.
			'<th>Size</th>'.
			'<th>Download</th>'.
			'</tr></thead><tbody>';
	foreach ($shares as $share)
	{
		if (canViewShare($share['name']))
		{
			$listing .= '<tr>'.
					'<td><img src="'.htmlentities(pathinfo($_SERVER['SCRIPT_NAME'])['dirname']).'/icon/folder.gif" alt="[DIR]"</td>'.
					'<td><a href="'.htmlentities($share['uri']).'">'.htmlentities($share['name']).'/</a></td>'.
					'<td><em>N/A</em></td>'.
					'<td>'.htmlentities(prettifyFileSize(getFileSize($share['name'], ''))).'</td>'.
					'<td><em>N/A</em></td>'.
					'</tr>';
		}
	}
	$listing .= '</tbody></table>';
	outputListing($listing);
}

function serveDirectoryListing($share, $path)
{
	$date_format = 'Y-m-d h:i';

	$listing = '<table><thead>'.
			'<tr>'.
			'<th></th>'.
			'<th>Name</th>'.
			'<th>Last Modified</th>'.
			'<th>Size</th>'.
			'<th>Download</th>'.
			'</tr></thead><tbody>';
	$listing .= '<tr>'.
			'<td><img src="'.htmlentities(pathinfo($_SERVER['SCRIPT_NAME'])['dirname']).'/icon/back.gif" alt="[PARENTDIR]" width="20" height="22"></td>'.
			'<td><a href="'.getParentHttpUri(shareStringAndPathStringToFullPathString($share, $path)).'">[Parent Directory]</a></td>'.
			'<td></td>'.
			'<td></td>'.
			'<td></td>'.
			'</tr>';
	// check if the share is readable (note: not necessarily visible)
	if (canReadShare($share))
	{
		$dir_list = getDirectoryListing($share, $path);
		if ($dir_list !== false)
		{
			foreach ($dir_list as $file)
			{
				if ($file['basic_type'] === 'file')
				{
					// add the session id and open in new tab
					$listing .= '<tr>'.
							'<td><img src="'.htmlentities(pathinfo($_SERVER['SCRIPT_NAME'])['dirname']).'/icon/generic.gif" alt="[FILE]" width="20" height="22"></td>'.
							'<td><a href="'.htmlentities($file['uri']).'?'.urlencode(session_name()).'='.urlencode(session_id()).'" target="_blank">'.htmlentities($file['name']).'</a></td>'.
							'<td>'.htmlentities(date($date_format, $file['last_modified'])).'</td>'.
							'<td>'.htmlentities(prettifyFileSize($file['size'])).'</td>'.
							'<td><a href="'.htmlentities($file['uri']).'?'.urlencode(session_name()).'='.urlencode(session_id()).'&amp;download">Download</a></td>'.
							'</tr>';
				}
				else if ($file['basic_type'] === 'directory')
				{
					// just open in the same tab
					$listing .= '<tr>'.
							'<td><img src="'.htmlentities(pathinfo($_SERVER['SCRIPT_NAME'])['dirname']).'/icon/folder.gif" alt="[DIR]" width="20" height="22"></td>'.
							'<td><a href="'.htmlentities($file['uri']).'">'.htmlentities($file['name']).'/</a></td>'.
							'<td>'.htmlentities(date($date_format, $file['last_modified'])).'</td>'.
							'<td>'.htmlentities(prettifyFileSize($file['size'])).'</td>'.
							'<td></td>'.
							'</tr>';
				}
				else
				{
					// the "?"s avoid causing potential errors
					$listing .= '<tr>'.
							'<td><img src="'.htmlentities(pathinfo($_SERVER['SCRIPT_NAME'])['dirname']).'/icon/unknown.gif" alt="[ ? ]" width="20" height="22"></td>'.
							'<td><a href="'.htmlentities($file['uri']).'">'.htmlentities($file['name']).'</a></td>'.
							'<td>?</td>'.
							'<td>?</td>'.
							'<td></td>'.
							'</tr>';
				}
			}
		}
		else
		{
			http_response_code(404);
			$listing .= '<tr><td colspan="4"><span class="error">Error: Either the file or folder "/' . htmlentities(shareStringAndPathStringToFullPathString($share, $path)) . '" does not exist, or you don\'t have permission to view it.</span></td></tr>';
		}
	}
	else
	{
		http_response_code(404);
		$listing .= '<tr><td colspan="4"><span class="error">Error: Either the share "' . htmlentities($share) . '" does not exist, or you don\'t have permission to view it.</span></td></tr>';
	}
	$listing .= '</tbody></table>';
	outputListing($listing);
}
