<?php
include_model('comment', 'note_version');

if (!empty(Request::$params->name))
  $user = User::$_->find_by_name(Request::$params->name);
else
  $user = User::$_->find(Request::$params->id);

if (!$user)
  die_404();
else
  set_title($user->id == User::$current->id ? "My Profile" : $user->name . "'s Profile");

if (User::$_->is('>=40')) {
  // $user_ips = UserLog.find_by_sql("SELECT ul.ip_addr, ul.created_at FROM user_logs ul WHERE ul.user_id = #{@user.id} ORDER BY ul.created_at DESC")
  // $user_ips.map! { |ul| ul.ip_addr }
  // $user_ips.uniq!
}

$tag_types = CONFIG::$tag_types;
foreach (array_keys($tag_types) as $k) {
  if (!preg_match('/^[A-Z]/', $k) || $k == 'General' || $k == 'Faults')
    unset($tag_types[$k]);
}

// $tag_types = array_filter(array_map(function($k){if (preg_match('/^[A-Z]/', $k) && $k != 'General' && $k != 'Faults') return $k;}, array_keys(CONFIG::$tag_types)));

// vde($tag_types);
?>