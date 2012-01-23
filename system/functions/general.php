<?php
# Creates an stdClass out of an array.
function array2obj($arr, $rec = true, $pretty_props = true, $sufix = '_') {
  $c = new stdClass;
  add_props($c, $arr, $rec, $pretty_props, $sufix);
  return $c;
}

/**
 * Add properties
 *
 * Adds properties to an object from array.
 */
function add_props(&$obj, $arr, $rec = true, $pretty_props = true, $sufix = '_') {
  foreach ($arr as $k => $v) {
    (is_array($v) && $rec) && $v = array2obj($v);
    preg_match('~^[\d]~', $k) && $k = $sufix.$k;
    $pretty_props && $k = preg_replace('~[\W]~', '', $k);
    
    $obj->$k = $v;
  }
}

# Converts a array2obj back to array, recursively and changing back pretty_props.
function obj2array($obj) {
  $new = array();
  foreach ($obj as $prop => &$val) {
    if (is_object($val))
      $new[] = obj2array($val);
    else {
      if (strpos($prop, '_') === 0 && is_numeric(str_replace('_', '', $prop)))
        $new[(int)str_replace('_', '', $prop)] = $obj->$prop;
      else
        $new[$prop] = $obj->$prop;
    }
    
    unset($val);
  }
  return $new;
}

function is_indexed_arr($arr) {
  if (!is_array($arr) || empty($arr))
    return false;
  
  $i = 0;
  foreach(array_keys($arr) as $k) {
    if($k !== $i)
      return false;
    $i++;
  }
  return true;
}

function between($num, $min, $max) {
  return $num >= $min && $num <= $max;
}

function empty_obj(&$object, $ignore_private = true, $ignore_protected = true) {
  $obj_name = get_class($object);
  $obj = (array)$object;
  
  foreach(array_keys($obj) as $prop) {
    $is_private = $is_protected = false;
    
    $prop = preg_replace("/[^\w*]/", '', $prop);
    $prop_name = str_replace(array($obj_name, '*'), '', $prop);
    
    if(preg_match("~^$obj_name$prop_name$~", $prop))
      $is_private = true;
    if(preg_match("~^\*$prop_name$~", $prop))
      $is_protected = true;
    
    if(!$is_private || !$is_protected || ($is_private && !$ignore_private) || ($is_protected && !$ignore_protected))
      return;
  }
  return true;
}

function check_array(&$var){
  if (is_array($var))
    return;
  $var = array($var);
}

/**
 * Given an array, the first key is taken as the name of the array in question.
 * The other keys are taken as the keys for the array in question.
 * Dunno how useful this is outside this system...
 *
 * Returns null on error.
 */
// # Request::$params->user[name]
# foo[bar] => $foo['bar']
# [foo][bar] => $var_to_check['foo']['bar']
function isset_array($arr, &$var_to_check = null) {
  $arr = str_replace(array('[', ']'), array("['", "']"), $arr);
  $result = null;
  
  if ($var_to_check) {
    $ext_var = (array)$var_to_check;
    
  } else {
    $var_to_check = substr($arr, 0, strpos($arr, '['));
    global ${$var_to_check};
    
    if (!isset(${$var_to_check}))
      return;
    
    $ext_var = (array)${$var_to_check};
    $arr = str_replace_limit($var_to_check, '', $arr);
  }
  
  eval ('isset($ext_var'.$arr.') && $result = $ext_var'.$arr.';');
  return $result;
}

# Just a quick way to return gmdate().
function gmd($format = 'Y-m-d H:i:s'){
  return gmdate($format);
}

function datetime_to_timestamp($datetime) {
  $date = new DateTime($datetime);
  return $date->getTimestamp();
}

/**
 * GMD math.
 *
 * Adds, substracts or calculates difference of a date interval from current date.
 * For more info check the DateTime class in the PHP manual.
 *
 * @param string $type - 'add', 'sub' or 'diff'.
 * @param string $q    - days, hours, mins or seconds to do the math with.
 */
