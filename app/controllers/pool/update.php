<?php
required_params('id');
required_params('pool', 'only', 'post');

$pool = Pool::find(Request::$params->id);

if (!$pool->can_be_updated_by(User::$current))
  access_denied();

if (Request::$post) {
  $pool->update_attributes(Request::$params->pool);
  respond_to_success("Pool updated", array('#show', array('id' => Request::$params->id)));
}
?>