<?php
class ActiveRecordException extends Exception {}

/**
 * Active Record base
 *
 * This class can't have any property as they will interfere with Collecion and Models.
 * So any property regarding this class (and others) will be stored in
 * the System class.
 */
class ActiveRecord {
  # Allowing data to be null to create empty models.
  final function __construct($data = null, $params = array()) {
    
    // if (property_exists($this, 'is_collection')) {
    if ($this->cn() == 'collection') {
      $model_name = $params['model_name'];
      unset($params['model_name']);
      
      $this->model_name = $model_name;
    }
    
    if (!$data) {
      if ($this->cn() == 'collection')
        $this->call_custom_construct($data);
        
      return false;
    }
    
    if ($this->cn() != 'collection') {
      $this->set_model_current_data($data);
      $this->add_attributes($data);
    }
    
    if (empty($params['prevent_construct']))
      $this->call_custom_construct($data);
  }
  
  /**
   * Add/change attributes to model
   *
   * Filters protected attributes of the model.
   * Also calls the "on_attribute_change()" method, if exists, of the model,
   * in case extra operation is needed when changing certain attribute.
   * It's intended to be an equivalent to " def attribute=(val) " in rails.
   * E.g. "is_held" for post model.
   */
  final function add_attributes($attrs) {
    if (!is_array($attrs))
      return false;
    
    foreach ($attrs as $attr => $v) {
      if (in_array($attr, ApplicationModel::$protected_props) || (self::model_data() && self::model_data()->assoc_exists($attr)))
        continue;
      
      $on_change_method = 'on_' . $attr . '_change';
      if (method_exists($this, $on_change_method))
        $this->$on_change_method($v);
      else
        $this->$attr = $v;
    }
    return true;
  }
  
  # TODO: incomplete function.
  private function validate_data($data = null, $action) {
    # TODO: $data could be removed and be always $this.
    if (!$data)
      $data = $this;
    else
      $data = array2obj($data);
    
    $this->run_callbacks('before_validation');
    $error = false;
    
    if (self::model_data()->has_validations()) {
    
      foreach (self::model_data()->get_validations() as $model_property => $model_validations) {
        
        foreach ($model_validations as $prop => $opts) {
          check_array($opts);

          # Check action
          if (isset($opts['on'])) {
            if ($action != $opts['on'])
              continue;
            unset($opts['on']);
          }
          # Check conditionals
          if (isset($opts['if'])) {
            foreach ($opts['if'] as $condition => $condition_param) {
              switch ($condition) {
                case 'property_exists':
                  if (empty($this->$condition_param))
                    continue 3;
                break;
              }
            }
            unset($opts['if']);
          }
          
          if ($data->$model_property === null) {
            if (empty($opts['accept_null'])) {
              # Some validations, like confirmation, should only activate if the model property is present.
              # So we're skipping it here.
              if ($prop == 'confirmation')
                continue;
              return false;
            }
             
            unset($opts['accept_null']);
            continue;
          }
          
          // # Check message
          // if (isset($opts['message'])) {
            // $message = $opts['message'];
            // unset($opts['message']);
          // } else
            // $message = null;
          
          switch ($prop) {
          
            // case ($param == 'acceptance' && $opts):
              // if (empty($data->$prop)) {
                // break 4;
              // }
            // break;
          
            case 'confirmation':
              $prop_confirm = $model_property . '_confirmation';
              
              if ($this->$prop_confirm === null)
                continue;
              
              !isset($opts['message']) && $opts['message'] = 'doesn\'t match confirmation.';
              
              $validation_result = $this->$model_property === $this->$prop_confirm;
            break;
            
            case 'presence':
              !isset($opts['message']) && $opts['message'] = 'cannot be empty.';
              $validation_result = property_exists($data, $model_property);
            break;
            
            case 'uniqueness':
              if ($action != 'create') {
                $validation_result = true;
                continue;
              }
              !isset($opts['message']) && $opts['message'] = 'must be unique.';
              $validation_result = $this->check_data_uniqueness($model_property, $data->$model_property);
            break;
            
            case 'exclusion':
              $validation_result = validate_data($data->$model_property, array('in' => $opts), true);
              if ($validation_result === true) {
                $validation_result = isset($opts['message']) ? $opts['message'] : 'is not valid.';
              } else
                $validation_result = true;
            break;
            
            case 'inclusion':
              $validation_result = validate_data($data->$model_property, array('in' => $opts), true);
            break;
            
            default:
              $validation_result = validate_data($data->$model_property, array($prop => $opts), true);
            break;
          }
          
          if ($validation_result !== true) {
            if (isset($opts['message'])) {
              $message = $opts['message'];
            } else {
              if (!is_string($validation_result)) {
                $message = 'is not valid';
              } else
                $message = $validation_result;
            }
            
            $this->record_errors->add($model_property, $message);
            
            $error = true;
          }
        }
      }
    }
    
    if ($error) {
      return false;
    }
    
    $this->run_callbacks('after_validation');
    
    return true;
  }

  
  # In case the model has no keycolumns, we need a way to find it if saving it.
  private function set_model_current_data($data) {
    if (!self::model_data() || self::model_data()->table->get_indexes())
      return;
    
    $this->current_model_data = array();
    
    foreach ($data as $key => $val) {
      $this->current_model_data[$key] = $val;
    }
  }
  
