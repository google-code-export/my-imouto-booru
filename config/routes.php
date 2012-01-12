<?php
ActionController::$routes = array(
  'static#index' => '$root',
  'post#show' => array('post/show/$id/$tag_title', 'requirements' => array('id' => '\d+')),
  'pool#zip' => array('pool/zip/$id/$filename', 'requirements' => array('id' => '\d+', 'filename' => '.+')),
  array('$controller/$action/$id.$format', 'requirements' => array('id' => '[-\d]+')),
  array('$controller/$action/$id', 'requirements' => array('id' => '[-\d]+')),
  '$controller/$action.$format',
  '$controller/$action'
);
?>