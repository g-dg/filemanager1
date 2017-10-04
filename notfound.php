<?php
if (!defined('GD_FILEMANAGER_VERSION'))
{
	http_response_code(403);
	exit('Error! No direct script access allowed!');
}

require_once('template.php');

function serveNotFoundMessage()
{
	http_response_code(404);
	outputStandardTemplateHeader('/'.$GLOBALS['requested_full_path']);
	echo '<span class="error">Error: Either the file or folder "/' . htmlentities($GLOBALS['requested_full_path']) . '" does not exist, or you don\'t have permission to view it.</span>
<br />
<a href="'.htmlentities(pathinfo($_SERVER['SCRIPT_NAME'])['dirname'] . '/').'">&lt; Back to main listing</a>';
	outputStandardTemplateFooter();
	exit();
}