  function attributes() {
    $attrs = (array)$this;
    foreach (ApplicationModel::$protected_props as $p) {
      unset($attrs[$p]);
    }
    
    return $attrs;
  }
  
  protected function load_association($prop) {
    # Load model in case it's not been loaded.
    self::model_data()->load_assoc_model($prop);
    
    $assoc_function = self::model_data()->assocs->$prop->type . '_do';
    $this->$assoc_function($prop);
    return $this->$prop;
  }
  
  # Allowing $data = null for create()
  # TODO: if data is null and $this->is_collection, throw an error.
  private function call_custom_construct($data = null) {
    if (!method_exists($this, '_construct'))
      return;
    
    # Passing data to _construct is needed for Collection.
    if ($this->cn() == 'collection')
      $this->_construct($data);
    else
      $this->_construct();
  }
  
  final function __call($func, $args) {
    switch ($func) {
      # To check if an attribute has changed upon save();
      case (is_int(strpos($func, '_changed')) && (strlen($func) - strpos($func, '_changed')) == 8) :
        $attribute = str_replace('_changed', '', $func);
        return array_key_exists($attribute, $this->changed_attributes);
      break;
      
      # To check what an attribute was before save() or update_attributes();
      case ((strlen($func) - strpos($func, '_was')) == 4) :
        $attribute = str_replace('_was', '', $func);
        
        if (!array_key_exists($attribute, $this->changed_attributes))
          return;
        
        return $this->changed_attributes[$attribute];
      break;
    }
    
    if (method_exists($this, '_call'))
      return $this->_call($func, $args);
  }
  
  final function __get($prop) {
    if (get_class($this) == 'Collection')
      return;
    
    if (self::model_data() && self::model_data()->assoc_exists($prop)) {
      return $this->load_association($prop);
    }
    
    if ($prop == 'record_errors') {
      $this->record_errors = new RecordErrors;
      return $this->record_errors;
    }
    
    if (method_exists($this, 'set_' . $prop)) {
      $this->$prop = $this->{'set_' . $prop}();
      return $this->$prop;
    }
    
    if (method_exists($this, '_get'))
      return $this->_get($prop);
  }
  
  static function blank($data = array()) {
    $cn = self::cn();
    $model = new $cn;
    
    $model->empty_model = true;
    
    if (!$model->add_attributes($data))
      return false;
    
    return $model;
  }
  
  static function create_from_array($data) {
    if (!is_array($data))
      return false;
    
    $model = self::get_empty_model();
    
    $model->add_attributes($data);
    
    // foreach ($data as $k => $v)
      // $model->$k = $v;
    return $model;
  }
  
  static private function get_empty_model() {
    $mn = self::cn(false);
    return new $mn;
  }
  
  /**
   * Table name
   *
   * @return string: Name of the table for the model.
   */
  static function t() {
    return self::model_data()->table_name;
  }
  
  /**
   * Get class name.
   *
   * Returns the name of the class.
   */
  static function cn($lower = true) {
    return $lower ? strtolower(get_called_class()) : get_called_class();;
  }
    