function gmd_math($type, $q, $format = 'Y-m-d H:i:s') {
  $dt = new DateTime(gmd());
  
  if ($type == 'diff') {
    $dt2 = new DateTime($q);
    $dt = date_diff($dt, $dt2);
    
  } else {
    $dt->$type(new DateInterval("P$q"));
  }
  
  return $dt->format($format);
}

/**
 * Compare number
 *
 * Used by validate_data()
 */
function compare_num($num, $comparison, $return_msg = false, $comparing_str = true) {
  if (is_int(strpos($comparison, '..'))) {
    $operator = '..';
  } elseif (ctype_digit(substr($comparison, 1, 1)) || substr($comparison, 1, 1) == '-') {
    $operator = substr($comparison, 0, 1);
  } else {
    $operator = substr($comparison, 0, 2);
  }
  
  switch ($operator) {
    case '..':
      list($min, $max) = explode('..', $comparison);
      if ($min > $max)
        return;
      $min = (float)$min;
      $max = (float)$max;
      
      if ($num >= $min && $num <= $max)
        return true;
      elseif ($num < $min)
        $msg = $comparing_str ?
          "is too short (must be between $min and $max characters)":
          "is too low (must be between $min and $max)";
      elseif ($num > $max)
        $msg = $comparing_str ?
          "is too big (must be between $min and $max characters)":
          "is too big (must be between $min and $max)";
    break;
    
    case '>':
      $comparison = (float)substr($comparison, 1);
      
      if ($num > $comparison)
        return true;
      
      $msg = $comparing_str ?
        "is too short (must have more than $comparison characters)":
        "is too low (must be greater than $comparison)";
    break;
    
    case '>=':
      $comparison = (float)substr($comparison, 2);
      
      if ($num >= $comparison)
        return true;
      
      $msg = $comparing_str ?
        "is too short (must be at least $comparison characters long)":
        "is too low (must be equal or greater than $comparison)";
    break;
    
    case '<':
      $comparison = (float)substr($comparison, 1);
      
      if ($num < $comparison)
        return true;
      
      $msg = $comparing_str ?
        "is too big (must have less than $comparison characters)":
        "is too big (must be lower than $comparison)";
    break;
    
    case '<=':
      $comparison = (float)substr($comparison, 2);
      
      if ($num <= $comparison)
        return true;
      
      $msg = $comparing_str ?
        "is too big (must be $comparison characters max)":
        "is too big (must be equal or lower than $comparison)";
    break;
    
    case '==':
      $comparison = (float)substr($comparison, 2);
      
      if ($num === $comparison)
        return true;
      elseif ($num < $comparison)
        $msg = $comparing_str ?
          "is too short (must be $comparison characters long)":
          "is too low (must be equal to $comparison)";
      elseif ($num > $comparison)
        $msg = $comparing_str ?
          "is too big (must be $comparison characters long)":
          "is too big (must be equal to $comparison)";
    break;
  }
  
  return $return_msg ? $msg : false;
}

