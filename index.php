<?php
require_once('version.php');

// load various required files
require_once('config.php');
require_once('session.php');
require_once('database.php');
require_once('auth.php');
require_once('paths.php');
require_once('shares.php');
require_once('router.php');

// run various inits in those files
startSession();
checkUserIP();
authenticate();
setUpPaths();

// pass off to the router which passes off to the appropriate server.
// note that currently the session ends here to avoid session lock when serving files.
route();