  final static function __callStatic($func, $args) {
    // if ($func == 'find_by_post_id_and_user_id')
      // vd('aa');
    switch ($func) {
      /**
       * find_name_by_id
       *
       * 'by_' would be used as "conditions" param for SQL creation. If 'by_' is specified in the
       * function name and it's larger than 1, then:
       *  - the values for the conditions can be passed as find_by_user_id_and_name(array($user_id, $name))
       *  - the 'conditions' array in the options must be filled with the values for the conditions,
       *    e.g. find_by_name_and_created_at(array('conditions' => array($name, $created_at)));
       * if it's just one condition, a single value may be passed: find_by_name($name);
       *
       * When doing find_name, a single value will be returned.
       * When doing find_name_and_id, a row will be returned.
       * To return all the rows found, do find_name('all');
       */
      case is_int(strpos($func, 'find_')):
        $func = substr($func, 5);
        
        $params = $user_params = array();
        $select_type = null;
        
        if (strpos($func, 'first') === 0) {
          $select_type = 'first';
          $func = substr($func, 5);
        } elseif (strpos($func, 'last') === 0) {
          $select_type = 'last';
          $func = substr($func, 4);
        } elseif (strpos($func, 'all') === 0) {
          $select_type = 'all';
          $func = substr($func, 3);
        }
        
        strpos($func, '_') === 0 && $func = substr($func, 1);
        
        if (isset($args[0]) && ($args[0] == 'first' || $args[0] == 'last' || $args[0] == 'all'))
          $select_type = array_shift($args);
        elseif (!$select_type)
          $select_type = 'first';
        
        if (is_indexed_arr($args))
          $user_params = array_shift($args);
        
        check_array($user_params);
        
        if (is_int(strpos($func, 'by_'))) {
          $by = substr($func, strpos($func, 'by_'));
          $func = str_replace($by, '', $func);
          $by = explode('_and_', substr($by, 3));
          foreach ($by as &$b)
            $b .= ' = ?';
          $by = implode(' AND ', $by);
          
          $params['conditions'] = array_merge(array($by), $user_params);
        }
        
        if ($func) {
          $func = trim($func, '_');
          if (!substr_count($func, '_and_'))
            $params['return_value'] = $func;
          
          $params['select'] = str_replace('_and_', ', ', trim($func, '_'));
        }
        
        $params = array_merge($params, $user_params);
        
        $data = self::find($select_type, $params);
        // vd($data);
        return $data;
      break;
    }
    
    if (method_exists(get_called_class(), '_callStatic'))
      return static::_callStatic($func, $args);
  }
  
  #TODO: improve this function
  static protected function sanitize_sql($query, $args) {
    check_array($args);
    if ( !empty($args) ) {
      foreach ($args as $key => $val) {
        if (is_string($key)) {
          $keys[] = "/:$key/";
          $lmt = -1;
        } else {
          $keys[] = '/[?]/';
          $lmt = 1;
        }
        $args[$key] = "'$val'";
      }
      $query = preg_replace($keys, $args, $query, $lmt);
    }
    
    return "$query";
  }
  
  static function maximum($column) {
    return DB::select_value('MAX(' . $column . ') FROM ' . self::t());
  }
  
  static function minimum($column) {
    return DB::select_value('MIN(' . $column . ') FROM ' . self::t());
  }
  
  /**
   * for now it receives parameters as seen on Post::update_has_children()
   */
  static function exists($query, $params = null) {
    if (is_int($query))
      $where = ' ' . self::t() . '.id = '.$query;
    else
      $where = self::sanitize_sql($query, $params);
    
    return DB::exists(self::t().' WHERE '.$where);
  }
  
  /**
   * Check time column
   *
   * Called by save() and create(), checks if time $column
   * exists and automatically sets a value to it.
   */
  private function check_time_column($column, &$d) {
    if (!$this->column_exists($column))
      return;
    
    $type = self::model_data()->table->get_type($column);
    
    if ($type == 'date' || $type == 'datetime' || $type == 'year' || $type == 'timestamp')
      $d[] = $time = gmd();
    elseif ($type == 'time')
      $d[] = $time = gmd('H:i:s');
    
    else
      return;
    
    $this->$column = $time;
    
    return true;
  }
  
