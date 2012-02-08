<?php
class Collection extends ActiveRecord {
  function _construct($data = null) {
    if($data) {
      $i = 0;
      foreach($data as $d) {
        $this->$i = new $this->model_name($d);
        $i++;
      }
    }
    
    unset($this->model_name);
    unset($this->t);
  }
  
  /**
   * Searches objects for a property with a value and returns object.
   */
  function search($prop, $value) {
  
    foreach ($this as &$obj) {
      if ($obj->$prop == $value)
        return $obj;
    }
    
    return false;
  }
  
  # returns an *array* with the models that matches the options.
  # $posts->select(array('is_active' => true, 'user_id' => 4));
  function select($opts) {
    $objs = array();
    
    foreach ($this as $obj) {
      foreach ($opts as $prop => $cond) {
        if (!$obj->$prop || $obj->$prop != $cond)
          continue;
        $objs[] = $obj;
      }
    }
    
    return $objs;
  }
  
  function to_xml() {
    if (empty($this->{0}))
      return;
    
    $xml = '<' . strtolower($this->{0}->t()) . '>';
    
    foreach ($this as $obj) {
      if (method_exists($obj, 'to_xml'))
        $xml .= $obj->to_xml();
      else
        $xml .= to_xml($obj, null, array('skip_instruct' => true));
    }
    
    $xml .= '</' . strtolower($this->{0}->t()) . '>';
    
    return $xml;
  }
}
?>