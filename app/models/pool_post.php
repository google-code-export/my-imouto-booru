<?php
$table_name = 'pools_posts';
belongs_to('post', array('joins' => ' JOIN users ON posts.user_id = users.id JOIN posts_tags ON posts.id = posts_tags.post_id JOIN tags ON posts_tags.tag_id = tags.id '));
belongs_to('next_post', array('model_name' => "Post", 'foreign_key' => "next_post_id"));
belongs_to('prev_post', array('model_name' => "Post", 'foreign_key' => "prev_post_id"));

class PoolPost extends ActiveRecord {
  static $_;
  
  var $api_attributes = array('id', 'pool_id', 'post_id', 'active', 'sequence', 'next_post_id', 'prev_post_id');
  
  function _construct() {
    $this->active = true;
    $this->sequence = (string)$this->sequence;
    $this->api_attributes = array_fill_keys($this->api_attributes, '');
    foreach (array_keys($this->api_attributes) as $attr) {
      // # TODO: Don't know what 'active' is about. It's a "versioned".
      if (isset($this->$attr))
        $this->api_attributes[$attr] = &$this->$attr;
    }
  }
  
  function can_change_is_public($user) {
    return $user->has_permission($pool); # only the owner can change is_public
  }

  function can_change(&$user, $attribute = null) {
    if ($user->is('>=20'))
      return true;
    elseif ($this->is_public || $user->has_permission($this))
      return true;
  }

  # This matches Pool.post_pretty_sequence in pool.js.
  function pretty_sequence() {
    # Pool sequence must start with a number.
    return '#'.$this->sequence;
  }

  # Changing pool orderings affects pool sorting in the index.
  function expire_cache() {
    // Cache.expire
  }

  function to_json($params = array()) {
    return to_json($this->api_attributes, $params);
  }
}
?>