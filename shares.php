<?php
if (!defined('GD_FILEMANAGER_VERSION'))
{
	http_response_code(403);
	exit('Error! No direct script access allowed!');
}

// used in checking user permissions with the shares
// if no args, gets currently requested share
function getShareData($requested_share = null)
{
	if (is_null($requested_share))
	{
		$requested_share = $GLOBALS['requested_share'];
	}
	foreach ($_SESSION['shares'] as $share)
	{
		if ($requested_share === $share['NAME'])
		{
			return $share;
		}
	}
	return null;
}

function getShareList()
{
	$share_array = array();
	foreach ($_SESSION['shares'] as $share)
	{
		$share_array[] = array('name' => $share['NAME'],
				'uri' => getHttpUri($share['NAME']),
				'basic_type' => 'directory');
	}
	return $share_array;
}

// for checking share permissions
// if no args passed, defaults to currently requested share.
// returns true if the share is blank (ie. for the share list)
// this can be used to check if the share exists
function canViewShare($share)
{
	if ($share === '')
	{
		return true;
	}
	$share_data = getShareData($share);
	if (!is_null($share_data))
	{
		$required_groups = explode(',', trim($share_data['GROUPS_VISIBLE'], ','));
		//$required_groups[] = 'root'; // add root so they can access everything
		$joined_groups = $_SESSION['groups'];
		return (count(array_intersect($joined_groups, $required_groups)) > 0);
	}
	else
	{
		return false;
	}
}
function canReadShare($share)
{
	if ($share === '')
	{
		return true;
	}
	$share_data = getShareData($share);
	if (!is_null($share_data))
	{
		$required_groups = explode(',', trim($share_data['GROUPS_ACCESS_FILES'], ','));
		//$required_groups[] = 'root'; // add root so they can access everything
		$joined_groups = $_SESSION['groups'];
		return (count(array_intersect($joined_groups, $required_groups)) > 0);
	}
	else
	{
		return false;
	}
}
function canEditShare($share)
{
	if ($share === '')
	{
		return false;
	}
	$share_data = getShareData($share);
	if (!is_null($share_data))
	{
		$required_groups = explode(',', trim($share_data['GROUPS_MODIFY_FILES'], ','));
		//$required_groups[] = 'root'; // add root so they can access everything
		$joined_groups = $_SESSION['groups'];
		return (count(array_intersect($joined_groups, $required_groups)) > 0);
	}
	else
	{
		return false;
	}
}

function getNumberOfVisibleShares()
{
	$visible_share_count = 0;
	foreach ($_SESSION['shares'] as $share)
	{
		if (canViewShare($share['NAME']))
		{
			$visible_share_count++;
		}
	}
	return $visible_share_count;
}

function getNumberOfReadableShares()
{
	$visible_share_count = 0;
	foreach ($_SESSION['shares'] as $share)
	{
		if (canReadShare($share['NAME']))
		{
			$visible_share_count++;
		}
	}
	return $visible_share_count;
}

function getNumberOfEditableShares()
{
	$visible_share_count = 0;
	foreach ($_SESSION['shares'] as $share)
	{
		if (canEditShare($share['NAME']))
		{
			$visible_share_count++;
		}
	}
	return $visible_share_count;
}
