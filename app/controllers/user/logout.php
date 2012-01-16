<?php
set_title('Logout');

cookie_remove('login');
cookie_remove('pass_hash');
$_SESSION = array();
session_destroy();

$dest = isset(Request::$params->from) ? Request::$params->from : '#home';

respond_to_success("You are now logged out", $dest);
?>