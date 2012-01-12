<?php
function nl2p($string, $line_breaks = true) {

  $string = str_replace(array('<p>', '</p>', '<br>', '<br />'), '', $string);

  // It is conceivable that people might still want single line-breaks
  // without breaking into a new paragraph.
  if (!$line_breaks)
    return '<p>'.preg_replace(array("/([\r\n]{2,})/i", "/([^>])\r\n([^<])/i"), array("</p>\r\n<p>", '$1<br />$2'), trim($string)).'</p>';
  else 
    return '<p>'.preg_replace(
    array("/([\r\n]{2,})/i", "/([\r\n]{3,})/i","/([^>])\r\n([^<])/i"),
    array("</p>\r\n<p>", "</p>\r\n<p>", '$1<br />$2'),
    trim($string)).'</p>'; 
}

function str_replace_limit($search, $replace, $string, $limit = 1) {
  if (is_bool($pos = (strpos($string, $search))))
    return $string;
  
  $search_len = strlen($search);
  
  for ($i = 0; $i < $limit; $i++) {
    $string = substr_replace($string, $replace, $pos, $search_len);
    
    if (is_bool($pos = (strpos($string, $search))))
      break;
  }
  return $string;
}

function h($str) {
  return htmlspecialchars($str);
}

function u($str) {
  return urlencode($str);
}

# Cleans up a string, translating chars, removing non \w ones and converting whitespaces to hyphens.
function url_friendly_str($str) {
  return trim(preg_replace(array('~[^\w-]~', '~-{2,}~'), array('', '-'), str_replace(array(' ', '--'), '-', translate_chars(strtolower($str)))), ' -_');
}

function translate_chars($string) {
  return strtr($string,
  "ÀÁÂÃÄÅáâãäåÒÓÔÕÖØòóôõöøÈÉÊËèéêëÇçÌÍÎÏìíîïÙÚÛÜùúûüÿÑñ",
  "AAAAAAaaaaaOOOOOOooooooEEEEeeeeCcIIIIiiiiUUUUuuuuyNn"
  );
}

function bytes_to_human($bytes){ 
	$size = $bytes / 1024; 
	if($size < 1024){ 
		$size = number_format($size, 1); 
		$size .= ' KB'; 
	} else { 
		if($size / 1024 < 1024){ 
				$size = number_format($size / 1024, 1); 
				$size .= ' MB'; 
		} else if ($size / 1024 / 1024 < 1024) { 
				$size = number_format($size / 1024 / 1024, 1); 
				$size .= ' GB'; 
		}  
	} 
	return $size; 
}

function to_json($arr) {
  $parts = array();
  $is_list = true;
  
  if (is_object($arr)) {
    $arr = obj2array($arr);
  } 
  
  $i = 0;
  foreach ( $arr as $key => $value ){
    if (!is_int($key) || $key != $i){
      $is_list = false;
      break;
    }
    $i++;
  }
  
  foreach($arr as $key => $value) {
    if(is_object($value)) {
      if(!empty_obj($value))
        $parts[] = to_json((array)$value);
      else
        $parts[] = "\"$key\":".to_json(array());
    } elseif(is_array($value)) {
      $parts[] = $is_list ? to_json($value) : '"' . $key . '":' . to_json($value);
      
    } else {
      
      $str = !$is_list ? '"' . $key . '":' : '';

      if (is_int($value))
        $str .= (int)$value;
      elseif ($value === true)
        $str .= "true";
      elseif ($value === false)
        $str .= "false";
      elseif ($value === null)
        $str .= "null";
      elseif (is_string($value)) {
        $patt = array("\\", /*'/',*/ '"', "\b", "\t", "\n", "\f", "\r", "\u");
        $repl = array("\\\\", /*"\\/",*/ "\\".'"', "\\b", "\\t", "\\n", "\\f", "\\r", "\\u");
        $value = str_replace($patt, $repl, $value);
        $str .= '"' . $value . '"';
      }
      
      $parts[] = $str;
    }
  } 
  $json = implode(',', $parts); 
  $json = utf8_encode($json);
  
  return $is_list ? '[' . $json . ']' : '{' . $json . '}';
  // return $is_list && !empty($json) ? '[' . $json . ']' : '{' . $json . '}';
}

function to_xml($arr, $root = null, $opts = array()) {
  if (is_object($arr) && !$root)
    $root = strtolower(get_class($arr));
  
  $xml = '';
  
  if (empty($opts['skip_instruct']))
    $xml .= '<?xml version="1.0" encoding="UTF-8"?>';
  
  $xml .= "<$root ";
  
  $attrs = array();
  
  foreach ($arr as $k => $v) {
    if (!is_scalar($v))
      continue;
    elseif (is_bool($v))
      $v = $v ? 'true' : 'false';
    
    $attrs[] = $k . '="' . $v . '"';
  }
  
  $xml .= implode(' ', $attrs);
  $xml .= " />";
  
  return $xml;
}

function json_error_handler($errno, $errstr, $errfile, $errline){
  if (!(error_reporting() & $errno)) {
    return;
  }
  header('Content-type: application/json');
  header('HTTP/1.1 500 Internal Server Error');
  // # TODO: hardcoded system's folder name.
  $errfile = addslashes(str_replace(array('\\', ROOT), array('/', ''), $errfile));
  // $errfile = addslashes(preg_replace('~^(.*)\/myimouto~', '', str_replace('\\', '/', $errfile)));
  
  switch ($errno) {
      
    case E_USER_ERROR:
      $e = "Fatal: [$errno] $errstr<br />($errfile:$errline)";
      break;

    case E_USER_WARNING:
      $e = "Warning: [$errno] $errstr<br />($errfile:$errline)";
      break;

    case E_USER_NOTICE:
      $e = "Notice: [$errno] $errstr<br />($errfile:$errline)";
      break;

    default:
      $e = "[$errno] $errstr<br />($errfile:$errline)";
      break;
  }

  exit(to_json(array('reason' => $e)));
}
?>