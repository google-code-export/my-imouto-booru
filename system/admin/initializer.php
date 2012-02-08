<?php
$_SERVER['REMOTE_ADDR'] == System::$conf->sysadmin_host_addr || die("<p><h2>FORBIDDEN FOR CLIENT ${_SERVER['REMOTE_ADDR']}</h2></p>");

define('ADMINROOT', str_replace('\\', '/', dirname(__FILE__)) . '/');

require ADMINROOT . 'functions.php';

require 'renderer.php';
?>