<?php
before_filter(array('only_user' => 20), 'only', 'create, destroy, delete, flag, revert_tags, activate, update_batch');
before_filter(array('post_only_user' => 20), 'only', 'update, upload, flag');
before_filter(array('only_user' => 33), 'only', 'moderate, undelete');
before_filter(array('only_user' => 50), 'only', 'import, export');

verify_method('post', array('only', 'update, destroy, create, revert_tags, vote, flag'));

after_filter('save_tags_to_cookie', 'only', 'update, create');
helper('avatar', 'tag', 'comment', 'pool', 'favorite');

set_actions(
  'activate',
  'browse',
  'create',
  'delete',
  'destroy',
  'error',
  'flag',
  'index',
  'import',
  'moderate',
  'random',
  'show',
  'undelete',
  'update',
  'update_batch',
  'upload',
  'vote'
);
?>