<?php
define('GD_FILEMANAGER_VERSION', '1.0.2');

session_start();
session_unset();
//session_regenerate_id(true);
//setcookie(session_name(), '', time() - 1);
header('Location: '.pathinfo($_SERVER['SCRIPT_NAME'])['dirname'].'/');
exit();
