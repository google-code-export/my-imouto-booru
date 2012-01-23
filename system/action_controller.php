<?php
class ActionController {
  
  static $actions = array();
  static $routes = array();
  static $after_filters = array();
  
  // var $models = array();
  
  static function run_after_filters() {
    if (empty(self::$after_filters))
      return;
    
    
    
    foreach(self::$after_filters as $params) {
      $func = array_shift($params);
      
      call_user_func_array($func, $params);
    }
  }
  
  static function start() {
    Request::parse_request();
    
    try {
      self::routing();
    } catch (Exception $e) {
      // TODO
      // error handling, redirect to 404 ?
      echo $e->getMessage();
      exit;
    }
    
    
    if (!System::controller_exists())
      die_404();
  }
  
  static function rescue_request() {
    return false;
  }
  
  static function load_controller() {
    if (false === (include CTRLSPATH . Request::$controller . '/_controller.php'))
      die('Could not load controller file!');
    
    $modelname = strtolower(ApplicationModel::filename_to_modelname(Request::$controller));
    
    if (Application::$models->exists($modelname))
      Application::$models->{$modelname}->load();
  }
  
  static function action_exists() {
    foreach (self::$actions as $k => $v) {
      if (!is_int($k) && Request::$action == $k) {
        Request::$action = $v;
        return true;
      } elseif (is_int($k) && Request::$action == $v)
        return true;
    }
  }
  
  static function routing() {
    if (!SYSCONFIG::php_parses_routes) {
      return;
    }
    
    $url_route = preg_replace('~^\/~', '', Request::$url);
    
    $i = 0;
    
    foreach (self::$routes as $rkey => $route) {
      Request::$action = Request::$controller = $requirements = null;
      
      if (!is_numeric($rkey)) {
        if (strstr($rkey, '#'))
          list(Request::$controller, Request::$action) = explode('#', $rkey);
        else
          Request::$controller = $rkey;
      }
      
      
      if (is_array($route)) {
        if (array_key_exists('requirements', $route))
          $requirements = $route['requirements'];
        
        $route = array_shift($route);
      }
      
      if((empty(Request::$controller) && !strstr($route, '$controller')) ||
          (empty(Request::$action) && !strstr($route, '$action')))
        throw new Exception('Insufficient arguments in routes file.');
      
      $patt = array('/', '.', '*');
      $repl = array('\/', '\.', '.+');
      $route = str_replace($patt, $repl, $route);
      
      if ($route == '$root')
        $route = '';
      
      # Parse variables.
      if (strstr($route, '$')) {
        preg_match_all('/\$([\w]+)/', $route, $vars);
        $vars = $vars[1];
        if(!empty($requirements)) {
          foreach($vars as $var) {
            if($var != 'controller' && $var != 'action' && !array_key_exists($var, $requirements)) {
              $route = str_replace('$'.$var, '([^\/]+)?', $route);
            }
          }
          unset($var);
        }
        $route = preg_replace('/\$[\w]+/', '([^\/]+)', $route);
      }
      
      $route = "/^$route$/";
      
      if(!preg_match($route, $url_route, $match)) {
        if($i == count(self::$routes) - 1) {
          # Find default /controller/action
          preg_match("/^([\w]+)(\/([\w]+))?/", $url_route, $match);
        } else {
          $i++;
          continue;
        }
      }
      
      if (!empty($vars)) {
        array_shift($match);
        $c = 0;
        foreach ($vars as $var) {
          if($c == count($match))
            continue;
          if ($var == 'controller') {
            Request::$controller = $match[$c];
            $c++;
            continue;
          } elseif ($var == 'action') {
            Request::$action = $match[$c];
            $c++;
            continue;
          } elseif ($var == 'format') {
            Request::$format = $match[$c];
            $c++;
            continue;
          }
          Request::$params->$var = $match[$c];
          $c++;
        }
      }
      
      if (isset($requirements)) {
        foreach($requirements as $var => $req_patt) {
          if (!isset(Request::$params->$var))
            throw new Exception("Unknown match $var in routes file.");
          
          $req_patt = "~^$req_patt$~";
          if (!preg_match($req_patt, Request::$params->$var)) {
            $i++;
            continue 2;
          }
        }
      }
      
      if (empty(Request::$action))
        Request::$action = 'index';
      
      if (empty(Request::$format)) {
        if(preg_match('~\.([\w]+)$~', $url_route, $m))
          Request::$format = $m[1];
        else
          Request::$format = 'html';
      }
      break;
    }
  }
}
?>