<?php
# Only 'mysql' or 'pdo'.
System::$conf->database_driver = 'mysql';

System::$conf->dbinfo = array(
  'host'    => "localhost",
  'user'    => "",
  'pw'      => "",
  'db'      => "myimouto",
  'charset' => ''
);

# System Admin configuration.
System::$conf->sysadmin_base_url = 'sysadmin';
System::$conf->sysadmin_host_addr = '127.0.0.1';

# If files were deployed in root, set to null.
System::$conf->dir_base = null;
?>