<?php
require ROOT . 'system/database/'.SYSCONFIG::database_driver.'.php';

// DB::$debug_sql_errors = SYSCONFIG::debug_sql_errors;
DB::$detailed_errors = SYSCONFIG::show_detailed_errors;

DB::connect(
  SYSCONFIG::$dbinfo['host'],
  SYSCONFIG::$dbinfo['db'],
  SYSCONFIG::$dbinfo['user'],
  SYSCONFIG::$dbinfo['pw'],
  SYSCONFIG::$dbinfo['charset']
);

// SYSCONFIG::$dbinfo = array();
?>