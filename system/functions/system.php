<?php
/**
 * When including a model, do so in the first line of the model file.
 */
function include_model() {
  $models = func_get_args();
  
  foreach ($models as $model) {
    $model_name = strtolower(ApplicationModel::filename_to_modelname($model));
    if (empty(Application::$models->$model_name)) {
      echo "Warning: include_model: Model $model_name not found";
      continue;
    }
    Application::$models->$model_name->load();
  }
}

/**
 * Include helper files
 *
 * Accepts strings of the helper files to be included: helper('post', 'comment');
 */
function helper(){
  // vde(Application::$helpers);
  foreach(func_get_args() as $helper)
    Application::$helpers->load($helper);
    // System::include_helper($helper);
}

/**
 * Association Functions.
 *
 * First argument is the property name through which the association will
 * be accessed. Second argument (optional) is an array of SQL parameters
 * like 'foreign_key'.
 * Usually the name of the property is the name of the model if belongs to
 * e.g. $post->note (model = Note).
 *
 * If the name of the property differs from the name of the model ($post->creator,
 * model User), the name of the model must be stated in $parameters['model_name'].
 *
 * In the case of a has_many association, the name of the property equals to
 * the name of the model + 's'. E.g. $pool->pool_posts (model = PoolPost).
 */
function has_one() {
  System::$assocs_temp['has_one'][] = func_get_args();
}

function has_many() {
  System::$assocs_temp['has_many'][] = func_get_args();
}

function belongs_to() {
  System::$assocs_temp['belongs_to'][] = func_get_args();
}

/**
 * Callbacks
 */
function before($action, $functions) {
  System::$callbacks_temp['before_'.$action][] = $functions;
}

function after($action, $functions) {
  System::$callbacks_temp['after_'.$action][] = $functions;
}

function validates($data, $validation = null) {
  System::validations_temp($data, $validation);
}

# Useful when dealing with 'controller#action' route strings.
function parse_url_token($token) {
  if (is_bool(strpos($token, '#')))
    $token .= '#';
  
  $token = explode('#', $token);
  !$token[0] && $token[0] = Request::$controller;
  !$token[1] && $token[1] = 'index';
  return implode('#', $token);
}

/**
 * URL for controller#action
 *
 * Parses a controller#action token and return the URL based on routes.
 * It can also receive any URL, i.e. '/post', 'www.google.com', etc.
 *
 * Special $opts:
 * compare_current_uri (bool): If the current Controller/Action matches the $url token,
 *   the current URL (with no get params) is returned.
 * anchor (string): anchor for the url (i.e. /post/show/1234#c24)
 */
