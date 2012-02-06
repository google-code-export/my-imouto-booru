<?php
# Only 'mysql' or 'pdo'.
System::$conf->database_driver = 'mysql';

System::$conf->dbinfo = array(
  'host'    => "localhost",
  'user'    => "",
  'pw'      => "",
  'db'      => "",
  'charset' => ''
);
  
# If files were deployed in root, set to null.
System::$conf->dir_base = null;
  
System::$conf->autoload_models = array(
  'post',
  'user',
  'pool',
  'poolpost'
);
  
# Database debug.
System::$conf->show_detailed_errors = false;
  
/**
 * When json request, if an error occurs, it will be output in json.
 * Used for debug.
 */
System::$conf->show_errors_on_json = false;

# For debug, show detailed information whenever an error occurs.
# Also for exiting with a message in render_markup_default.php instead
# of exiting with status 500.
System::$conf->system_error_reporting = false;
  
# For E_ALL compliant sake... die at the minimum error.
# Only works if system_error_reporting is activated.
System::$conf->die_at_error = false;

/**
 * Parse routes with PHP
 *
 * Routes can be parsed by htaccess or PHP.
 * Enabling this means routes are Not defined in htaccesss, leaving
 * them to PHP.
 */
System::$conf->php_parses_routes = false;
?>