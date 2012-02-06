<?php
class DB {
  static $PDO;
  static $detailed_errors;
  static $error = false;
  
  /**
   * Query Options
   *
   * Options for queries that can be set through DB::query_opts();
   * These options will be used only for the next query.
   *
   * By default, when using a preset function (such as DB::select_value), the fetch_func and
   * fetch_style options are set to fit the request. However, if the options are already set,
   * they won't be overriden by the defaults.
   *
   * fetch_func : The PDOStatement function to retrieve the data with (default is fetchAll).
   * fetch_style: Used by the fetch_func. If set to 'resource', the PDOStatement object is returned.
   *
   * show_query : echoes the query.
   * test       : PDOStatement is created but not executed, plus echoes query.
   * persistent : Prevents Only the show_query option to be cleared, ecchoing all following queries.
   *              To clear it do DB::query_opts(array('persistent', false));
   *              or just unset(DB::query_opts['persistent']);
   */
  static $query_opts = array();
  
  static function connect($host, $db_name, $user, $pw, $charset = 'charset=UTF-8') {
    try {
      self::$PDO = new PDO("mysql:host=$host;dbname=$db_name;$charset", $user, $pw, array(PDO::ATTR_PERSISTENT => true));
    } catch (PDOException $e) {
      die ("Couldn't connect to Database: " . $e->getMessage() . "<br/>");
    }
  }
  
  static function show_query($persistent = false) {
    self::query_opts('show_query');
    if ($persistent)
      self::query_opts('persistent');
  }
  
  static function test() {
    self::query_opts('test');
  }
  
  # Used by preset functions.
  private static function default_query_opts($opts) {
    foreach ($opts as $opt => $val) {
      !self::isset_query_opt($opt) && self::query_opts(array($opt => $val));
    }
  }
  
  static function query_opts($opts) {
    if (!is_array($opts))
      $opts = array($opts);
    
    foreach ($opts as $k => $opt) {
      if (is_int($k))
        self::$query_opts[$opt] = true;
      else
        self::$query_opts[$k] = $opt;
    }
  }
  
  static function clear_query_opts() {
    $persistent = array('show_query');
    
    if (!self::isset_query_opt('persistent')) {
      self::$query_opts = null;
      return;
    }
    
    foreach (array_keys(self::$query_opts) as $opt) {
      if (!in_array($opt, $persistent))
        unset(self::$query_opts[$opt]);
    }
  }
  
  private static function isset_query_opt($opt) {
    return !empty(self::$query_opts[$opt]);
  }
  
  private static function drop_error($src, $on_state) {
    self::$error = true;
    
    if (!System::$conf->system_error_reporting)
      return;
    
    if (self::$detailed_errors) {
      $err = debug_backtrace();
      $at = null;
      
      if (empty($err[2]['file'])) {
        $index = 3;
      } else {
        $index = 2;
      }
      
      if (is_int(strpos($err[$index]['file'], 'active_record.php'))) {
        foreach ($err as $dbug) {
          if (!isset($dbug['file']) || is_int(strpos($dbug['file'], 'active_record.php')) || is_int(strpos($dbug['file'], 'pdo.php')))
            continue;
          
          $at = "['".$dbug['file']."' (".$dbug['line'].")]";
          unset($dbug);
          break;
        }
      }
      if (!$at && !empty($err[$index]['file']))
        $at = "['".$err[$index]['file']."' (".$err[$index]['line'].")]";
      
      $on_state = "(on $on_state)";
      $er = "\vThere was an error $on_state $at";
      
    } else
      $er = "\vThere was an error with an SQL query";
    
    if (is_string($src)) {
      $e = $src;
    } else {
      $e = $src->errorInfo();
      $e = $e[2];
    }
    
    $er .= ": <strong>$e</strong>\v";
    
    echo $er;
  }
  
  private static function parse_func_args($params) {
    $sql = array_shift($params);
    
    # Workaround for queries with named placeholders
    if (self::has_named_placeholders($sql) && !is_int(key($params[0])))
      $params = array_shift($params);
    
    if (!$params)
      $params = array();
    
    return array($sql, $params);
  }
  
