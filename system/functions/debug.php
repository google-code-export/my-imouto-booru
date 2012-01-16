<?php
function script_time($end = false){
  static $starttime;
  
  $mtime = microtime(); 
  $mtime = explode(" ",$mtime); 
  $mtime = $mtime[1] + $mtime[0]; 
  
  if (!$end) {
    $starttime = $mtime;
    
  } else {
    $endtime = $mtime; 
    $totaltime = ($endtime - $starttime); 
    return $totaltime;
  }
}

function vd() {
  $vars = func_get_args();
  call_user_func_array('var_dump', $vars);
}

// function vdes() {
  // $vars = func_get_args();
  // call_user_func_array('var_dump', $vars);
  // echo "<br />\r\n";
  // $backtrace = implode("<br />\r\n", get_stacktrace(debug_backtrace()));
  // echo $backtrace;
  // exit;
// }

function vde() {
  $vars = func_get_args();
  call_user_func_array('var_dump', $vars);
  exit;
}

function vdob(&$v, $replace_n = false) {
  ob_start();
  vd($v);
  if ($replace_n)
    return str_replace("\n", '<br />', ob_get_clean());
  else
    return ob_get_clean();
}

function memusage() {
  echo 'Memory usage: '.number_to_human_size(memory_get_usage());
}

# Experimental?
function system_error_reporting($errno, $errstr, $errfile, $errline){
  if (!(error_reporting() &$errno))
    return;
  
  $backtrace = debug_backtrace();
  
  $function = $backtrace[1]['function'];
  $class = !empty($backtrace[1]['class']) ? $backtrace[1]['class'] . '::' : null;
  
  $is_sysfile = true;
  $i = 1;
  $called_by = $stacktrace = array();
  $filename = '';
  
  $stacktrace = get_stacktrace($backtrace);
  
  $stacktrace = implode("<br />\r\n", $stacktrace);
  
  $errfile = '/' . str_replace(ROOT, '', str_replace('\\', '/', $errfile));
  
  switch ($errno) {
    case E_USER_ERROR:
      $e = "Fatal: [$errno] $errstr ($errfile:$errline)";
      break;

    case E_USER_WARNING:
      $e = "Warning: [$errno] $errstr ($errfile:$errline)";
      break;

    case E_USER_NOTICE:
      $e = "Notice: [$errno] $errstr ($errfile:$errline)";
      break;

    default:
      $e = "[ErrNo $errno] $errstr ($errfile:$errline)";
      break;
  }
  
  echo $e . "<br />\r\n";
  echo $stacktrace;
  
  if (SYSCONFIG::die_at_error)
    exit;
}

function get_stacktrace($backtrace) {
  $stacktrace = array();
  
  foreach (range(1, count($backtrace)) as $i) {
    if (!isset($backtrace[$i]['file']))
      continue;
    $filename = '/' . str_replace(ROOT, '', str_replace('\\', '/', $backtrace[$i]['file']));
    
    if (is_int(strpos($filename, '/system/initializer.php')))
      break;
    
    $class = !empty($backtrace[$i]['class']) ? $backtrace[$i]['class'] . '::' : null;
    $function = $class . $backtrace[$i]['function'];
    $line = isset($backtrace[$i]['line']) ? ', line <strong>' . $backtrace[$i]['line'] . '</strong>' : null;
    $stacktrace[] = "[$function], called in <strong>$filename</strong>$line";
  }
  
  return $stacktrace;
}
?>