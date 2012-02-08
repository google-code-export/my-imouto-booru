<?php
class ActionView {

  static $layout = null;
  static $page_title = null;
  
  static $content_for = array();
  static $current_content_for = array();
  
  static $params = array();
  // static $render_args = array();
  # Created to be able to select which file to render in cases like /post/show.php
  static $render = null;
  
  static function render($type, $value = null, $params = array()) {
    if ($type === false) {
      self::$params['nothing'] = true;
      return;
    }
    
    if (is_int(strpos($type, '#'))) {
      /**
       * We're rendering a controller/action file.
       * In this case, $value holds the params, and we only change
       * the 'render' value and return. We can't call the
       * render file within this or any function because of variables scope.
       * 
       * This is expected to occur in a controller, therefore in the controller one must
       * also return; after calling this function, so further code won't be executed.
       */
    
      list($ctrl, $act) = explode('#', parse_url_token($type));
      self::parse_parameters($value);
      self::$render = VIEWPATH."$ctrl/$act.php";
      return;
    }
    
    # Running after-filters.
    ActionController::run_after_filters();
    
    self::parse_parameters($params);
    
    if ($type == 'json') {
      header('Content-Type: application/json; charset=utf-8');
      if (is_array($value))
        $value = to_json($value);
      echo $value;
      exit;
      
    } elseif ($type == 'xml') {
      header('Content-type: application/rss+xml; charset=UTF-8');
      
      if (is_array($value) || is_object($value)) {
        $root = isset($params['root']) ? $params['root'] : 'response';
        $value = to_xml($value, $root);
      }
      
      echo $value;
      exit;
      
    } elseif ($type == 'inline') {
      self::$params['render_type'] = 'inline';
      self::$params['render_value'] = $value;
      include SYSROOT . 'action_view/render_markup_default.php';
    }
  }
  
  static function redirect_to($url, $url_params = array(), $redirect_params = array()) {
    # Running after-filters!
    ActionController::run_after_filters();
    
    if ($redirect_params)
      self::parse_parameters($redirect_params);
    
    $route = url_for($url, $url_params);
    
    header("Location: $route");
    exit;
  }
  
  private static function parse_parameters($params) {
    if (!$params)
      return;
    
    if (isset($params['status']))
      self::set_http_status($params['status']);
    if (isset($params['layout']))
      layout($params['layout']);
  }
  
  static function set_http_status($status) {
    if (is_int($status)) {
      $status_var = 'status_'.substr((string)$status, 0, 1).'xx';
      global $$status_var;
      $status_var = $$status_var;
      $status = $status.' '.$status_var[$status];
    }
    
    $status = 'HTTP/1.1 '.$status;
    
    header($status);
  }
  
  static function exit_with_status($status, $message) {
    self::set_http_status($status);
    
    include SYSROOT . 'config/exit_functions.php';
    
    if (function_exists('exit_' . $status))
      call_user_func('exit_' . $status);
    else
      echo $message;
    
    exit;
  }
}
?>