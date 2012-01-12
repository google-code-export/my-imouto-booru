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

class ApplicationHelpers {
  
  function __construct($helpers) {
    foreach ($helpers as $helper)
      $this->$helper = true;
  }
  
  function load($helper) {
    if (empty($this->$helper))
      return;
    
    include ROOT."app/helpers/" . $helper . "_helper.php";
    unset($this->$helper);
  }
}
?>