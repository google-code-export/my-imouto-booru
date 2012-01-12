<?php
set_title('Logout');

Cookies::delete('login');
Cookies::delete('pass_hash');
$_SESSION = array();
session_destroy();

$dest = isset(Request::$params->from) ? Request::$params->from : '#home';

respond_to_success("You are now logged out", $dest);
?>