<?php
class DB {
  static $detailed_errors;
  static $error = false;
  
  /**
   * Query Options
   *
   * Options for queries that can be set through DB::query_opts();
   * These options will be used only for the next query.
   *
   * By default, when using a preset function (such as DB::select_value), the fetch_style
   * option is set to fit the request. However, if the option is already set, it won't be
   * overriden by the default one.
   *
   * fetch_style: all (default) - returns all rows found.
   *              row - returns the first row found.
   *              value - returns a single value.
   *              row_count - returns mysql_affected_rows().
   *              resource - returns the mysql object created by mysql_query()
   *
   * show_query : echoes the query.
   * test       : PDOStatement is created but not executed, plus echoes query.
   * persistent : Prevents Only the show_query option to be cleared, ecchoing all following queries.
   *              To clear it do DB::query_opts(array('persistent', false));
   *              or just unset(DB::query_opts['persistent']);
   */
  static private $query_opts = array();
  
  static function connect($host, $db_name, $user, $pw) {
    mysql_connect($host, $user, $pw);
    
    if (mysql_error())
      die("Couldn't connect to MySQL!");
    
    mysql_select_db($db_name) or die("Couldn't connect to database!");
    mysql_set_charset('charset=UTF-8');
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
  
  private static function drop_error($e, $on_state) {
    self::$error = true;
    
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
          if (!isset($dbug['file']) || is_int(strpos($dbug['file'], 'active_record.php')) || is_int(strpos($dbug['file'], 'mysql.php')))
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
    
    $e && $er .= ": <strong>$e</strong>\v";
    
    echo $er;
  }
  
  private static function parse_func_args($params) {
    $sql = array_shift($params);
    
    # Workaround for queries with named placeholders
    if (is_int(strpos($sql, ':')) && !is_int(key($params[0])))
      $params = array_shift($params);
    
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
    return mysql_real_escape_string($str);
  }
  
  static function bind_params($sql, $params) {
    if (!$params)
      return $sql;
    
    $marker_question = $marker_name = false;
    
    if (is_int(strpos($sql, '?')))
      $marker_question = true;
    
    if (is_int(strpos($sql, ':')))
      $marker_name = true;
    
    if ($marker_question && $marker_name)
      throw new Exception('<strong>DB::bind_params:</strong> Only one type of markers is allowed.');
    
    if ($marker_question) {
      $params = array_values($params);
      
      self::parse_multimark($sql, $params);
      
      if (substr_count($sql, '?') > count($params))
        throw new Exception('<strong>DB::bind_params:</strong> Invalid parameter number: number of bound variables does not match number of tokens.');
      
      $parts = array_filter(explode('?', $sql));
      $sql = '';
      
      foreach (range(0, count($parts)-1) as $i) {
        if ($i <= count($params) - 1) {
        
          if (is_array($params[$i]))
            throw new Exception('<strong>DB::bind_params:</strong> Invalid parameter, array given.');
          
          $param = $params[$i];
          
          if ($param === null)
            $param = 'NULL';
          elseif ($param === false)
            $param = 0;
          elseif ($param === true)
            $param = 1;
          else {
            $param = mysql_real_escape_string($param);
            !is_numeric($param) && $param = "'$param'";
          }
          
          $sql .= $parts[$i] . $param;
          
        } else
          $sql .= $parts[$i];
      }
      
      $sql = trim($sql);
      
    } else {
      foreach ($params as $key => $param) {
        if (is_bool(strpos($sql, ":$key"))) {
          throw new Exception('<strong>DB::bind_params:</strong> Keys don\'t match named placeholders.');
        }
        
        $param = mysql_real_escape_string($param);
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
      $sql = self::bind_params($sql, $params);
    } catch (Exception $e) {
      self::drop_error($e->getMessage(), 'prepare');
      return false;
    }
    
    if (self::isset_query_opt('test')) {
      echo $sql."\v";
      return false;
    
    } elseif (self::isset_query_opt('show_query')) {
      echo $sql."\v";
      
    }
    
    $mysql_res = mysql_query($sql);
    
    if (mysql_error()) {
      self::drop_error(mysql_error(), 'execute');
      return false;
    }
    
    # Fetch style: all, row, value, row_count.
    $fetch_style = !empty(self::$query_opts['fetch_style']) ? self::$query_opts['fetch_style'] : 'all';
    
    if (strpos($sql, 'INSERT') === 0) {
      $result = mysql_insert_id();
    
    } elseif ($fetch_style === 'row') {
      $result = mysql_fetch_assoc($mysql_res);
    } elseif ($fetch_style === 'value') {
      $result = mysql_fetch_assoc($mysql_res);
      if ($result)
        $result = current($result);
    } elseif ($fetch_style === 'row_count') {
      $result = mysql_affected_rows();
    } elseif ($fetch_style === 'resource') {
      $result = $mysql_res;
    
    # 'all' by default.
    } elseif (!is_bool($mysql_res)) {
      while ($fetch = mysql_fetch_assoc($mysql_res))
        $result[] = $fetch;
      empty($result) && $result = array();
    
    } else {
    /**
     * Some queries will return boolean. If they weren't filtered up there,
     * just return the boolean result.
     */
      $result = $mysql_res;
    
    }
    
    // if (self::$query_opts && !self::isset_query_opt('persistent'))
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
    
    self::default_query_opts(array('fetch_style' => 'row'));
    
    return self::execute_sql($sql, $params);
  }
  
  static function select_value() {
    list($sql, $params) = self::parse_func_args(func_get_args());
    $sql = "SELECT $sql";
    
    self::default_query_opts(array('fetch_style' => 'value'));
    
    $result = self::execute_sql($sql, $params);
    return $result;
  }
  
  # Use only when retrieving ONE column from multiple rows.
  static function select_values() {
    $results = call_user_func_array('DB::select', func_get_args());
    
    return array_flat($results);
  }
  
  static function update() {
    list($sql, $params) = self::parse_func_args(func_get_args());
    $sql = "UPDATE $sql";
    
    self::default_query_opts(array('fetch_style' => 'row_count'));
    
    return self::execute_sql($sql, $params);
  }
  
  static function insert() {
    list($sql, $params) = self::parse_func_args(func_get_args());
    $sql = "INSERT INTO $sql";
  
    self::default_query_opts(array('fetch_style' => 'row_count'));
    
    return self::execute_sql($sql, $params);
  }
  
  static function insert_ignore() {
    list($sql, $params) = self::parse_func_args(func_get_args());
    $sql = "INSERT IGNORE INTO $sql";
  
    self::default_query_opts(array('fetch_style' => 'row_count'));
    
    return self::execute_sql($sql, $params);
  }
  
  static function delete() {
    list($sql, $params) = self::parse_func_args(func_get_args());
    $sql = "DELETE FROM $sql";
    
    self::default_query_opts(array('fetch_style' => 'row_count'));
    
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