  private function check_data_uniqueness($prop, $prop_value) {
    $where = array("`$prop` = :prop");
    
    $keys['prop'] = $prop_value;
    
    if (!$this->is_empty_model()) {
      $model_keys = $this->get_model_keydata();
      foreach ($model_keys as $k => $v)
        $where[] = "`$k` != :$k";
      $keys = array_merge($model_keys, $keys);
    }
    
    $where = implode(' AND ', $where);
    $sql = self::t()." WHERE $where";
    
    $result = DB::exists($sql, $keys) ? false : true;
    
    return $result;
  }
  
  static function model_data() {
    if (empty(Application::$models->{self::cn()}))
      return false;
    
    return Application::$models->{self::cn()};
  }
  
  function column_exists($column_name) {
    return self::model_data()->table->exists($column_name);
  }
  
  function is_empty_model() {
    return isset($this->empty_model);
  }
  
  # If no data is passed, it's assumed this method is being called from save
  # to create the current object, otherwise it's a Model class creating a new model.
  final static function create($data) {
    // if ($data) {
    $cn = self::cn();
    // $cn = get_class($this);
    
    if (!$new_model = self::blank($data))
      return false;
    
    if (!$new_model->run_callbacks('before_save'))
      return false;
    
    if ($new_model->create_do())
      $new_model->run_callbacks('after_save');
    
    return $new_model;
    // }
  }
  
  final function create_do() {
    if (!$this->run_callbacks('before_validation_on_create'))
      return false;
    
    if (!$this->validate_data(null, 'create'))
      return false;
    
    $this->run_callbacks('after_validation_on_create');
    
    if (!$this->run_callbacks('before_create'))
      return false;
    
    $values = $values_names = array();
    
    foreach ($this as $d => $v) {
      if (!$this->column_exists($d))
        continue;
      
      $values_names[] = $d;
      $values[] = $v;
    }
    
    if (!$values)
      return false;
    
    if ($this->check_time_column('created_at', $values)) {
      $values_names[] = 'created_at';
      $data['created_at'] = end($values);
    }
    
    if ($this->check_time_column('updated_at', $values)) {
      $values_names[] = 'updated_at';
      $this->updated_at = end($values);
    }
    
    $values_marks = implode(', ', array_fill(0, (count($values_names)), '?'));
    $values_names = implode(', ', $values_names);
    
    $sql = self::t().' ('.$values_names.') VALUES ('.$values_marks.')';
    
    array_unshift($values, $sql);
    
    $id = call_user_func_array('DB::insert', $values);
    
    $primary_key = $this->get_table_indexes('PRI');
    
    if ($primary_key && count($primary_key) == 1) {
      if (!$id) {
        $this->record_errors->add_to_base('There was a MySQL Error - Couldn\'t retrieve new PRI KEY');
        return false;
      }
      
      if ($keycol = $this->get_model_keydata()) {
        $keycol = key($keycol);
        $this->$keycol = $id;
      }
    }
    
    unset($this->empty_model);
    
    # Complete the model's creation by calling its _construct.
    # after_create callbacks may need the complete model.
    $this->call_custom_construct();
    
    $this->run_callbacks('after_create');
    
    return true;
  }
  
  /**
   * Delete
   *
   * Deletes row from database based on Primary keys.
   * $keys could be an array of ids, for mass delete.
   * Properties are also deleted.
   */
  final function delete($keys = array()) {
    if ($keys) {
      check_array($keys);
      // $keys = array_combine(array_fill(0, count($ids), 'id'), $ids);
      
      $w = ' '.self::t().'.`id` IN (??) ';
      
    } else {
      if (!$keys = $this->get_model_keydata())
        return false;
      
      foreach (array_keys($keys) as $k)
        $w[] = self::t().".`$k` = ?";
      
      $w = implode(' AND ', $w);
    }
    
    // DB::delete(array_values($keys));
    call_user_func_array('DB::delete', array_merge(array(self::t().' WHERE '.$w), array_values($keys)));
    
    foreach ($this as $p => $v)
      unset($this->$p);
    
    return true;
  }
  
  # Deletes current model from database but keeps its properties.
  final function destroy() {
    
    $this->run_callbacks('before_destroy');
    
    if (!$keys = $this->get_model_keydata())
      return false;
    
    foreach (array_keys($keys) as $k)
      $w[] = "`$k` = ?";
    
    $w = implode(' AND ', $w);
    
    call_user_func_array('DB::delete', array_merge(array(self::t().' WHERE '.$w), array_values($keys)));
    
    $this->run_callbacks('after_destroy');
    
    return true;
  }
  
