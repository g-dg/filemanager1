<?php
if (!defined('GD_FILEMANAGER_VERSION'))
{
	http_response_code(403);
	exit('Error! No direct script access allowed!');
}

/* Listing arrays have:
 * 'name' => displayed name
 * 'uri' => full, http- and href-ready uri\
 * 'basic_type' => 'directory' || 'file'  (shares are classifed as directories)
 */

function outputListing($body)
{
	$listing_head = '<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="UTF-8"></meta>
		<meta name="viewport" content="width=device-width"></meta>
		<title>/'.$GLOBALS['requested_full_path'].' - Garnet DeGelder\'s File Manager '.htmlentities(GD_FILEMANAGER_VERSION).'</title>
		<link rel="stylesheet" type="text/css" href="'.htmlentities(pathinfo($_SERVER['SCRIPT_NAME'])['dirname'].'/style.css').'"></link>
	</head>
	<body>
		<div class="header">
			<div class="tools">
				<ul>';
	$listing_head .= '<li>Currently logged in as "'.htmlentities($_SESSION['username']).'"</li>';
	/*if (canEditShare($GLOBALS['requested_share']))
	{
		$listing_head .= '<li><a href="'.htmlentities(pathinfo($_SERVER['SCRIPT_NAME'])['dirname'].'/upload.php?path='.urlencode($GLOBALS['requested_full_path']).'').'">Upload</a></li>';
	}*/
	if (inGroup('root'))
	{
		$listing_head .= '<li><a href="'.htmlentities(pathinfo($_SERVER['SCRIPT_NAME'])['dirname'].'/admin.php').'">Administration</a></li>';
	}
	$listing_head .= '<li><a href="'.htmlentities(pathinfo($_SERVER['SCRIPT_NAME'])['dirname'].'/account.php').'">My Account</a></li>';
	$listing_head .= '<li><a href="'.htmlentities(pathinfo($_SERVER['SCRIPT_NAME'])['dirname'].'/logout.php').'">Log Out</a></li>';
	$listing_head .= '</ul>
			</div>
			<div class="title">/'.htmlentities($GLOBALS['requested_full_path']).' - Garnet DeGelder\'s File Manager on '.htmlentities($_SERVER['HTTP_HOST']).'</div>
		</div>
		<div class="content">
		';
	$listing_tail = '
		</div>
		<div class="footer">
			Copyright &copy; 2017  Garnet DeGelder
		</div>
	</body>
</html>
';
	echo $listing_head . $body . $listing_tail;
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
							'<td><a href="'.htmlentities($file['uri']).'?'.urlencode(session_name()).'='.urlencode(session_id()).'&download">Download</a></td>'.
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
