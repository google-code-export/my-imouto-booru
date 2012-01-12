<?php
before_filter(array('only_user' => 20), 'only', 'create');
verify_method('post', array('only', 'create', 'update'));

set_actions(
  'index',
  'update',
  'create'
);
?>