  private function get_old_model_data() {
    return $this->current_model_data;
  }
  
  private function get_table_indexes($type = null) {
    return self::model_data()->table->get_indexes($type);
    // if ($type == 'pri')
      // return self::model_data()->table->get_primary_key();
    // elseif ($type == 'id')
      // return self::model_data()->table->get_id_key();
    
    // $keys = self::model_data()->table->get_indexes();
    
    // if (!$type)
    // return $keys;
  }
  
  /**
   * Get model's primary keys
   *
   * Searches in the System::$database_tables variable for
   * all primary keys that the model may have, and returns
   * a column_name => column_value array.
   */
  private function get_model_keydata() {
    $key_cols = $this->get_table_indexes();
    
    if(!$key_cols) {
      return $this->get_old_model_data();
    }
    
    foreach ($key_cols as $k) {
      $data[$k] = isset($this->$k) ? $this->$k : null;
    }
    
    return $data;
  }
  
  private function throw_exception($function, $message) {
    throw new ActiveRecordException("<b>ActiveRecord::$function</b>: $message.");
  }
  
  private function get_current_data() {
    if (!$primary_keys = $this->get_model_keydata())
      $this->throw_exception('get_current_data', 'Current keydata cannot be found for model '.get_class($this));
    
    $conds_sql = array();
    
    foreach (array_keys($primary_keys) as $k)
      $conds_sql[] = "`$k` = ?";
    
    $conds_sql = implode(' AND ', $conds_sql);
    // $find = 'find_by_'.implode('_and_', array_keys($primary_keys));
    // $conds = array_map(function($k) {
      // return 
    // }
    // $params['conditions'] = array(
      // 'user_id = ?'
    // );
    
    // $params = array();
    foreach ($primary_keys as $val) {
      $conds[] = $val;
    }
    
    array_unshift($conds, $conds_sql);
    // vde($conds);
    
    $params['conditions'] = $conds;
    // $params = array('conditions' => array_merge($params);
    
    // if (count($params) > 1)
      // $params = array($params);
    // db::show_query(1);
    // vde($find);
    // vd('start');
    // $current = call_user_func_array(array(get_called_class(), $find), $params);
    $current = self::find($params);
    // vde($current);
    if (empty($current)) {
      $this->throw_exception('get_current_data', 'Current data cannot be found for model '.get_class($this));
    }
    
    return $current;
  }
  
  /**
   * Find current model data.
   *
   * Searches in database, based on PRImary keys, for current
   * stored data.
   */
  private function find_current() {
    $cn = get_class($this);
    $current = $this->get_current_data();
    return $current;
  }
  
  /**
   * Save object
   *
   * Saves in the database the properties of the object that match
   * the columns of the corresponding table in the database.
   *
   * Is the model is new, create() will be called instead.
   *
   * @array $values: If present, object will be updated according
   * to this array, otherwise, according to its properties.
   */
  final function save() {
    if (!$this->validate_data(null, 'save'))
      return false;
    // if (get_class($this) == 'BlogPost')
      // wlog(vdob($this), 1);
    if (!$this->run_callbacks('before_save'))
      return false;
    // if (get_class($this) == 'BlogPost')
      // wlog(vdob($this), 1);
    if ($this->is_empty_model()) {
      if (!$this->create_do())
        return false;
    } else {
      if (!$this->save_do())
        return false;
    }
    // if (get_class($this) == 'BlogPost')
      // wlog(vdob($this), 1);
    $this->run_callbacks('after_save');
    // if (get_class($this) == 'BlogPost')
      // wlog(vdob($this), 1);
    return true;
  }
  
