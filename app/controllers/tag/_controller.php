<?php
before_filter(array('only_user' => 50), 'only', 'fix_count');
before_filter(array('only_user' => 40), 'only', 'mass_edit, edit_preview');
before_filter(array('only_user' => 20), 'only', 'update,  edit');
if (CONFIG::allow_delete_tags)
  before_filter((array('only_user' => 35)), 'only', 'delete');

set_actions(
  'summary',
  'index',
  'fix_count',
  'edit',
  'update',
  'related',
  CONFIG::allow_delete_tags && 'delete'
  // 'popular_by_day',
  // 'popular_by_week',
  // 'popular_by_month',
  // 'cloud',
  // 'mass_edit',
  // 'edit_preview'
);
?>