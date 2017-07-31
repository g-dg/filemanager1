<?php
if (!defined('GD_FILEMANAGER_VERSION'))
{
	http_response_code(403);
	exit('Error! No direct script access allowed!');
}

require_once('template.php');

/* Listing arrays have:
 * 'name' => displayed name
 * 'uri' => full, http- and href-ready uri\
 * 'basic_type' => 'directory' || 'file'  (shares are classifed as directories)
 */

function outputListing($body)
{
	echo getStandardTemplateHeader('/'.$GLOBALS['requested_full_path']) . $body . getStandardTemplateFooter();
}

function serveShareListing()
{
	$shares = getShareList();
	$listing = '<table>';
	foreach ($shares as $share)
	{
		if (canViewShare($share['name']))
		{
			$listing .= '<tr><td><a href="'.htmlentities($share['uri']).'">'.htmlentities($share['name']).'/</a></td></tr>';
		}
	}
	$listing .= '</table>';
	outputListing($listing);
}

function serveDirectoryListing($share, $path)
{
	$listing = '<table>';
	$listing .= '<tr>'.
			'<td><a href="'.getParentHttpUri(shareStringAndPathStringToFullPathString($share, $path)).'">[Parent Directory]</a></td>'.
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
							'<td><a href="'.htmlentities($file['uri']).'?'.urlencode(session_name()).'='.urlencode(session_id()).'" target="_blank">'.htmlentities($file['name']).'</a></td>'.
							'<td><a href="'.htmlentities($file['uri']).'?'.urlencode(session_name()).'='.urlencode(session_id()).'&amp;download">Download</a></td>'.
							'</tr>';
				}
				else if ($file['basic_type'] === 'directory')
				{
					// just open in the same tab
					$listing .= '<tr>'.
							'<td><a href="'.htmlentities($file['uri']).'">'.htmlentities($file['name']).'/</a></td>'.
							'<td></td>'.
							'</tr>';
				}
				else
				{
					$listing .= '<tr>'.
							'<td><a href="'.htmlentities($file['uri']).'">'.htmlentities($file['name']).'</a></td>'.
							'<td></td>'.
							'</tr>';
				}
			}
		}
		else
		{
			http_response_code(404);
			$listing .= '<tr><td><span style="font-size: large;">Error: Either the file or folder "/' . htmlentities(shareStringAndPathStringToFullPathString($share, $path)) . '" does not exist, or you don\'t have permission to view it.</span></td></tr>';
		}
	}
	else
	{
		http_response_code(404);
		$listing .= '<tr><td><span style="font-size: large;">Error: Either the share "' . htmlentities($share) . '" does not exist, or you don\'t have permission to view it.</span></td></tr>';
	}
	$listing .= '</table>';
	outputListing($listing);
}
