<?php
class SYSCONFIG {
  # Only 'mysql' or 'pdo'.
  const database_driver = 'mysql';
  
  /**
   * Shows "Warnings" when an error occurs in a system function.
   * Use only when under a development enviroment.
   */
  // const show_function_errors = true;
  
  static $dbinfo = array(
    'host'    => "localhost",
    'user'    => "",
    'pw'      => "",
    'db'      => "myimouto",
    'charset' => 'charset=UTF-8'
  );
  
  # If files were deployed in root, set to null.
  const url_base = null;
  
  static $autoload_models = array(
    'post',
    'user',
    'pool',
    'poolpost'
  );
  
  # Database debug.
  const show_detailed_errors = false;
  
  /**
   * Parse routes with PHP
   *
   * Routes can be parsed by htaccess or PHP.
   * Enabling this means routes are Not defined in htaccesss, leaving
   * them to PHP.
   */
  const php_parses_routes = false;
  
  /**
   * When json request, if an error occurs, it will be output in json.
   * Used for debug.
   */
  const show_errors_on_json = false;

  # For debug, show detailed information whenever an error occurs.
  # Also for exiting with a message in render_markup_default.php instead
  # of exiting with status 500.
  const system_error_reporting = false;
  
  # For E_ALL compliant sake... die at the minimum error.
  # Only works if system_error_reporting is activated.
  const die_at_error = false;
}
?>