function url_for($url, $opts = array()) {
  // global $ActionController;
  // vde($opts);
  $routes = &ActionController::$routes;
  
  $params = null;
  $used_params = array();
  
  if (!empty($opts['anchor'])) {
    $anchor = $opts['anchor'];
    unset($opts['anchor']);
  } else
    $anchor = null;
  
  if (is_bool(strpos($url, '/')) && is_bool(strpos($url, 'www.'))) {
    
    # Complete the given $url.
    $url = parse_url_token($url);
    
    if (!empty($opts['compare_current_uri'])) {
      list($url_ctrl, $url_act) = explode('#', $url);
      if ($url_ctrl == Request::$controller && $url_act == Request::$action)
        return Request::$url;
      else
        unset($url_ctrl, $url_act, $opts['compare_current_uri']);
    }
    
    list($ctrl, $act) = explode('#', $url);
    
    if (array_key_exists($url, $routes)) {
      # $root isn't an array, there may be more like it.
      if (!is_array($routes[$url]))
        $route = $routes[$url];
      else
        $route = $routes[$url][0];
      
      /**
       * Replacing variables in route for $opts
       */
      # First check if url requires variables, and if they're in $opts
      # This is simmilar to 'Parse variables' in ActionController::routing()
      if (is_int(strpos($route, '$'))) {
        preg_match_all('/\$([\w]+)/', $route, $vars);
        $vars = $vars[1];
        
        foreach ($vars as $var) {
          
          
          // # If url requires parameters and they're not in options,
          // # return (or throw an exception).
          // if (!isset($opts[$var])) // Missing parameter for route.
            // die('Missing parameter for route.');
          if (!isset($opts[$var])) // Missing parameter for route.
            $opts[$var] = false;
          else {
            # If requirement isn't met, return (or throw an exception).
            if (isset($routes[$url]['requirements'][$var])) {
              $req = $routes[$url]['requirements'][$var];
              if (!preg_match('~^'.$req.'$~', $opts[$var]))
                die('Requirement not met for route parameter.'); // Requirement not met for route parameter.
            }
          }
          $used_params[] = $var;
          
          $bl = $opts[$var] === false ? '(?:\/)?' : null;
          $route = preg_replace('~'.$bl.'\$'.addslashes($var).'~', $opts[$var], $route);
        }
      }
      
    } else {

      foreach ($routes as $k => $route) {
        if (!is_int($k))
          continue;
        elseif (is_array($route))
          $r = $route[0];
        else
          $r = $route;
        
        preg_match_all('/\$([\w]+)/', $r, $vars);
        $vars = $vars[1];
        
        /* Find the matching route by checking the route's parameters.
         * All the route's parameters must be set in $opts and must
         * match requirement (if exists), otherwise the route is discarted.
         */
        foreach ($vars as $var) {
          $route_opts = $opts;
          $used_params = array();
          // vd($opts, 1);

          // vd($var, 2);
          if ($var == 'controller' || $var == 'action') {
            # We wont't need controller or action anymore.
            $used_params[] = $var;
            continue;
          } elseif ($var == 'format') {
            # We might need format later.
            continue;
          } 
          
          // # Check if $$ctrl->$var exists to use it in params only if no options were given.
          if (!isset($route_opts[$var])) {
            // global ${$ctrl};
            // if (empty($opts) && isset(${$ctrl}->$var)) {
                // $route_opts[$var] = ${$ctrl}->$var;
            
            // } else
              $route_opts[$var] = false;
          }
          // vde($vars);
          if (isset($route['requirements'][$var]) && !preg_match('~^'.$route['requirements'][$var].'$~', $route_opts[$var]))
            continue 2;
          // vd($var);
          $used_params[] = $var;
          $bl = $route_opts[$var] === false ? '(?:\/)?' : null;
          $r = preg_replace('~'.$bl.'\$'.addslashes($var).'~', $route_opts[$var], $r);
          break;
        }
        // vd($r);
        $opts = array_merge($opts, $route_opts);
        
        # Replace $controller, $action and .$format in $r.
        if (is_int(strpos($r, '$controller')))
          $r = str_replace('$controller', $ctrl, $r);
        
        if (is_int(strpos($r, '$action'))) {
          # If action is 'index', we don't need to show so in the url.
          if ($act == 'index') {
            $r = str_replace(array('/$action', '$action'), '', $r);
          } else
            $r = str_replace('$action', $act, $r);
        }
        
        if (strstr($r, '.$format')) {
          # If format is in route, we won't need it later.
          $used_params[] = 'format';
          
          if (empty($opts['format']) || $opts['format'] == 'html')
            $opts['format'] = null;
          
          $r = str_replace('.$format', $opts['format'], $r);
        }
          
        $found = true;
        break;
      }
      if (!isset($found))
        die('Unable to find route for redirection');
      
      $route = $r;
    }
    
    $route = '/'.$route;
    
  } else
    $route = $url;
  // if (isset($opts['id']) && $opts['id'] == 22) {
    // $used_params;
  // }
  // vd($used_params);
  $opts_left = array_diff(array_keys($opts), $used_params);
  
  if($opts_left) {
    foreach($opts_left as $param)
      $params[] = $param.'='.u($opts[$param]);
    $params = '?' . implode('&', $params);
  }
  $anchor && $route .= '#'.$anchor;
  return $route.$params;
}


// function die_at_error($errno, $errstr, $errfile, $errline){
  // if (!(error_reporting() & $errno)) {
      // return;
  // }
  // # TODO: hardcoded system's folder name.
  // $errfile = addslashes(preg_replace('~^(.*)\/myimouto~', '', str_replace('\\', '/', $errfile)));
  
  // switch ($errno) {
      
    // case E_USER_ERROR:
      // $e = "Fatal: [$errno] $errstr ($errfile:$errline)";
      // break;

    // case E_USER_WARNING:
      // $e = "Warning: [$errno] $errstr ($errfile:$errline)";
      // break;

    // case E_USER_NOTICE:
      // $e = "Notice: [$errno] $errstr ($errfile:$errline)";
      // break;

    // default:
      // $e = "[$errno] $errstr ($errfile:$errline)";
      // break;
  // }

  // die($e);
// }


?>