<?php
function do_content_for($name){
  ActionView::$current_content_for[] = $name;
  
  !isset(ActionView::$content_for[$name]) && ActionView::$content_for[$name] = null;
  
  ob_start();
}

function end_content_for($prefix = false) {
  $current = array_pop(ActionView::$current_content_for);
  
  if ($prefix)
    ActionView::$content_for[$current] = ob_get_clean() . ActionView::$content_for[$current];
  else
    ActionView::$content_for[$current] .= ob_get_clean();
}

# Added the conditional before printing \r\n, specifically for XML,
# to avoid the "XML declaration not at beginning of document" error.
function content_for($name) {
  if (Request::$format == 'html')
    echo "\r\n";
  
  if (!empty(ActionView::$content_for[$name]))
    echo ActionView::$content_for[$name];
}

function check_content_for($name) {
  return !empty(ActionView::$content_for[$name]);
}

function empty_content_for($name) {
  unset (ActionView::$content_for[$name]);
}

/**
 * Sets the page title.
 */
function set_title($title = CONFIG::app_name) {
  ActionView::$page_title = $title;
}

/**
 * Returns the title.
 */
function page_title() {
  return ActionView::$page_title;
}

function render() {
  $data = func_get_args();
  call_user_func_array('ActionView::render', $data);
}

function render_partial($part, $locals = array()) {
  check_array($locals);
  # Will automatically make global vars with names of
  # the active controller and part.
  $locals[] = $part;
  $locals[] = Request::$controller;
  
  foreach($locals as $key => $var) {
    if (is_int($key))
      global $$var;
    else {
      global $$key;
      $$key = $var;
    }
  }
  
  ob_start();
  
  if (is_int($pos = strpos($part, '/'))) {
    preg_match('~\/([^\/]+)$~', $part, $m);
    $part = preg_replace('~\/([^\/]+)$~', '/_\1.php', $part);
    $file = VIEWPATH . $part;
  } else
    $file = VIEWPATH . Request::$controller . '/_' . $part . '.php';
  
  if (false === (include $file)) {
    echo "Partial ($part) could not be found.";
  }
  
  echo ob_get_clean();
}

function exit_with_status($status, $message = null) {
  ActionView::set_http_status($status);
  echo $message;
  exit;
}

function layout($layout) {
  ActionView::$layout = $layout;
}

function isset_layout() {
  return isset(ActionView::$layout);
}

function redirect_to($url, $url_params = array(), $redirect_params = array()) {
  ActionView::redirect_to($url, $url_params, $redirect_params);
  // $args = func_get_args();
  // call_user_func_array('ActionView::redirect_to', $args);
}
?>