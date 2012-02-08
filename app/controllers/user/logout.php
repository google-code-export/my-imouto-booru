<?php
set_title('Logout');

session_regenerate_id();
session_destroy();
$_SESSION = array();
session_start();
cookie_remove('login');
cookie_remove('pass_hash');

$dest = isset(Request::$params->from) ? Request::$params->from : '#home';

respond_to_success("You are now logged out", $dest);
?>