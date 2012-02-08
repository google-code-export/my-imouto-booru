<?php
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