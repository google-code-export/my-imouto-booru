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
    
    if (property_exists($this, 'is_collection')) {
      $model_name = $params['model_name'];
      unset($params['model_name']);
      
      $this->model_name = $model_name;
    }
    
    if (!$data) {
      if (property_exists($this, 'is_collection'))
        $this->call_custom_construct($data);
        
      return false;
    }
    
    if ($this->cn() != 'collection') {
      $this->set_model_current_data($data);
      $this->set_models_new_data($data);
    }
    
    if (empty($params['prevent_construct']))
      $this->call_custom_construct($data);
  }
  
  function blank($data = array()) {
    $cn = get_class($this);
    $model = new $cn;
    
    $model->empty_model = true;
    
    if (!$model->add_attributes($data))
      return false;
    
    return $model;
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
      if ($attr == 'empty_model' || ($this->model_data() && $this->model_data()->assoc_exists($attr)))
        continue;
      
      $on_change_method = 'on_' . $attr . '_change';
      if (method_exists($this, $on_change_method))
        $this->$on_change_method($v);
      else
        $this->$attr = $v;
    }
    return true;
  }
  
  function create_from_array($data) {
    if (!is_array($data))
      return false;
    
    $model = $this->get_empty_model();
    
    foreach ($data as $k => $v)
      $model->$k = $v;
    return $model;
  }
  
  private function get_empty_model() {
    $mn = $this->cn(false);
    return new $mn;
  }
  
  # In case the model has no keycolumns, we need a way to find it if saving it.
  private function set_model_current_data(&$data) {
    if (!$this->model_data() || $this->model_data()->tables->get_keycolumns())
      return;
    
    $this->current_model_data = array();
    
    foreach ($data as $key => $val) {
      $this->current_model_data[$key] = $val;
    }
  }
  
  private function set_models_new_data(&$data) {
    if ($data) {
      foreach($data as $key => $val)
        $this->$key = $val;
    }
  }
  
  # Allowing $data = null for create()
  # TODO: if data is null and $this->is_collection, throw an error.
  private function call_custom_construct(&$data = null) {
    if (!method_exists($this, '_construct'))
      return;
    
    # Passing data to _construct is needed for Collection.
    if (property_exists($this, 'is_collection'))
      $this->_construct($data);
    else
      $this->_construct();
  }
  
  function __call($n, $o) {
    
    switch ($n) {
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
      case is_int(strpos($n, 'find_')):
        $n = substr($n, 5);
        
        $params = $user_params = array();
        $select_type = null;
        
        if (strpos($n, 'first') === 0) {
          $select_type = 'first';
          $n = substr($n, 5);
        } elseif (strpos($n, 'last') === 0) {
          $select_type = 'last';
          $n = substr($n, 4);
        } elseif (strpos($n, 'all') === 0) {
          $select_type = 'all';
          $n = substr($n, 3);
        }
        
        if (isset($o[0]) && ($o[0] == 'first' || $o[0] == 'last' || $o[0] == 'all'))
          $select_type = array_shift($o);
        elseif (!$select_type)
          $select_type = 'first';
        
        if (is_indexed_arr($o))
          $user_params = array_shift($o);
        
        check_array($user_params);
        
        if (is_int(strpos($n, 'by_'))) {
          $by = substr($n, strpos($n, 'by_'));
          $n = str_replace($by, '', $n);
          $by = explode('_and_', substr($by, 3));
          foreach ($by as &$b)
            $b .= ' = ?';
          $by = implode(' AND ', $by);
          
          $params['conditions'] = array_merge(array($by), $user_params);
        }
        
        if ($n) {
          $n = trim($n, '_');
          if (!substr_count($n, '_and_'))
            $params['return_value'] = $n;
          
          $params['select'] = str_replace('_and_', ', ', trim($n, '_'));
        }
        
        $params = array_merge($params, $user_params);
        
        $data = $this->find($select_type, $params);
        
        return $data;
      break;
      
      # To check if an attribute has changed upon save();
      case ((strlen($n) - strpos($n, '_changed')) == 8) :
        $attribute = str_replace('_changed', '', $n);
        return array_key_exists($attribute, $this->changed_attributes);
      break;
      
      # To check what an attribute was before save() or update_attributes();
      case ((strlen($n) - strpos($n, '_was')) == 4) :
        $attribute = str_replace('_was', '', $n);
        
        if (!array_key_exists($attribute, $this->changed_attributes))
          return;
        
        return $this->changed_attributes[$attribute];
      break;
    }
    
    if (method_exists($this, '_call'))
      return $this->_call($n, $o);
  }
  
  #TODO: improve this function
  protected function sanitize_sql($query, $args) {
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
  
  function __get($prop) {
    if (method_exists($this, '_is_collection'))
      return;
    
    if ($this->model_data() && $this->model_data()->assoc_exists($prop)) {
      return $this->load_association($prop);
    }
    
    if ($prop == 'record_errors') {
      $this->record_errors = new RecordErrors;
      return $this->record_errors;
    }
    
    if (method_exists($this, '_get'))
      return $this->_get($prop);
  }
  
  function maximum($column) {
    return DB::select_value('MAX(' . $column . ') FROM ' . $this->t());
  }
  
  function minimum($column) {
    return DB::select_value('MIN(' . $column . ') FROM ' . $this->t());
  }
  
  function bare() {
    $attrs = array();
    $protected = array('current_model_data', 'record_errors', 'changed_attributes');
    foreach ($this as $name => $val) {
      if (in_array($name, $protected))
        continue;
      $attrs[$name] = $val;
    }
    return $attrs;
  }
  
  function attributes() {
    $attrs = (array)$this;
    
    $protected = array('current_model_data', 'record_errors', 'changed_attributes');
    
    foreach ($protected as $p) {
      unset($attrs[$p]);
    }
    
    return $attrs;
  }
  
  protected function load_association($prop) {
    # Load model in case it's not been loaded.
    $this->model_data()->load_assoc_model($prop);
    
    $assoc_function = $this->model_data()->assocs->$prop->type . '_do';
    $this->$assoc_function($prop);
    return $this->$prop;
  }
  
  /**
   * for now it receives parameters as seen on Post::update_has_children()
   */
  function exists($query, $params = null) {
    if (is_int($query))
      $where = ' id = '.$query;
    else
      $where = $this->sanitize_sql($query, $params);
    
    return DB::exists($this->t().' WHERE '.$where);
  }
  
  /**
   * Table name
   *
   * @return string: Name of the table for the model.
   */
  function t() {
    // return isset($this->t) ? $this->t : System::$models->{$this->cn()}->table;
    return isset($this->t) ? $this->t : $this->model_data()->table_name;
  }
  
  /**
   * Get class name.
   *
   * Returns the name of the class.
   */
  function cn($lower = true) {
    return $lower ? strtolower(get_class($this)) : get_class($this);
  }
  
  # Model File Name
  function fn() {
    return ApplicationModel::modelname_to_filename($this->cn(false));
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
    
    $type = $this->model_data()->tables->get_type($column);
    
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
      $keys = $this->get_model_keydata();
      foreach ($keys as $k => $v)
        $where[] = "`$k` != :$k";
    }
    
    $where = implode(' AND ', $where);
    $result = DB::exists($this->t()." WHERE $where", $keys) ? false : true;
    
    return $result;
  }
  
  function model_data() {
    if (empty(Application::$models->{$this->cn()}))
      return false;
    
    return Application::$models->{$this->cn()};
  }
  
  # TODO: incomplete function.
  private function validate_data($data = null, $action) {
    # TODO: $data could be removed and be always $this.
    if (!$data)
      $data = &$this;
    else
      $data = array2obj($data);
    
    $this->run_callbacks('before_validation');
    $error = false;
    
    if ($this->model_data()->has_validations()) {
    
      foreach ($this->model_data()->get_validations() as $model_property => $model_validations) {
        
        foreach ($model_validations as $prop => $opts) {
          check_array($opts);
          
          if ($data->$model_property === null) {
            if (empty($opts['accept_null'])) {
              return false;
            } else
              unset($opts['accept_null']);
          }
          
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
              
              !isset($opts['message']) && $opts['message'] = 'doesn\'t match confirmation.';
              
              $validation_result = $this->$model_property === $this->$prop_confirm;
            break;
            
            case 'presence':
              !isset($opts['message']) && $opts['message'] = 'cannot be empty.';
              $validation_result = property_exists($data, $model_property);
            break;
            
            case 'uniqueness':
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
            if (!is_string($validation_result)) {
              $message = isset($opts['message']) ? $opts['message'] : 'is not valid';
            } else
              $message = $validation_result;
            
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
  
  // private function parse_validation_options(&$data, &$prop, &$opts) {
    // foreach ($opts as $opt => $params) {
      // switch ($opt) {
        // case 'numericality':
          // switch ($params) {
            // case ($params === true && ctype_digit($this->$prop)):
              // return true;
            // break;
            
            // case (
          // }
          # Checking if $prop is a whole number.
          // if ($params === true && ctype_digit($data->$prop))
            // return true;
          
          // elseif (is_string($params)) {
            // $op = compare_num($data->$prop, $params);
            // if ($op !== null)
              // return $op;
          // }
          
        // break;
      // }
    // }
  // }
  
  function empty_record($data) {
    foreach ($data as $key => $v) {
      $this->$key = $v;
    }
    
    $this->set_empty_model();
  }
  
  function set_empty_model() {
    $this->empty_model = true;
  }
  
  function column_exists($column_name) {
    return $this->model_data()->tables->exists($column_name);
  }
  
  function is_empty_model() {
    return isset($this->empty_model);
  }
  
  # If no data is passed, it's assumed this method is being called from save
  # to create the current object, otherwise it's a Model class creating a new model.
  function create($data = null) {
    if ($data) {
      $cn = get_class($this);
      
      if (!$new_model = $this->blank($data))
        return false;
      
      if (!$new_model->run_callbacks('before_save'))
        return false;
      
      if ($new_model->create())
        $new_model->run_callbacks('after_save');
      
      return $new_model;
    }
    
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
      // elseif ($v === null)
        // $v = 'null';
      
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
    
    $sql = $this->t().' ('.$values_names.') VALUES ('.$values_marks.')';
    
    array_unshift($values, $sql);
    
    $id = call_user_func_array('DB::insert', $values);
    
    $primary_key = $this->get_table_keycolumns('pri');
    
    if ($primary_key && count($primary_key) == 1) {
      if (!$id) {
        $this->record_errors->add_to_base('There was a MySQL Error');
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
      
      $w = ' `id` IN (??) ';
      
    } else {
      if (!$keys = $this->get_model_keydata())
        return false;
      
      foreach (array_keys($keys) as $k)
        $w[] = "`$k` = ?";
      
      $w = implode(' AND ', $w);
    }
    
    // DB::delete(array_values($keys));
    call_user_func_array('DB::delete', array_merge(array($this->t().' WHERE '.$w), array_values($keys)));
    
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
    
    call_user_func_array('DB::delete', array_merge(array($this->t().' WHERE '.$w), array_values($keys)));
    
    $this->run_callbacks('after_destroy');
    
    return true;
  }
  
  private function get_old_model_data() {
    return $this->current_model_data;
  }
  
  private function get_table_keycolumns($type = null) {
    if ($type == 'pri')
      return $this->model_data()->tables->get_primary_key();
    
    $keys = $this->model_data()->tables->get_keycolumns();
    
    if (!$type)
      return $keys;
  }
  
  /**
   * Get model's primary keys
   *
   * Searches in the System::$database_tables variable for
   * all primary keys that the model may have, and returns
   * a column_name => column_value array.
   */
  private function get_model_keydata() {
    $key_cols = $this->get_table_keycolumns();
    
    $keys = array();
    if(!$key_cols) {
      return $this->get_old_model_data();
    }
    
    foreach ($key_cols as $k) {
      $keys[$k] = isset($this->$k) ? $this->$k : null;
    }
    
    return $keys;
  }
  
  private function throw_exception($function, $message) {
    throw new ActiveRecordException("<b>ActiveRecord::$function</b>: $message.");
  }
  
  private function get_current_data() {
    if (!$primary_keys = $this->get_model_keydata())
      $this->throw_exception('get_current_data', 'Current keydata cannot be found for model '.get_class($this));
    
    $find = 'find_by_'.implode('_and_', array_keys($primary_keys));
    
    $params = array();
    foreach ($primary_keys as $val) {
      $params[] = $val;
    }
    if (count($params) > 1)
      $params = array($params);
    
    $current = call_user_func_array(array($this, $find), $params);
    
    if (empty($current)) {
      $this->throw_exception('get_current_data', 'Current data cannot be found '.get_class($this));
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
  final function save($values = array()) {
    
    // try {
      // $this->validate_data(null, 'save');
    // } catch (ActiveRecordException $e) {
      // return false;
    // }
    if (!$this->validate_data(null, 'save'))
      return false;
    
    if (!$this->run_callbacks('before_save'))
      return false;
    
    if ($this->is_empty_model()) {
      if (!$this->create())
        return false;
    } else {
      if (!$this->save_do($values))
        return false;
    }
    
    $this->run_callbacks('after_save');
   
    return true;
  }
  
  private final function save_do($values) {
    $w = $wd = $q = $d = $this->changed_attributes = array();
    
    $dt = $this->model_data()->tables->get_names();
    
    if (!$values)
      $data = &$this;
    else
      $data = &$values;

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
    
    foreach ($data as $prop => $val) {
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
      $q = $this->t() . " SET " . implode(', ', $q);
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
    if (!$this->model_data()->has_callbacks_for($cb))
      return true;
    
    foreach ($this->model_data()->get_callbacks_for($cb) as $func) {
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
  function update($ids, $attrs) {
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
      return;
    
    $this->add_attributes($attrs);
    
    $this->run_callbacks('before_update');
    // $r = $this->save();
    
    // vde($r);
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
  
  function find_by_sql($sql, $sql_params = array(), $params = array()) {
    $data = DB::execute_sql($sql, $sql_params);
    
    $this->calc_rows();
    
    if (isset($params['return_array']))
      return $data;
    
    $params['model_name'] = $this->cn(true);
    $collection = new Collection($data, $params);
    
    return $collection;
  }
  
  function reload() {
    try {
      $data = $this->get_current_data();
    } catch (ActiveRecordException $e) {
      echo $e;
      return false;
    }
    
    foreach($this as $prop => $val)
      unset($this->$prop);
     
    $this->set_models_new_data($data);
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
  
  private function create_sql($params) {
    $binding_data = array();
    $calc_rows = null;
    
    $joins = isset($params['joins']) ? ' '.$params['joins'] : null;
    $select = isset($params['select']) ? $params['select'] : '*';
    $from   = isset($params['from']) ? $params['from'] : $this->t();
    $order  = isset($params['order']) ? ' ORDER BY '.$params['order'] : null;
    $group  = isset($params['group']) ? ' GROUP BY '.$params['group'] : null;
    
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
    
    $sql_query = "SELECT$calc_rows $select FROM $from$joins$where$group$order$limit";
    
    $sql = array($sql_query, $binding_data);
    
    return $sql;
  }
  
  function count($args) {
    $args['select'] = 'COUNT(*)';
    $sql = $this->create_sql($args);
    
    $result = call_user_func_array('DB::execute_sql', $sql);
    return isset($result[0]['COUNT(*)']) ? $result[0]['COUNT(*)'] : false;
  }
  
  function find($params) {
    $select_types = array('first', 'last', 'all');
    
    if (is_string($params) && in_array($params, $select_types)) {
      $params = func_get_args();
      
      $select = array_shift($params);
      
      if (!$params)
        return false;
      $params = array_shift($params);
    } else
      $select = 'first';
    
    if (!is_array($params) && (is_int($params) || ctype_digit($params))) {
      # single ID.
      $params = array('conditions' => array('id = ?', $params));
      
    } elseif (is_array($params) && is_indexed_arr($params)) {
      # array of IDs.
      $params = array('conditions' => array('id IN (??)', $params));
    }
    
    if (isset($params['return_array'])) {
      $return_array = true;
      unset($params['return_array']);
    } else
      $return_array = false;
    
    if (isset($params['return_value'])) {
      $return_value = $params['return_value'];
      $return_array = true;
      unset($params['return_value']);
    } else
      $return_value = false;
    
    # for collection
    if (isset($params['model_name'])) {
      $model_name = $params['model_name'];
      unset($params['model_name']);
    } else
      $model_name = get_class($this);
    
    if ($select == 'all' && isset($params['select'])) {
      $return_array = true;
    }
    
    if (!empty($params['return'])) {
      switch ($params['return']) {
        case 'model':
          $return_array = false;
        break;
      }
      unset($params['return']);
    }
    
    $sql = $this->create_sql($params);
    
    $data = call_user_func_array('DB::execute_sql', $sql);
    
    $this->calc_rows();
    
    if (empty($data)) {
      if ($return_value)
        return false;
      else
        return array();
    }
    
    if ($select == 'first') {
      if (isset($data[0]))
        $data = $data[0];
      else
        return false;
    } elseif ($select == 'last') {
      $data = end($data);
    }
    
    if ($return_array) {
      if ($select == 'first' || $select == 'last') {
        if ($return_value) {
          if (isset($data[$return_value]))
            return $data[$return_value];
          else
            return false;
        }
      }
      
      return $data;
    } else {
      if ($select == 'all') {
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
  
  protected function parse_has($prop, $params) {
    $params = obj2array($params);
    
    if(empty($params['foreign_key']))
      $params['foreign_key'] = substr($this->t(), 0, strlen($this->t())-1).'_id';
    
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
    
    $args['find_params'] += $params;
    return $args;
  }
  
  protected function has_one_do($prop) {
    
    $params = $this->parse_has($prop, $this->model_data()->assocs->$prop->params);
    $model_name = $this->model_data()->assocs->$prop->model_name;
    $params['find_params']['from'] = Application::$models->{strtolower($model_name)}->table_name;
    $params['find_params']['model_name'] = $model_name;
    $this->$prop = $this->find('first', $params['find_params']);
  }
  
  protected function has_many_do($prop) {
    $params = $this->parse_has($prop, $this->model_data()->assocs->$prop->params, 1);
    $model_name = $this->model_data()->assocs->$prop->model_name;
    $params['find_params']['from'] = Application::$models->{strtolower($model_name)}->table_name;
    $params['find_params']['model_name'] = $model_name;
    $this->$prop = $this->collection('find', $params['find_params']);
  }
  
  protected function belongs_to_do($prop) {
    $params = $this->model_data()->assocs->$prop->params;
    
    $model_name = $this->model_data()->assocs->$prop->model_name;
    $foreign_key = !empty($params->foreign_key) ? $params->foreign_key : $prop.'_id';
    $this->$prop = $model_name::$_->find($this->$foreign_key);
  }
  
  # Sets a property on the model that will let know any
  # function that makes queries that we require FOUND_ROWS().
  private function activate_calc_rows($var) {
    $this->calc_rows = $var;
  }
  
  private function calc_rows() {
    if (!$this->will_calc_rows())
      return;
    
    global ${$this->calc_rows};
    
    ${$this->calc_rows} = DB::found_rows();
    
    $this->clear_calc_rows();
  }
  
  private function will_calc_rows() {
    return !empty($this->calc_rows);
  }
  
  private function clear_calc_rows() {
    unset($this->calc_rows);
  }
  
  function collection() {
    $args = func_get_args();
    
    if (!$args)
      return false;
    
    # If first value is 'Paginate->foo', $foo will be filled with
    # the FOUND_ROWS() value of the query. This is needed to calculate
    # pages for paginator.
    if (is_int(strpos($args[0], 'Paginate->'))) {
      $paginate = substr(array_shift($args), 10);
      
      $this->activate_calc_rows($paginate);
      
      if (empty($args))
        return false;
      
      $find_func = array_shift($args);
    } else {
      $find_func = array_shift($args);
    }
    
    if (!$args)
      return false;
    
    $params = &$args;
    
    $func_params = &$params;
    
    # TODO: drop error if 3rd param isn't array.
    if ($find_func == 'find')
      array_unshift($func_params, 'all');
    
    $collection = call_user_func_array(array($this, $find_func), $func_params);
    
    return (array)$collection ? $collection : array();
  }
}

class Collection extends ActiveRecord {

  var $is_collection = true;

  function _construct(&$data = null) {
    if($data) {
      $i = 0;
      foreach($data as $d) {
        $this->$i = new $this->model_name($d);
        $i++;
      }
    }
    
    unset($this->model_name);
    unset($this->t);
    unset($this->is_collection);
  }
  
  function _is_collection() {}
  
  /**
   * Searches objects for a property with a value and returns object.
   */
  function search($prop, $value) {
  
    foreach ($this as &$obj) {
      if ($obj->$prop == $value)
        return $obj;
    }
    
    return false;
  }
  
  # returns an *array* with the models that matches the options.
  # $posts->select(array('is_active' => true, 'user_id' => 4));
  function select($opts) {
    $objs = array();
    
    foreach ($this as &$obj) {
      foreach ($opts as $prop => $cond) {
        if (!$obj->$prop || $obj->$prop != $cond)
          continue;
        $objs[] = $obj;
      }
    }
    
    return $objs;
  }
}
?>