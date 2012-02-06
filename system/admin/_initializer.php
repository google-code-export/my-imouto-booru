<?php
$host_addr = '127.0.0.1';

$_SERVER['REMOTE_ADDR'] == $host_addr || die("<p><h2>FORBIDDEN FOR CLIENT ${_SERVER['REMOTE_ADDR']}</h2></p>");

$sysadmin = true;

define('SYSROOT', str_replace('/admin', '', str_replace('\\', '/', dirname(__FILE__)).'/'));
define('ROOT', str_replace('system/', '', SYSROOT));

require ROOT.'config/config.php';

require SYSROOT.'system.php';
require SYSROOT.'config/config.php';

require SYSROOT.'database/initialize.php';
require SYSROOT.'load_functions.php';
require SYSROOT.'admin/_functions.php';

$table_data_file = SYSROOT."data/database_tables.php";

$request_uri = preg_replace('~\?(.*)$~', '', $_SERVER['REQUEST_URI']);
$action = str_replace(System::$conf->dir_base . '/sysadmin/', '', $request_uri);

if (!$action)
  $action = 'index';

if (!file_exists("$action.php"))
  die("Such action ($action.php) does not exist.");

require '_renderer.php';
?>