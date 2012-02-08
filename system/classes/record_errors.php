<?php
class RecordErrors {
  function add($attribute, $msg = null) {
    if (!isset($this->$attribute))
      $this->$attribute = array();
    
    $this->{$attribute}[] = $msg;
  }
  
  function add_to_base($msg) {
    $this->add('model_base', $msg);
  }
  
  function on($attribute) {
    if (!isset($this->$attribute))
      return null;
    elseif (count($this->$attribute) == 1)
      return current($this->$attribute);
    else
      return $this->$attribute;
  }
  
  function on_base() {
    return $this->on('model_base');
  }
  
  function full_messages() {
    $full_messages = array();
    
    foreach (array_keys((array)$this) as $attr) {
      foreach ($this->$attr as $msg) {
        if ($attr == 'model_base')
          $full_messages[] = $msg;
        else
          $full_messages[] = ucfirst($attr) . ' ' . $msg;
      }
    }
    
    return $full_messages;
  }
  
  function invalid($attribute) {
    return (isset($this->$attribute));
  }
  
  function blank() {
    $vars = get_object_vars($this);
    return empty($vars);
  }
  
  function count() {
    $i = 0;
    foreach ($this as $attr) {
      foreach ($attr as $e)
        $i++;
    }
    return $i;
  }
}
?>