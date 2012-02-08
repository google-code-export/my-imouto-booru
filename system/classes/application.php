<?php
class Application {
  static $models;
  static $helpers;
  
  static function load_files() {
    self::$models = new ApplicationModelBase();
    
    require SYSROOT . 'data/app_helpers.php';
    self::$helpers = new ApplicationHelpers($helpers);
  }
}
?>