# TODO: incomplete function.
# returns false if $data is null unless $params('accept_null' => true)
# $return_messages : for ActiveRecord::validate_data().
function validate_data($data, $callbacks, $return_messages = false) {
  check_array($callbacks);
  
  foreach ($callbacks as $key => $val) {
    $func = $args = null;
    
    if (!is_int($key)) {
      if ($data === null) {
        if (empty($callbacks['accept_null']))
          return false;
        else
          unset($callbacks['accept_null']);
      }
      
      if (is_array($val) && isset($val['message'])) {
        $message = $val['message'];
        unset($val['message']);
      }
      
      switch ($key) {
        case '%callback%':
          $func = $val;
          $args = array($data);
        break;
        
        # Checks a string's length.
        case 'length':
          check_array($val);
          $compare_to = array_shift($val);
          
          // $messages = $val; // future messages.
          $r = compare_num(strlen($data), $compare_to, $return_messages, true);
          return $r;
        break;
        
        case 'format':
          is_array($val) && $val = array_shift($val);
          
          if (!preg_match($val, $data))
            return false;
        break;
        
        /**
         * Number:
         * ($data, array('number' => array(true, 'odd', 'even', '>=90')))
         * Note: By default the param will be passed to compare_num.
         */
        case 'number':
          check_array($val);
          foreach ($val as $param) {
            switch ($param) {
              case ($param === true):
                if (!ctype_digit((string)$data))
                  return false;
              break;
              
              case 'odd':
                if (!($data&1))
                  return false;
              break;
              
              case 'even':
                if (!($data&2))
                  return false;
              break;
              
              default:
                $r = compare_num(strlen($data), $compare_to, $return_messages, false);
                return $r;
              break;
            }
          }
        break;
        
        case 'in':{
          if (!in_array($data, $val))
            return false;
          break;
        }
          
        
      }
      
    } else {
      $func = $val;
      $args = array($data);
    }
    
    if (!empty($func)) {
      if (!call_user_func_array($func, $args)) {
        return false;
      }
    }
  }
  return true;
}

# Difference from compare_num: this one simply returns true or false.
function num_compare($num, $op) {
  $comparison_ops = array(
    '<' => function($n, $c){
      return $n < $c;
    },
    '<=' => function($n, $c){
      return $n <= $c;
    },
    '>' => function($n, $c){
      return $n > $c;
    },
    '>=' => function($n, $c){
      return $n >= $c;
    },
    '==' => function($n, $c){
      return $n == $c;
    },
    '..' => function($n, $c){
      list($min, $max) = explode('..', $c);
      return $n >= $min && $n <= $max;
    }
  );
  
  $c = preg_replace('~[\d\.]~', '', $op);
  
  if (array_key_exists($c, $comparison_ops)) {
    if ($c != '..')
      $op = str_replace($c, '', $op);
    return $comparison_ops[$c]($num, $op);
  }
  
  return null;
}

function array_flat($arr) {
  $flat = array();
  foreach ($arr as $v) {
    if (is_array($v))
      $flat = array_merge($flat, array_flat($v));
    else
      $flat[] = $v;
  }
  return $flat;
}

function cycle() {
  static $vars;
  static $cycle = 0;
  
  $args = func_get_args();
  
  # Clear vars if null was passed.
  if (count($args) == 1 && $args[0] === null) {
    $vars = null;
    $cycle = 0;
    return;
  # Reset cycle if new options were given.
  } elseif ($vars && $vars !== $args) {
    $vars = null;
    $cycle = 0;
  }
  
  if (empty($vars))
    $vars = $args;
  if ($cycle > count($vars) - 1)
    $cycle = 0;
  
  $value = $vars[$cycle];
  $cycle++;
  return $value;
}

# Pings a site without waiting for a response.
function curl_request_async($url, $params, $type = 'POST') {
  foreach ($params as $key => &$val) {
    if (is_array($val)) $val = implode(',', $val);
    $post_params[] = $key.'='.urlencode($val);
  }
  
  $post_string = implode('&', $post_params);

  $parts = parse_url($url);

  $fp = fsockopen($parts['host'],
    isset($parts['port'])?$parts['port']:80,
    $errno, $errstr, 30);

  if ('GET' == $type)
    $parts['path'] .= '?' . $post_string;

  $out = "$type ".$parts['path']." HTTP/1.1\r\n";
  $out.= "Host: ".$parts['host']."\r\n";
  $out.= "Content-Type: application/x-www-form-urlencoded\r\n";
  $out.= "Content-Length: ".strlen($post_string)."\r\n";
  $out.= "Connection: Close\r\n\r\n";
  
  if ('POST' == $type && isset($post_string))
    $out.= $post_string;

  fwrite($fp, $out);
  fclose($fp);
}
?>