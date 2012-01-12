<?php
if (Request::$post) {
  required_params('pool');
  
  $pool = Pool::$_->create(array_merge(Request::$params->pool, array('user_id' => User::$current->id)));
  
  if ($pool->record_errors->blank())
    respond_to_success("Pool created", array("#show", array('id' => $pool->id)));
  else
    respond_to_error($pool, "#index");
} else
  $pool = Pool::$_->blank(array('user_id' => User::$current->id));
?>