  private final function save_do() {
    $w = $wd = $q = $d = $this->changed_attributes = array();
    
    $dt = self::model_data()->table->get_names();
    
    try {
      $current = $this->find_current();
    } catch (ActiveRecordException $e) {
      echo $e;
      return;
    }
    
    $has_primary_keys = false;
    
    foreach ($this->get_model_keydata() as $k => $v) {
      $w[] = "`$k` = ?";
      $wd[] = $v;
    }
    
    if ($w)
      $has_primary_keys = true;
    
    foreach ($this as $prop => $val) {
      # Can't update properties that don't have a column in DB, or
      # PRImary keys, or created_at column.
      if (!in_array($prop, $dt) || $prop == 'created_at' || $prop == 'updated_at') {
        continue;
        
      } elseif (!$has_primary_keys && $val == $current->$prop) {
      
        $w[] = "`$prop` = ?";
        $wd[] = $current->$prop;
        
      } elseif ($val != $current->$prop) {
        
        $this->changed_attributes[$prop] = $current->$prop;
        $q[] = "`$prop` = ?";
        $d[] = $val;
      }
    }
    
    # Update `updated_at` if exists.
    if ($this->check_time_column('updated_at', $d))
      $q[] = "`updated_at` = ?";
    
    if (!empty($q)) {
      $q = self::t() . " SET " . implode(', ', $q);
      $w && $q .= ' WHERE '.implode(' AND ', $w);
      
      array_unshift($d, $q);
      
      $d = array_merge($d, $wd);
      
      call_user_func_array('DB::update', $d);
    }
    
    return true;
  }
  
  /**
   * Callbacks for events
   *
   * Checks before/after callbacks in System and
   * executes the functions.
   *
   * @string $cb: The callback event.
   */
  protected function run_callbacks($cb) {
    if (!self::model_data()->has_callbacks_for($cb))
      return true;
    
    foreach (self::model_data()->get_callbacks_for($cb) as $func) {
      if (false === $this->$func())
        return false;
    }
    
    return true;
  }
  
  /**
   * Update rows
   *
   * Pass an id, or an array of ids, and the attributes to update.
   */
  static function update($ids, $attrs) {
    if (!is_array($ids))
      $ids = array($ids);
    
    $model_name = get_class($this);
    
    foreach ($ids as $id) {
      $object = new $model_name('find', $id);
      if ($object)
        $object->update_attributes($attrs);
    }
  }
  
  /**
   * Update object attributes
   *
   * Set object's properties according to $attrs and
   * calls save().
   *
   * @array $attrs: array(column_name => value).
   */
  function update_attributes($attrs) {
    if (!is_array($attrs))
      return false;
    
    $this->add_attributes($attrs);
    
    $this->run_callbacks('before_update');
    
    if (!$this->save())
      return false;
    
    $this->run_callbacks('after_update');
    
    return true;
  }
  
  /**
   * Update object attribute
   *
   * Calls update_attributes() with one attribute.
   */
  function update_attribute($attr, $value) {
    return $this->update_attributes(array($attr => $value));
  }
  
  static function find_by_sql($sql_params, $params = array()) {
    $sql = array_shift($sql_params);
    $data = DB::execute_sql($sql, $sql_params);
    
    self::parse_calc_rows_param($params);
    
    self::retrieve_calc_rows();
    
    if (isset($params['return_array']))
      return $data;
    
    $params['model_name'] = self::cn(true);
    $collection = new Collection($data, $params);
    !get_object_vars($collection) && $collection = array();
    
    if (!count($collection))
      $collection = array();
    return $collection;
  }
  
  static private function parse_calc_rows_param($params) {
    if (isset($params['calc_rows']) || in_array('calc_rows', $params)) {
      if (array_key_exists('calc_rows', $params)) {
        $rows_var = $params['calc_rows'];
      } else {
        $rows_var = 'found_rows';
      }
      
      self::set_calc_rows($rows_var);
      return true;
    }
  }
  
  function reload() {
    try {
      $data = $this->get_current_data();
    } catch (ActiveRecordException $e) {
      // echo $e;
      return false;
    }
    
    foreach($this as $prop => $val)
      unset($this->$prop);
     
    $this->add_attributes((array)$data);
    $this->call_custom_construct();
  }
  
  private function get_assocs($type = 'all', $model = null) {
    !$model && $model = get_class($this);
    $model = strtolower($model);
    
    // $assocs = System::$models->$model->assocs;
    $assocs = System::get_model_assocs($model);
    
    if ($type == 'all')
      return $assocs;
    
    $r = array();
    
    foreach ($assocs as $assoc)
      $assoc->assoc_type == $type && $r[] = $assoc;
    
    return $r;
  }
  
