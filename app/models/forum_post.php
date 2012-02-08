<?php
belongs_to('creator', array('model_name' => "User", 'foreign_key' => 'creator_id'));
after('create', 'initialize_last_updated_by, update_parent_on_create');
before('validation', 'validate_title, validate_lock');
validates(array(
  'body' => array('length' => array('>1', 'message' => "You need to enter a body"))
));
before('destroy', 'update_parent_on_destroy');
has_many('children', array('model_name' => "ForumPost", 'foreign_key' => 'parent_id', 'order' => "id"));
belongs_to('parent_post', array('model_name' => "ForumPost", 'foreign_key' => 'parent_id'));

class ForumPost extends ActiveRecord {
  static function lock($id) {
    # Run raw SQL to skip the lock check
    db::update("forum_posts SET is_locked = TRUE WHERE id = ?", $id);
  }
  
  static function unlock($id) {
    # Run raw SQL to skip the lock check
    db::update("forum_posts SET is_locked = FALSE WHERE id = ?", $id);
  }
  
  function validate_lock() {
    if ($this->root && $this->root->is_locked) {
      $this->record_errors->add_to_base("Thread is locked");
      return false;
    }

    return true;
  }

  static function stick($id) {
    # Run raw SQL to skip the lock check
    db::update("forum_posts SET is_sticky = TRUE WHERE id = ?", $id);
  }

  static function unstick($id) { db::show_query();
    # Run raw SQL to skip the lock check
    db::update("forum_posts SET is_sticky = FALSE WHERE id = ?", $id);
  }

  function update_parent_on_destroy() {
    if (!$this->is_parent) {
      $this->parent_post->update_attribute('response_count', $this->parent_post->response_count - 1);
    }
  }

  function update_parent_on_create() {
    if (!$this->is_parent && $this->parent_post) {
      $this->parent_post->update_attributes(array('updated_at' => $this->updated_at, 'response_count' => $this->parent_post->response_count + 1, 'last_updated_by' => $this->creator_id));
    }
  }

  function set_root() {
    if ($this->is_parent)
      return $this;
    else
      return $this->parent_post;
  }

  function set_root_id() {
    if ($this->is_parent)
      return $this->id;
    else
      return $this->parent_id;
  }

  function api_attributes() {
    return array(
      'body' => $this->body, 
      'creator' => $this->author, 
      'creator_id' => $this->creator_id, 
      'id' => $this->id, 
      'parent_id' => $this->parent_id, 
      'title' => $this->title
    );
  }
  
  function to_json($params = array()) {
    return to_json($this->api_attributes(), $params);
  }

  function to_xml($options = array()) {
    return to_xml($this->api_attributes(), "forum_post", $options);
  }
  
  static function updated($user) {
    $conds = array();
    
    if (!$user->is_anonymous)
      $conds[] = "creator_id <> " . $user->id;

    if (!$newest_topic = ForumPost::find_first(array('order' => "id desc", 'limit' => 1, 'select' => "created_at", 'conditions' => $conds)))
      return false;
    return $newest_topic->created_at > $user->last_forum_topic_read_at;
  }

  function set_is_parent() {
    return empty($this->parent_id);
  }

  function validate_title() {
    if ($this->is_parent) {
      if (empty($this->title)) {
        $this->record_errors->add('title', "missing");
        return false;
      }
      
      if (!preg_match('/\S/', $this->title)) {
        $this->record_errors->add('title', "missing");
        return false;
      }
    }
    
    return true;
  }
  
  function initialize_last_updated_by() {
    if ($this->is_parent) {
      $this->update_attribute('last_updated_by', $this->creator_id);
    }
  }
  
  function set_last_updater() {
    return User::find_name($this->last_updated_by);
  }
  
  function set_author() {
    return $this->creator->name;
  }
}
?>