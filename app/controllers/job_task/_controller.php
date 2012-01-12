<?php
before_filter(array('only_user' => 50), 'only', 'destroy, restart');

set_action(
  'index',
  // 'show',
  // 'destroy',
  // 'restart'
);
?>