  final static function create_sql($params) {
    $binding_data = array();
    $calc_rows = null;
    
    $joins  = isset($params['joins']) ? ' '.$params['joins'] : null;
    $select = isset($params['select']) ? $params['select'] : '*';
    $from   = isset($params['from']) ? $params['from'] : self::t();
    $order  = isset($params['order']) ? ' ORDER BY '.$params['order'] : null;
    $group  = isset($params['group']) ? ' GROUP BY '.$params['group'] : null;
    $having = isset($params['having'])? ' HAVING '.$params['having'] : null;
    
    if (self::parse_calc_rows_param($params))
      $calc_rows = ' SQL_CALC_FOUND_ROWS';
    
    /**
     * TODO: passing 'per_page' and 'page' param as 'offset' and 'limit'.
     * These vars are about pagination, so if they're present, we're adding the
     * SQL_CALC_FOUND_ROWS option to the query.
     * We have to make a special function for pagination, involving
     * create_page_params(), calc_pages() and pagination().
     *
     * Also note that the 'page' param will be reduced by 1, so the actual page
     * must be passed.
     */
    if (isset($params['per_page']) && isset($params['page'])) {
      $params['limit'] = $params['per_page'];
      $params['page'] > 0 && $params['page']--;
      global $pagination_limit;
      $pagination_limit = $params['per_page'];
      $params['offset'] = $params['per_page'] * $params['page'];
      $calc_rows = ' SQL_CALC_FOUND_ROWS';
      if (!self::set_calc_rows(1)) {
        self::set_calc_rows('found_rows');
      }
    }
    
    if (isset($params['conditions'])) {
      $where = ' WHERE ';
      if (is_array($params['conditions'])) {
        $where .= array_shift($params['conditions']);
        
        $params['conditions'] && $binding_data = $params['conditions'];
        check_array($binding_data);
      } else {
        $where .= $params['conditions'];
      }
      
      unset($params['conditions']);
      
    } else
      $where = null;
    
    if(isset($params['offset']) && isset($params['limit']))
      $limit = " LIMIT ".$params['offset'].", ".$params['limit'];
    elseif(isset($params['limit']) && !isset($params['offset']))
      $limit = " LIMIT ".$params['limit'];
    else
      $limit = null;
    
    $sql_query = "SELECT$calc_rows $select FROM $from$joins$where$group$having$order$limit";
    
    $sql = array($sql_query, $binding_data);
    
    return $sql;
  }
  
  static function count($args) {
    $args['select'] = 'COUNT(*)';
    $sql = self::create_sql($args);
    
    $result = call_user_func_array('DB::execute_sql', $sql);
    return isset($result[0]['COUNT(*)']) ? (int)$result[0]['COUNT(*)'] : false;
  }
  
  static protected function parse_find_params(&$select_type, &$params = array()) {
    $select_types = array('first', 'last', 'all');
    
    if (is_string($select_type) && in_array($select_type, $select_types)) {
      // if (!$params)
        // $params['select']
        // return false;
      
      $find_params['select_type'] = $select_type;
      
    } else {
      $params = $select_type;
      $find_params['select_type'] = 'first';
    }
    
    if (!is_array($params) && (is_int($params) || ctype_digit($params))) {
      # single ID.
      $params = array('conditions' => array(self::t() . '.id = ?', $params));
      
    } elseif (is_array($params) && is_indexed_arr($params)) {
      # array of IDs.
      $params = array('conditions' => array(self::t() . '.id IN (??)', $params));
    }
    
    check_array($params);
    
    if (!empty($params['return_array'])) {
      $find_params['return_array'] = true;
      unset($params['return_array']);
    } else
      $find_params['return_array'] = false;
    
    if (!empty($params['return_value'])) {
      $find_params['return_value'] = $params['return_value'];
      $find_params['return_array'] = true;
      unset($params['return_value']);
    } else
      $find_params['return_value'] = false;
    
    # for collection
    if (isset($params['model_name'])) {
      $find_params['model_name'] = $params['model_name'];
      unset($params['model_name']);
    } else
      $find_params['model_name'] = self::cn();
    
    if ($find_params['select_type'] == 'all' && isset($params['select'])) {
      $find_params['return_array'] = true;
    }
    
    if (!empty($params['return'])) {
      switch ($params['return']) {
        case 'model':
          $find_params['return_array'] = false;
        break;
      }
      unset($params['return']);
    }
    
    return $find_params;
  }
  
