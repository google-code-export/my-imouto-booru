<?php
System::$conf->environment = 'production';

System::$conf->autoload_models = array(
  'post',
  'user',
  'pool',
  'poolpost',
  'forumpost'
);

/**
 * Parse routes with PHP
 *
 * Routes can be parsed by htaccess or PHP.
 * Enabling this means routes are Not defined in htaccesss, leaving
 * them to PHP.
 */
System::$conf->php_parses_routes = true;

if (System::$conf->environment == 'development') {

  System::$conf->show_detailed_errors = true;
  System::$conf->show_errors_on_json = true;
  System::$conf->system_error_reporting = true;
  System::$conf->die_at_error = true;
} else {

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
}
?>