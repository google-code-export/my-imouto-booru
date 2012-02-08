<?php
$action_file = ADMINROOT . 'actions/' . Request::$action.".php";

if (!file_exists($action_file)) {
  require ADMINROOT . 'invalid_action.php';
  exit;
}

ob_start();
require $action_file;
$body = ob_get_clean();

require ADMINROOT.'layout.php';
?>