<?php
require_once('version.php');

$GLOBALS['script_start_time'] = microtime(true);

// run for 10 minutes max
// (when serving files, this is overwritten)
set_time_limit(600);
