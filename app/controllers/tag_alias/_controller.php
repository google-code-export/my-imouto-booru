<?php
before_filter(array('only_user' => 30), 'only', 'create');
verify_method('post', array('only', 'create', 'update'));

set_actions(
  'create',
  'index',
  'update'
);
?>