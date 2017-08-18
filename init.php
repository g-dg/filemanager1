<?php
require_once('version.php');

$GLOBALS['script_start_time'] = microtime(true);

// run for at least 10 minutes
// (when serving files, this is overwritten)
set_time_limit(600);
