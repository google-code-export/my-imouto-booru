<?php
verify_method('post', array('only', 'create, destroy, update, mark_as_spam'));
before_filter(array('only_user' => 20), 'only', 'create, destroy, update');
before_filter(array('only_user' => 33), 'only', 'moderate');
helper('post', 'avatar');

set_actions(
  'index',
  'create',
  'edit',
  'update',
  'destroy',
  'show'
);
?>