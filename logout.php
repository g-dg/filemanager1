<?php
require_once('init.php');

session_start();
session_unset();
//session_regenerate_id(true);
//setcookie(session_name(), '', time() - 1);
header('Location: '.pathinfo($_SERVER['SCRIPT_NAME'])['dirname'].'/');
exit();
