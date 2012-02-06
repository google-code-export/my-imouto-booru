<?php
# Static class to store user request data (uri vars, controller, action, etc).
class Request {
  /* About system data and routes variables */
  static $controller = null;
  static $action = null;
  static $format = null; //.html, .json, .xml, etc
  
  static $url = null; // url with cut-off GET params.
  static $abs_url = null; // complete url including GET params.
  static $remote_ip = null;
  
  /* About request: method and parameters */
  static $method = null; //request method, POST or GET
  static $post = false; //if request method is POST, this will be true, this and $get are just quick ways to know request method.
  static $get = false; //if request method is GET, this will be true
  static $params = array(); // stdClass that will be filled with all GET, POST and routes.php parameters
  static $get_params = array(); // = &$_GET
  static $post_params = array(); // = &$_POST
  
  /**
   * Check required parameters
   *
   * Checks if a parameter is missing in the $params object
   * and exits with a 400 status if it is missing.
   *
   * This is useful in post methods, if the user tries to post with empty
   * parameters.
   * 
   * Example: required_params(array('id', 'pool' => 'name'), 'only', 'post', 'put');
   * This will check for $params->id and for $params->pool->name.
   *
   * Created to be used at the first lines of an action file.
   */
  static function required_params($args) {
    $params = array_shift($args);
    check_array($params);
    
    if (!empty($args)) {
      $type = array_shift($args);
      $methods = $args;
      unset($args);
      if (
          (($type == 'only' && in_array(strtolower(self::$method), $methods))
           || ($type == 'except' && !in_array(strtolower(self::$method), $methods)))
          && !self::check_required_params($params)
         )
      {
        exit_with_status(400);
      }
    } else {
      if (!self::check_required_params($params))
        exit_with_status(400);
    }
  }
  
  # required_params(array('id' => array('ctype_digit'), 'commit'), 'on', 'create');
  # required_params(array('user[id]' => array('ctype_digit'), 'commit'), 'on', 'create');
  private static function check_required_params($params) {
    foreach ($params as $param => $validation) {
      if (is_int($param)) {
        $param = $validation;
        unset($validation);
      }
      
      if (is_int(strpos($param, '[')))
        $param = '['.str_replace_limit('[', '][', $param);
      else
        $param = "[$param]";
      
      $data = isset_array($param, Request::$params);
      
      if (!empty($validation)) {
        if (!validate_data($data, $validation))
          return false;
      } elseif ($data === null) {
        return false;
      }
    }
    return true;
  }
  
  static function parse_request() {
    self::$params = new stdClass;
    
    # Get method
    self::$method = &$_SERVER['REQUEST_METHOD'];
    self::$remote_ip = &$_SERVER['REMOTE_ADDR'];
    
    if (self::$method === 'POST')
      self::$post = true;
    elseif (self::$method === 'GET')
      self::$get = true;
    
    self::$abs_url = &$_SERVER['REQUEST_URI'];
    self::$url = preg_replace('~\?.*~', '', $_SERVER['REQUEST_URI']);
    
    if (!System::$conf->php_parses_routes) {
      if (empty($_GET['URLtoken'])) {
        exit_with_status(404);
        die('Impossible to find route. Bad htaccess config?');
      }
        
      # $_GET is filled with parameters from htaccess.
      # Parse them accordingly and fill $_GET with url parameters.
      list($_GET['controller'], $_GET['action']) = explode('@', $_GET['URLtoken']);
      empty($_GET['controller']) && die_404();
      empty($_GET['action']) && $_GET['action'] = 'index';
      
      unset($_GET['URLtoken']);
      
      foreach ($_GET as $param => $value) {
        if (!property_exists('Request', $param))
          continue;
        
        self::$$param = $value;
        unset($_GET[$param]);
      }
      empty(self::$format) && self::$format = 'html';
      
      # Parse GET params from $abs_url
      if (!is_bool(strpos(self::$abs_url, '?'))) {
        $get_params = urldecode(substr(self::$abs_url, strpos(self::$abs_url, '?') + 1));
        
        $get_params = explode('&', $get_params);
        
        foreach ($get_params as $gp) {
          $param = explode('=', $gp);
          
          if (empty($param[0]) || empty($param[1]))
            continue;
          
          $_GET[$param[0]] = $param[1];
        }
      }
    }
    
    # Get post/get parameters
    add_props(self::$params, $_GET, false);
    add_props(self::$params, $_POST, false);
    
    self::$get_params = &$_GET;
    self::$post_params = &$_POST;
  }
}
?>