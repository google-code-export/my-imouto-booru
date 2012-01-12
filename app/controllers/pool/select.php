<?php
required_params('post_id');

if (User::$current->is_anonymous)
  $pools = Pool::$_->find_all(array('order' => "name", 'conditions' => "is_active = TRUE AND is_public = TRUE"));
else
  $pools = Pool::$_->find_all(array('order' => "name", 'conditions' => array("is_active = TRUE AND (is_public = TRUE OR user_id = ?)", User::$current->id)));

$options = array('(000) DO NOT ADD' => 0);
// vde($pools);
foreach ($pools as $p) {
  $options[str_replace('_', ' ', $p->name)] = $p->id;
}

$option_value = !empty($_SESSION['last_pool_id']) ? $_SESSION['last_pool_id'] : null;

layout(false);
?>