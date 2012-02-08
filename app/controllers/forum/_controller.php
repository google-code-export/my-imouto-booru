<?php
include_model('forum_post');
helper('avatar');
verify_method('post', array('only', 'create, destroy, update, stick, unstick, lock, unlock'));
before_filter(array('only_user' => 40), 'only', 'stick, unstick, lock, unlock');
before_filter(array('only_user' => 10), 'only', 'destroy, update, edit, add, mark_all_read');
before_filter(array('post_only_user' => 10), 'only', 'create');

set_actions(
  'stick',
  'unstick',
  'preview',
  'new',
  'create',
  'destroy',
  'edit',
  'update',
  'show',
  'index',
  'search',
  'lock',
  'unlock',
  'mark_all_read'
);
?>