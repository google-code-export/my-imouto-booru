<?php
class Cookies {
  static $list = array();
  
  static function put($name, $value, $time = 0) {
    if(is_array($value))
      $value = implode("\r\n", $value);
    
    if($time)
      $time += time();
    setcookie($name, $value, $time, '/');
  }
  
  static function delete($name, $time = null) {
    if(!$time)
      $time = 31556926;
    $time -= time();
    setcookie($name, '', $time, '/');
  }
  
  static function put_list() {
    if(!self::$list)
      return;
    foreach(self::$list as $name => $value) {
      
      self::put($name, $value);
    }
  }
}
?>