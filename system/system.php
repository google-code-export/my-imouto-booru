<?php
class System {
  static $conf;
  
  /**
   * Temporal data
   *
   * Models will declare associations and callbacks when their file is loaded
   * throught the has_one(), before_save(), (and so on) functions.
   * Those functions will store here the data that will be used after
   * loading the model. Keeping temporally the data is needed because we can't
   * know the name of the model before loading it!
   */
  static $assocs_temp = array();
  static $callbacks_temp = array();
  static $validations_temp = array();
  
  /**
   * List of controllers.
   */
  static $controllers = array();
  
  /**
   * Load Files
   *
   * Loads models, controllers and helpers.
   */
  static function start() {
    require SYSROOT . 'data/app_controllers.php';
    require SYSROOT . 'classes/application.php';
    require SYSROOT . 'classes/application_helpers.php';
    require SYSROOT . 'classes/application_models.php';
    require SYSROOT . 'classes/record_errors.php';
    require SYSROOT . 'classes/cookies.php';
    require SYSROOT . 'classes/request_params.php';
    require SYSROOT . 'classes/collection.php';
    
    Application::load_files();
    
    foreach (self::$conf->autoload_models as $model)
      Application::$models->$model->load();
    
    if (self::$conf->system_error_reporting)
      set_error_handler('system_error_reporting');
        
    ActionController::start();
    
    if (System::$conf->show_errors_on_json && Request::$format == 'json') {
      set_error_handler('json_error_handler');
    }
    
    include CTRLSPATH . 'application.php';
    include ROOT . 'app/helpers/application_helper.php';

    ActionController::load_controller();

    if (!ActionController::action_exists()) {
      if (!ActionController::rescue_action())
        exit_with_status(404);
    }
  }
  
  # models[classname][assocs][user] = array()
  private static function register_assocs($class_name) {
    if (!self::$assocs_temp)
      return;
    
    foreach (self::$assocs_temp as $atype => $ds) {
      foreach ($ds as $d) {
        if (count($d) == 1 )
          self::$models[$class_name]['assocs'][strtolower($d[0])] = array(
            'assoc_type' => $atype,
            'params' => array(
              'class_name' => str_replace(' ', '', ucwords(str_replace('_', ' ', ($d[0]))))
            )
          );
        else
          self::$models[$class_name]['assocs'][$d[0]] = array('assoc_type' => $atype, 'params' => $d[1]);
      }
    }
    self::$assocs_temp = array();
  }
  
  static function switch_controller($controller) {
    self::clear_controller_data();
    request::$controller = $controller;
    ActionController::load_controller();
  }
  
  private static function clear_controller_data() {
    self::$assocs_temp = array();
    self::$callbacks_temp = array();
    self::$validations_temp = array();
  }
  
  /**
   * Adds validations to temporary variable
   *
   */
  static function validations_temp($data, $validations = null) {
    if (!$validations)
      self::$validations_temp = array_merge(self::$validations_temp, $data);
    else
      self::$validations_temp = array_merge(self::$validations_temp, array($data => $validations));
  }
  
  static function get_model_table($model) {
    return self::$models->$model->table;
  }
  
  static function get_model_assocs($model) {
    return self::$models->$model->assocs;
  }
  
  static function get_model_table_filename($model_name) {
    return SYSROOT . 'database/tables/' . $model_name . '.php';
  }
}

System::$conf = new stdclass;
?>