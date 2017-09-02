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
	if (GD_FILEMANAGER_NATURAL_SORT)
	{
		usort($listing, function($a,$b){return strnatcasecmp($a['name'], $b['name']);});
	}
	else
	{
		usort($listing, function($a,$b){return strcasecmp($a['name'], $b['name']);});
	}
	return $listing;
}
function sortNameDesc($listing)
{
	// Z-A
	if (GD_FILEMANAGER_NATURAL_SORT)
	{
		usort($listing, function($a,$b){return strnatcasecmp($b['name'], $a['name']);});
	}
	else
	{
		usort($listing, function($a,$b){return strcasecmp($b['name'], $a['name']);});
	}
	return $listing;
}
function sortDateAsc($listing)
{
	// earlier to later
	usort($listing, function($a,$b){return $a['last_modified'] - $b['last_modified'];});
	return $listing;
}
function sortDateDesc($listing)
{
	// later to earlier
	usort($listing, function($a,$b){return $b['last_modified'] - $a['last_modified'];});
	return $listing;
}
function sortSizeAsc($listing)
{
	// less to greater
	usort($listing, function($a,$b){return $a['size'] - $b['size'];});
	return $listing;
}
function sortSizeDesc($listing)
{
	// greater to less
	usort($listing, function($a,$b){return $b['size'] - $a['size'];});
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
function sortListingAsRequested($listing)
{
	if (!isset($_SESSION['sort_field'], $_SESSION['sort_order']))
	{
		$_SESSION['sort_field'] = 'name';
		$_SESSION['sort_order'] = 'asc';
	}
	if (isset($_GET['sort'], $_GET['order']))
	{
		$_SESSION['sort_field'] = $_GET['sort'];
		$_SESSION['sort_order'] = $_GET['order'];
	}
	return sortListing($listing, $_SESSION['sort_field'], $_SESSION['sort_order']);
}

function prettifyFileSize($size)
{
	if ($size < (2**10))
	{
		return sprintf("%d B", $size);
	}
	if ($size < (2**20))
	{
		return sprintf("%01.1f KiB", ($size / (2**10)));
	}
	if ($size < (2**30))
	{
		return sprintf("%01.1f MiB", ($size / (2**20)));
	}
	if ($size < (2**40))
	{
		return sprintf("%01.1f GiB", ($size / (2**20)));
	}
	return sprintf("%01.1f TiB", ($size / (2**30)));
}

function prettifyFileCount($count)
{
	if ($count < (10**3))
	{
		return $count;
	}
	if ($count < (10**6))
	{
		return sprintf("%01.1fK", ($count / (10**3)));
	}
	return sprintf("%01.1fM", ($count / (10**6)));
}

// just adds an "s"
function pluralize($number, $string)
{
	if ($number == 1)
	{
		return $number . ' ' . $string;
	}
	else
	{
		return $number . ' ' . $string . 's';
	}
}
// gives human-readable date (eg. 5 hours ago)
function prettifyDate($date)
{
	//return date(GD_FILEMANAGER_DATE_FORMAT, $date);

	$dateDiff = time() - $date;
	if ($dateDiff >= 31557600) // 1 year
	{
		return pluralize(round($dateDiff / 31557600), 'year'). ' ago';
	}
	if ($dateDiff >= 2635200) // 1 month
	{
		return pluralize(round($dateDiff / 2635200), 'month'). ' ago';
	}
	if ($dateDiff >= 86400) // 1 day
	{
		return pluralize(round($dateDiff / 86400), 'day'). ' ago';
	}
	if ($dateDiff >= 3600) // 1 hour
	{
		return pluralize(round($dateDiff / 3600), 'hour'). ' ago';
	}
	if ($dateDiff >= 60) // 1 minute
	{
		return pluralize(round($dateDiff / 60), 'minute'). ' ago';
	}
	return pluralize(round($dateDiff), 'second'). ' ago';
}

function getNextSortRequestString($field)
{
	$currentOrder = 'asc';
	if (isset($_SESSION['sort_order']))
	{
		$currentOrder = $_SESSION['sort_order'];
	}
	$newOrder = $currentOrder;
	switch ($currentOrder)
	{
		case 'asc':
			$newOrder = 'desc';
			break;
		case 'desc':
			$newOrder = 'asc';
			break;
		default:
			$newOrder = 'desc';
	}
	return 'sort='.urlencode($field).'&order='.urlencode($newOrder);
}

function serveShareListing()
{
	outputStandardTemplateHeader('/'.$GLOBALS['requested_full_path']);
	$shares = sortListingAsRequested(getShareList());
	echo '<table class="listing"><thead>'.
			'<tr>'.
			'<th></th>'.
			'<th><span title="Sort by name"><a href="'.htmlentities(getCurrentHttpUri() . '?'. getNextSortRequestString('name')).'">Name</a></span></th>'.
			'<th><span title="Sort by time last modified"><a href="'.htmlentities(getCurrentHttpUri() . '?'. getNextSortRequestString('last-modified')).'">Last Modified</a></span></th>'.
			'<th><span title="Sort by size"><a href="'.htmlentities(getCurrentHttpUri() . '?'. getNextSortRequestString('size')).'">Size</a></span></th>'.
			'<th></th>'.
			'</tr></thead><tbody>';
	foreach ($shares as $share)
	{
		if (canViewShare($share['name']))
		{
			echo '<tr>'.
					'<td class="icon"><span title="Directory"><img src="'.htmlentities(pathinfo($_SERVER['SCRIPT_NAME'])['dirname']).'/icon/folder.gif" alt="[DIR]" width="20" height="22"></span></td>'.
					'<td><span title="'.htmlentities($share['name']).'/"><a href="'.htmlentities($share['uri']).'">'.htmlentities($share['name']).'/</a></span></td>'.
					'<td><span title="'.htmlentities(date(GD_FILEMANAGER_DATE_FORMAT, $share['last_modified'])).'"><em>'.htmlentities(prettifyDate($share['last_modified'])).'</em></span></td>'.
					'<td><span title="Contains '.htmlentities(pluralize($share['size'], 'file')).'">'.htmlentities(prettifyFileCount($share['size'])).'</span></td>'.
					'<td></td>'.
					'</tr>';
		}
	}
	echo '</tbody></table>';
	outputStandardTemplateFooter();
}

function serveDirectoryListing($share, $path)
{
	// check if the share is readable (note: not necessarily visible)
	if (canReadShare($share))
	{
		$dir_list = sortListingAsRequested(getDirectoryListing($share, $path));
		if ($dir_list !== false)
		{
			outputStandardTemplateHeader('/'.$GLOBALS['requested_full_path']);
			echo '<table class="listing"><thead>'.
					'<tr>'.
					'<th></th>'.
					'<th><span title="Sort by name"><a href="'.htmlentities(getCurrentHttpUri() . '?'. getNextSortRequestString('name')).'">Name</a></span></th>'.
					'<th><span title="Sort by time last modified"><a href="'.htmlentities(getCurrentHttpUri() . '?'. getNextSortRequestString('last-modified')).'">Last Modified</a></span></th>'.
					'<th><span title="Sort by size"><a href="'.htmlentities(getCurrentHttpUri() . '?'. getNextSortRequestString('size')).'">Size</a></span></th>'.
					'<th></th>'.
					'</tr></thead><tbody>';
			echo '<tr>'.
					'<td class="icon"><span title="Back"><img src="'.htmlentities(pathinfo($_SERVER['SCRIPT_NAME'])['dirname']).'/icon/back.gif" alt="[PARENTDIR]" width="20" height="22"></span></td>'.
					'<td><span title="Go back up a directory"><a href="'.getParentHttpUri(shareStringAndPathStringToFullPathString($share, $path)).'">[Parent Directory]</a></span></td>'.
					'<td></td>'.
					'<td></td>'.
					'<td></td>'.
					'</tr>';
			foreach ($dir_list as $file)
			{
				if (!GD_FILEMANAGER_HIDDEN_FILES || substr($file['name'], 0, 1) !== '.')
				{
					if ($file['basic_type'] === 'file')
					{
						// add the session id and open in new tab
						echo '<tr>'.
								'<td class="icon"><span title="File"><img src="'.htmlentities(pathinfo($_SERVER['SCRIPT_NAME'])['dirname']).'/icon/generic.gif" alt="[FILE]" width="20" height="22"></span></td>'.
								'<td><span title="'.htmlentities($file['name']).'"><a href="'.htmlentities($file['uri']).'?'.urlencode(session_name()).'='.urlencode(session_id()).'" target="_blank">'.htmlentities($file['name']).'</a></span></td>'.
								'<td><span title="'.htmlentities(date(GD_FILEMANAGER_DATE_FORMAT, $file['last_modified'])).'">'.htmlentities(prettifyDate($file['last_modified'])).'</span></td>'.
								'<td><span title="'.htmlentities(number_format($file['size'], 0, '.', ',')).' bytes">'.htmlentities(prettifyFileSize($file['size'])).'</span></td>'.
								'<td><span title="Download '.htmlentities($file['name']).'"><a href="'.htmlentities($file['uri']).'?'.urlencode(session_name()).'='.urlencode(session_id()).'&amp;download">Download</a></span></td>'.
								'</tr>';
					}
					else if ($file['basic_type'] === 'directory')
					{
						// just open in the same tab
						echo '<tr>'.
								'<td class="icon"><span title="Directory"><img src="'.htmlentities(pathinfo($_SERVER['SCRIPT_NAME'])['dirname']).'/icon/folder.gif" alt="[DIR]" width="20" height="22"></span></td>'.
								'<td><span title="'.htmlentities($file['name']).'/"><a href="'.htmlentities($file['uri']).'">'.htmlentities($file['name']).'/</a></span></td>'.
								'<td><span title="'.htmlentities(date(GD_FILEMANAGER_DATE_FORMAT, $file['last_modified'])).'">'.htmlentities(prettifyDate($file['last_modified'])).'</span></td>'.
								'<td><span title="Contains '.htmlentities(pluralize(number_format($file['size'], 0, '.', ','), 'file')).'">'.htmlentities(prettifyFileCount($file['size'])).'</span></td>'.
								'<td></td>'.
								'</tr>';
					}
					else
					{
						// the "?"s avoid causing potential errors
						echo '<tr>'.
								'<td class="icon"><span title="Unknown"><img src="'.htmlentities(pathinfo($_SERVER['SCRIPT_NAME'])['dirname']).'/icon/unknown.gif" alt="[ ? ]" width="20" height="22"></span></td>'.
								'<td><span title="'.htmlentities($file['name']).'"><a href="'.htmlentities($file['uri']).'">'.htmlentities($file['name']).'</a></span></td>'.
								'<td><span title="'.htmlentities(date(GD_FILEMANAGER_DATE_FORMAT, $file['last_modified'])).'?">'.htmlentities(prettifyDate($file['last_modified'])).'?</span></td>'.
								'<td><span title="Unknown size">?</span></td>'.
								'<td></td>'.
								'</tr>';
					}
				}
			}
			echo '</tbody></table>';
			outputStandardTemplateFooter();
		}
		else
		{
			require_once('notfound.php');
			serveNotFoundMessage();
		}
	}
	else
	{
		require_once('notfound.php');
		serveNotFoundMessage();
	}
}
