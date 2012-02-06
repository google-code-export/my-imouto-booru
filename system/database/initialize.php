<?php
require ROOT . 'system/database/'.System::$conf->database_driver.'.php';

// DB::$debug_sql_errors = SYSCONFIG::debug_sql_errors;
DB::$detailed_errors = System::$conf->show_detailed_errors;

DB::connect(
  System::$conf->dbinfo['host'],
  System::$conf->dbinfo['db'],
  System::$conf->dbinfo['user'],
  System::$conf->dbinfo['pw'],
  System::$conf->dbinfo['charset']
);

// System::$conf->dbinfo = array();
?>