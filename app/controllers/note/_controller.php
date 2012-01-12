<?php
// layout 'default', :only => [:index, :history, :search]
before_filter(array('post_only_user' => 20), 'only', 'destroy, update, revert');
verify_method('post', array('only', 'update, revert, destroy'));


set_actions(
  'index',
  'search',
  'update'
);
?>