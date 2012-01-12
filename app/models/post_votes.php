<?php
$table_name = "post_votes";

belongs_to('post', array('foreign_key' => 'post_id'));
belongs_to('user', array('foreign_key' => 'user_id'));

class PostVotes extends ActiveRecord {
  static $_;
  
  function find_by_ids($user_id, $post_id) {
    $this->find('first', array('conditions' => array("user_id = ? AND post_id = ?", $user_id, $post_id)));
  }
  
  function find_or_create_by_id($user_id, $post_id) {
    $entry = $this->find_by_ids(array('user_id' => $user_id, 'post_id' => $post_id));
    return $entry? $entry : $this->create(array('user_id' => $user_id, 'post_id' => $post_id));
  }
}
?>