  static function find($select, $params = array()) {
    $find_params = self::parse_find_params($select, $params);
    
    $sql = self::create_sql($params);
    
    $data = self::execute_find_sql($sql);
    
    return self::retrieve_find_result($data, $find_params);
  }
  
  static protected function execute_find_sql($sql) {
    $data = call_user_func_array('DB::execute_sql', $sql);
    self::retrieve_calc_rows();
    
    return $data;
  }
  
  static protected function retrieve_find_result($data, $find_params) {
    extract($find_params);
    
    if (empty($data)) {
      if ($return_value)
        return false;
      else
        return array();
    }
    
    if ($select_type == 'first') {
      if (isset($data[0]))
        $data = $data[0];
      else
        return false;
    } elseif ($select_type == 'last') {
      $data = end($data);
    }
    
    if ($return_array) {
      if ($select_type == 'first' || $select_type == 'last') {
        if ($return_value) {
          if (isset($data[$return_value]))
            return $data[$return_value];
          else
            return false;
        }
      }
      
      return $data;
    } else {
      if ($select_type == 'all') {
        $return = new Collection($data, array('model_name' => $model_name));
      } else {
        $return = new $model_name($data);
      }
      
      return $return;
    }
  }
  
  /**
   * Association functions
   */
  
  private function parse_has($prop, $params) {
    $params = obj2array($params);
    
    if(empty($params['foreign_key']))
      $params['foreign_key'] = substr(self::t(), 0, strlen(self::t())-1).'_id';
    
    $conds[] = $params['foreign_key']." = ?";
    unset($params['foreign_key']);
    
    $args['find_params']['conditions'] = array($this->id);
    if(!empty($params['conditions'])) {
      $conds[] = array_shift($params['conditions']);
      foreach($params['conditions'] as $c)
        $args['find_params']['conditions'][] = $c;
      unset($c);
      unset($params['conditions']);
    }
    
    $conds = implode(' AND ', $conds);
    array_unshift($args['find_params']['conditions'], $conds);
    
    $args['find_params'] = array_merge($args['find_params'], $params);
    return $args;
  }
  
  private function has_one_do($prop) {
    $params = $this->parse_has($prop, self::model_data()->assocs->$prop->params);
    $model_name = self::model_data()->assocs->$prop->model_name;
    $params['find_params']['from'] = Application::$models->{strtolower($model_name)}->table_name;
    $params['find_params']['model_name'] = $model_name;
    $this->$prop = $model_name::find('first', $params['find_params']);
  }
  
  private function has_many_do($prop) {
    $params = $this->parse_has($prop, self::model_data()->assocs->$prop->params, 1);
    $model_name = self::model_data()->assocs->$prop->model_name;
    $params['find_params']['from'] = Application::$models->{strtolower($model_name)}->table_name;
    $params['find_params']['model_name'] = $model_name;
    $this->$prop = $model_name::find_all($params['find_params']);
  }
  
  private function belongs_to_do($prop) {
    $params = self::model_data()->assocs->$prop->params;
    
    $model_name = self::model_data()->assocs->$prop->model_name;
    $foreign_key = !empty($params->foreign_key) ? $params->foreign_key : strtolower($model_name).'_id';
    $this->$prop = $this->$foreign_key ? $model_name::find($this->$foreign_key) : false;
  }
  
  # Sets a property on the model that will let know any
  # function that makes queries that we require FOUND_ROWS().
  # $var === 0 will unset the $rows_var;
  # $var === 1 will return the $rows_var (can be used to check if it's set).
  # otherwise, $rows_var will be set as $var.
  static private function set_calc_rows($var) {
    static $rows_var;
    
    if ($var === 0)
      $rows_var = null;
    elseif ($var === 1)
      return $rows_var;
    else
      $rows_var = $var;
  }
  
  static private function retrieve_calc_rows() {
    if (!self::set_calc_rows(1))
      return;
    
    global ${self::set_calc_rows(1)};
    
    ${self::set_calc_rows(1)} = DB::found_rows();
    
    #clear calc_rows
    self::set_calc_rows(0);
  }
}
?>