  static function parse_multimark(&$sql, &$params) {
    if (is_bool(strpos($sql, '??')))
      return;
    
    $sql = str_replace('??', 'MULTIMARK', $sql);
    $sql = explode('?', $sql);
    
    $parsed_params = array();
    
    foreach ($sql as &$part) {
      
      $current_param = current($params);
      next($params);
      
      if (is_bool(strpos($part, 'MULTIMARK'))) {
        $parsed_params[] = $current_param;
        continue;
      }
      
      if (!is_array($current_param)) {
        throw new Exception('DB::parse_multimark: Parameter passed is not an array.');
      }
      
      $parsed_params = array_merge($parsed_params, $current_param);
      
      $marks = implode(', ', array_fill(0, count($current_param), '?'));
      $part = str_replace_limit('MULTIMARK', $marks, $part);
      
      unset($part);
    }
    
    $params = $parsed_params;
    
    $sql = implode('?', $sql);
  }
  
  static function escape($str) {
    return self::$PDO->quote($str);
  }
  
  private static function has_named_placeholders($sql) {
    # Avoiding to use preg_match as much as possible.
    if (is_int(strpos($sql, ':'))) {
      $has_named_placeholders = false;
      
      $substr = substr($sql, strpos($sql, ':'));
      $strlen = strlen($substr);
      while ($strlen) {
        if (ctype_alpha(substr($substr, strpos($substr, ':') + 1, 1))) {
          $has_named_placeholders = true;
          break;

        } else {
          $substr = substr($substr, strpos($substr, ':') + 1);
          if (is_bool(strpos($substr, ':')))
            break;
          $strlen = strlen($substr);
        }
      }
    } else
      $has_named_placeholders = false;
    
    return $has_named_placeholders;
  }
  
  static function bind_params($sql, $params) {
    if (!$params)
      return $sql;
    
    $marker_question = $marker_name = false;
    
    if (is_int(strpos($sql, '?')))
      $marker_question = true;
    
    $marker_name = self::has_named_placeholders($sql);
    
    if ($marker_question && $marker_name)
      self::drop_error('<strong>DB::bind_params:</strong> Only one type of markers is allowed.', 'prepare');
      // throw new Exception('<strong>DB::bind_params:</strong> Only one type of markers is allowed.');
    
    if ($marker_question) {
      $params = array_values($params);
      
      # Already called on execute_sql
      // self::parse_multimark($sql, $params);
      
      if (substr_count($sql, '?') > count($params))
        self::drop_error('<strong>DB::bind_params:</strong> Invalid parameter number: number of bound variables does not match number of tokens.', 'prepare');
        // throw new Exception('<strong>DB::bind_params:</strong> Invalid parameter number: number of bound variables does not match number of tokens.');
      
      $parts = explode('?', $sql);
      
      $sql = null;
      foreach (range(0, count($parts)-1) as $i) {
        if (isset($params[$i])) {
        
          if (is_array($params[$i]))
            self::drop_error('<strong>DB::bind_params:</strong> Invalid parameter, array given.', 'prepare');
            // throw new Exception('<strong>DB::bind_params:</strong> Invalid parameter, array given.');
        
          $param = $params[$i];
          !is_numeric($param) && $param = "'$param'";
          
        } else
          $param = null;
        
        $sql .= $parts[$i] . $param;
      }
      
      $sql = trim($sql);
      
    } else {
      foreach ($params as $key => $param) {
        if (is_bool(strpos($sql, ":$key"))) {
          self::drop_error('<strong>DB::bind_params:</strong> Keys don\'t match named placeholders.', 'prepare');
          // throw new Exception('<strong>DB::bind_params:</strong> Keys don\'t match named placeholders.');
        }
        
        !is_numeric($param) && $param = "'$param'";
        
        $sql = str_replace(":$key", $param, $sql);
      }
    }
    
    return $sql;
  }
  
