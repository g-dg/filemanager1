<?php
if (!defined('GD_FILEMANAGER_VERSION'))
{
	http_response_code(403);
	exit('Error! No direct script access allowed!');
}

// must be passed a valid SQLite 3 query
function dbQuery($query)
{
	// create the database if not yet created
	if (!isset($GLOBALS['dbcon']))
	{
		$GLOBALS['dbcon'] = new SQLite3(GD_FILEMANAGER_DATABASE_FILE);
		$result = $GLOBALS['dbcon']->query('PRAGMA user_version;');
		$user_version = $result->fetchArray(SQLITE3_NUM);
		if ($user_version[0] !== 0) {
			http_response_code(500);
			die('Database error! (Tried to use an incompatible database version)');
		}
	}
	if (($result = @$GLOBALS['dbcon']->query($query)) === false)
	{
		http_response_code(500);
		die('Database error! (The application might not be set up)');
	}
	$array = array();
	while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
		$array[] = $row;
	}
	return $array;
}

// used to execute something
// returns the value
function dbExec($query)
{
	// create the database if not yet created
	if (!isset($GLOBALS['dbcon']))
	{
		$GLOBALS['dbcon'] = new SQLite3(GD_FILEMANAGER_DATABASE_FILE);
	}
	return $GLOBALS['dbcon']->exec($query);
}
