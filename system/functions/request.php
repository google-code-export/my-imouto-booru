<?php
/**
 * Set variables in Request::$params automatically
 *
 * This simple checks if variables exist in the $params object,
 * and if they don't, they will be created automatically, avoiding
 * things like:
 * <input type="text" value="<?php echo !empty(Request::$params->query) ? Request::$params->query : null ?>" />
 * and just echo the parameter.
 * <input type="text" value="<?php echo Request::$params->query ?>" />
 *
 * By default the value is null. To give a custom value for a param,
 * make the param the key and the value its value.
 * 
 * Example:
 * auto_create_params(array('query', 'tags' => 'none', 'users->foo->id'));
 * $params->query will be null while $params->tags will be 'none' and
 * $params->users->foo->id will also be null.
 */
function auto_set_params($params, $default_value = null) {
  check_array($params);
  
  foreach ($params as $key => $param) {
    if (is_int($key)) {
      $value = $default_value;
    } else {
      $value = $param;
      $param = $key;
    }
      
    if (property_exists(Request::$params, $param)) {
      continue;
    }
    
    if (is_int(strpos($param, '->'))) {
      $subs = explode('->', $param);
      
      $param_sub = &Request::$params;
      $i = 0;
      foreach ($subs as $sub) {
        if (!property_exists($param_sub, $sub)) {
          if ($i == (count($subs) - 1))
            $param_sub->$sub = $value;
          else
            $param_sub->$sub = new stdClass;
        }
        $param_sub = &$param_sub->$sub;
        $i++;
      }
    } else
      Request::$params->$param = $value;
  }
}

# TODO: make the 404 file configurable.
function die_404(){
  header("HTTP/1.1 404 Not Found");
  require ROOT.'public/404.html';
  exit;
}

/**
 * Shortcut.
 */
function required_params() {
  Request::required_params(func_get_args());
}
?>