  /**
   * 
   * @param array params - Input parameters to bind the $sql with.
   */
  static function execute_sql($sql, $params = array()) {
    self::$error = false;
    
    try {
      self::parse_multimark($sql, $params);
    } catch (Exception $e) {
      self::drop_error($e->getMessage(), 'prepare');
      return false;
    }
    
    $stmt = self::$PDO->prepare($sql);
    if (!$stmt) {
      self::drop_error(self::$PDO, 'prepare');
      return false;
    }
    
    if (self::isset_query_opt('test')) {
      echo self::bind_params($sql, $params)."\v";
      return false;
    
    } elseif (self::isset_query_opt('show_query')) {
      echo self::bind_params($sql, $params)."\v";
      
    }
    
    if (!is_array($params)) {
      self::drop_error('DB::execute_sql: Parameter for execution is not an array', 'execute');
      return false;
    }
    
    # Some check...
    // foreach ($params as &$param) {
      // if (
    // }
    
    // vd($params);
    $stmt->execute($params);
    $e = $stmt->errorInfo();
    
    if ($e[2] !== null) {
      self::drop_error($stmt, 'execute');
      return false;
    }
    
    $fetch_func = !empty(self::$query_opts['fetch_func']) ? self::$query_opts['fetch_func'] : 'fetchAll';
    $fetch_style = !empty(self::$query_opts['fetch_style']) ? self::$query_opts['fetch_style'] : PDO::FETCH_ASSOC;
    
    if (strpos($sql, 'INSERT') === 0)
      $result = self::$PDO->lastInsertId();
    elseif ($fetch_style == 'resource')
      $result = $stmt;
    else
      $result = $stmt->$fetch_func($fetch_style);
    
    // if (!self::isset_query_opt('persistent'))
    self::clear_query_opts();
    
    return $result;
  }
  
  static function select() {
    list($sql, $params) = self::parse_func_args(func_get_args());
    $sql = "SELECT $sql";
    
    return self::execute_sql($sql, $params);
  }
  
  static function select_row() {
    list($sql, $params) = self::parse_func_args(func_get_args());
    $sql = "SELECT $sql";
    
    self::default_query_opts(array('fetch_func' => 'fetch'));
    
    return self::execute_sql($sql, $params);
  }
  
  static function select_value() {
    list($sql, $params) = self::parse_func_args(func_get_args());
    $sql = "SELECT $sql";
    
    self::default_query_opts(array('fetch_func' => 'fetch', 'fetch_style' => PDO::FETCH_NUM));
    
    $result = self::execute_sql($sql, $params);
    return $result[0];
  }
  
  # Use only when retrieving ONE column from multiple rows.
  static function select_values() {
    $results = call_user_func_array('DB::select', func_get_args());
    
    return array_flat($results);
  }
  
  static function update() {
    list($sql, $params) = self::parse_func_args(func_get_args());
    $sql = "UPDATE $sql";
    
    self::default_query_opts(array('fetch_func' => 'rowCount', 'fetch_style' => null));
    
    return self::execute_sql($sql, $params);
  }
  
  static function insert() {
    list($sql, $params) = self::parse_func_args(func_get_args());
    $sql = "INSERT INTO $sql";
    
    self::default_query_opts(array('fetch_func' => 'rowCount', 'fetch_style' => null));
    
    return self::execute_sql($sql, $params);
  }
  
  static function insert_ignore() {
    list($sql, $params) = self::parse_func_args(func_get_args());
    $sql = "INSERT IGNORE INTO $sql";
  
    self::default_query_opts(array('fetch_func' => 'rowCount', 'fetch_style' => null));
    
    return self::execute_sql($sql, $params);
  }
  
  static function delete() {
    list($sql, $params) = self::parse_func_args(func_get_args());
    $sql = "DELETE FROM $sql";
    
    self::default_query_opts(array('fetch_func' => 'rowCount', 'fetch_style' => null));
    
    return self::execute_sql($sql, $params);
  }
  
  static function count() {
    $args = func_get_args();
    $args[0] = 'COUNT(*) FROM ' . $args[0];
    return call_user_func_array('self::select_value', $args);
  }
  
  static function exists() {
    if (call_user_func_array('self::count', func_get_args()))
      return true;
    else
      return false;
  }
  
  static function found_rows() {
    return self::select_value('FOUND_ROWS()');
  }
}
?>