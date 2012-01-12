<?php
verify_method('post', array('only', 'authenticate', 'update', 'create', 'unban', 'modify_blacklist', 'check'));
before_filter(array('only_user' => 10), 'only', 'authenticate, update, edit, modify_blacklist');
before_filter(array('only_user' => 35), 'only', 'invites');
before_filter(array('only_user' => 40), 'only', 'block, unblock, show_blocked_users');
before_filter(array('post_only_user' => 20), 'only', 'set_avatar');

// filter_parameter_logging :password

// auto_complete_for :user, :name
  
helper('avatar');

set_actions(
  'authenticate',
  'change_password',
  'check',
  'create',
  'edit',
  'home',
  // 'index',
  'login',
  'logout',
  'modify_blacklist',
  // 'reset_password',
  'set_avatar',
  'show',
  'signup',
  'update'
);
?>