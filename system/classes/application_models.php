<?php
class ApplicationModelBase {
  function __construct() {
    include SYSROOT . 'data/app_models.php';
    
    foreach ($models as $model_filename) {
      $model_name = strtolower(ApplicationModel::filename_to_modelname($model_filename));
      $this->$model_name = new ApplicationModel($model_filename);
    }
  }
  
  function __get($model) {
    
  }
  
  function exists($model_filename) {
    return isset($this->$model_filename);
  }
}

class ApplicationModel {
  static $properties = array(
    'tables',
    'table_name',
    'assocs',
    'callbacks',
    'validations',
    'include_helpers',
    'include_models'
  );
  
  var $is_loaded = false;
  
  function __construct($filename) {
    
    $name = ApplicationModel::filename_to_modelname($filename);
    
    $this->filename = $filename;
    $this->name = $name;
  }
  
  static function filename_to_modelname($filename) {
    if (is_int(strpos($filename, '_')))
      $name = str_replace(' ', '', ucwords(str_replace('_', ' ', $filename)));
    else
      $name = ucfirst($filename);
    
    return $name;
  }
  
  static function modelname_to_filename($modelname) {
    $modelname = preg_replace('/([A-Z])/', '_\1', $modelname);
    $modelname = strtolower(trim($modelname, '_'));
    return $modelname;
  }
  
  function load() {
    if ($this->is_loaded)
      return;
    
    include ROOT . 'app/models/' . $this->filename . '.php';
    
    # Set table name. It may differ from the name of the model.
    if (empty($table_name))
      $this->table_name = $this->filename . 's';
    else
      $this->table_name = $table_name;
    
    # Create singleton(?) class.
    if (property_exists($this->name, '_')) {
      $model_name = $this->name;
      $model_name::$_ = new $model_name;
    }
    
    $this->load_tables();
    
    $this->assocs = new StdClass;
    $this->register_assocs();
    $this->register_callbacks();
    $this->register_validations();
    Application::$helpers->load($this->filename);
    $this->is_loaded = true;
  }
  
  private function load_tables() {
    $this->tables = new ApplicationModelTable($this->table_name);
  }
  
  function get_assocs() {
  
  }
  
  function assoc_exists($prop) {
    if (!empty($this->assocs->$prop))
      return true;
  }
  
  function load_assoc_model($prop) {
    $assoc_modelname = strtolower(ApplicationModel::filename_to_modelname($this->assocs->$prop->model_name));
    if (empty(Application::$models->$assoc_modelname))
      return false;
    
    Application::$models->$assoc_modelname->load();
  }
  
  private function register_assocs() {
    if (!System::$assocs_temp)
      return;
    
    foreach (System::$assocs_temp as $assoc_type => $assocs) {
      foreach ($assocs as $assoc) {
        $prop_name = array_shift($assoc);
        if ($assoc)
          $params = array_shift($assoc);
        else
          $params = array();
        
        if (!isset($params['model_name'])) {
          if ($assoc_type == 'has_many')
            $model_name = ApplicationModel::filename_to_modelname(substr($prop_name, 0, -1));
          else
            $model_name = ApplicationModel::filename_to_modelname($prop_name);
        } else {
          $model_name = $params['model_name'];
          unset($params['model_name']);
        }
        
        $this->assocs->$prop_name = array2obj(array(
          'model_name' => $model_name,
          'type' => $assoc_type,
          'params' => $params
        ));
      }
    }
    
    System::$assocs_temp = array();
  }
  
  private function register_callbacks() {
    if (empty(System::$callbacks_temp))
      return;
    
    foreach (System::$callbacks_temp as $cb_name => $cb) {
      foreach ($cb as $cb_funcs) {
        $cb_funcs = explode(',', str_replace(' ', '', $cb_funcs));
        
        if (!empty($this->callbacks[$cb_name])) {
          $new_funcs = array_diff($cb_funcs, $this->callbacks[$cb_name]);
          $cb_funcs = array_unique(array_merge($this->callbacks[$cb_name], $new_funcs));
        }
        
        $this->callbacks[$cb_name] = $cb_funcs;
      }
    }
    
    System::$callbacks_temp = array();
  }
  
  /**
   * System::$models->post->validations
   */
  private function register_validations() {
    if (empty(System::$validations_temp))
      return;
    
    $this->validations = System::$validations_temp;
    
    System::$validations_temp = array();
  }
  
  function has_validations() {
    return !empty($this->validations);
  }
  
  function get_validations() {
    return $this->validations;
  }
  
  function has_callbacks_for($action) {
    return !empty($this->callbacks[$action]);
  }
  
  function get_callbacks_for($action) {
    if (!$this->has_callbacks_for($action))
      return false;
    
    return $this->callbacks[$action];
  }
  
  function include_helper() {
    if (is_bool($key = array_search($this->filename, System::$helpers)))
      return;
    
    include ROOT."app/helpers/".$this->name."_helper.php";
    
    unset(System::$helpers[$key]);
  }
}

class ApplicationModelTable {
  var $pri_keys = array();
  var $uni_keys = array();
  var $mul_keys = array();
  var $names = array();

  function __construct($model_filename) {
    $table_file = System::get_model_table_filename($model_filename);
    include $table_file;
    
    foreach ($columns as $column_name => $column_data) {
      $this->names[$column_name] = $column_data;
      
      if ($this->names[$column_name]['key'] == 'PRI')
        $this->pri_keys[] = $column_name;
      elseif ($this->names[$column_name]['key'] == 'MUL')
        $this->mul_keys[] = $column_name;
      elseif ($this->names[$column_name]['key'] == 'UNI')
        $this->uni_keys[] = $column_name;
    }
  }
  
  function get_names() {
    $names = array();
    foreach (array_keys($this->names) as $name)
      $names[] = $name;
    return $names;
  }
  
  function get_primary_key() {
    return $this->pri_keys;
  }
  
  function get_keycolumns() {
    if ($this->pri_keys)
      return $this->pri_keys;
    elseif ($this->uni_keys)
      return $this->uni_keys;
  }
  
  function get_type($column_name) {
    return $this->names[$column_name]['type'];
  }
  
  function exists($column_name) {
    return !empty($this->names[$column_name]);
  }
}
?>