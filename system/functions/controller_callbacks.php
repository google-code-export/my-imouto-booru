<?php
function set_actions() {
  $actions = array_filter(func_get_args());
  
  if (count($actions) === 1 && is_array($actions[0]))
    $actions = $actions[0];
  
  ActionController::$actions = array_merge(ActionController::$actions, $actions);
}

function before_filter($functions, $filter = null, $actions = null) {
  if ($filter) {
    $actions = explode(',', str_replace(' ', '', $actions));
    
    if (!filter_actions($filter, $actions))
      return;
  }
  
  check_array($functions);
  
  foreach($functions as $func => $params) {
    if (is_numeric($func)) {
      $func = $params;
      $params = array();
    } else
      check_array($params);
    
    call_user_func_array($func, $params);
  }
}

function after_filter($functions, $filter = null, $actions = null) {
  if ($filter) {
    $actions = explode(',', str_replace(' ', '', $actions));
    
    if (!filter_actions($filter, $actions))
      return;
  }
  
  check_array($functions);
  
  foreach($functions as $func => $params) {
    if (is_numeric($func)) {
      $func = $params;
      $params = array();
    } else
      check_array($params);
    
    ActionController::$after_filters[] = array($func, $params);
  }
}

function filter_actions($filter, $actions) {
  switch ($filter) {
    case 'only':
      if(!in_array(Request::$action, $actions))
        return;
    break;
    
    case 'except':
      if(in_array(Request::$action, $actions))
        return;
    break;
  }
  return true;
}

function response_format($params) {
  
}

/**
 * Verify request method
 *
 * @param string $method  - Request method to verify (post, get, etc).
 * @param array $actions  - Controller's actions to verify. The first value must be
 *                          either 'only' or 'except' to filter actions.
 * @param array callback  - Not yet supported. Custom callback function to call in case the verification fails.
 *                          By default the script will exit with a 400 HTTP status.
 */
function verify_method($method, $actions, $callback = array()) {
  $filter = array_shift($actions);
  
  if(!filter_actions($filter, $actions))
    return;
  
  if (Request::$method !== strtoupper($method)) {
    exit_with_status(400);
  }
}
?>