<?php
belongs_to('user');
before('validation', 'normalize_name');
validates('name', array('uniqueness' => true));


# TODO: conditions.
has_many('pool_posts', array('model_name' => 'PoolPost', 'order' => 'CAST(sequence AS UNSIGNED), post_id')); // , 'conditions' => "pools_posts.active"
has_many('all_pool_posts', array('model_name' => 'PoolPost', 'order' => 'CAST(sequence AS UNSIGNED), post_id'));
// m.versioned :name
// m.versioned :description, :default => ""
// m.versioned :is_public, :default => true
// m.versioned :is_active, :default => true
// m.after_undo :update_pool_links
// m.after_save :expire_cache


class Pool extends ActiveRecord {
  static $_;
  
  function _construct() {
    // vde('a');
    $this->is_public = !empty($this->is_public);
    $this->is_active = !empty($this->is_active);
    // vde($this->is_public);
    // $this->user = new User('find', $this->user_id);
  }
  
  function can_be_updated_by(&$user) {
    return ((bool)$this->is_public || User::$_->has_permission($this));
  }
  
  function get_pool_posts_from_posts(&$posts) {
    // post_ids = posts.map { |post| post.id }
    $post_ids = array();
    foreach($posts as $post)
      $post_ids[] = $post->id;
    
    if(!$post_ids)
      return array();
    // return [] if post_ids.empty?
    
    // vde($post_ids);
    $sql = "SELECT * FROM pools_posts WHERE post_id IN (??)";
    $pool_posts = PoolPost::$_->collection('find_by_sql', $sql, array($post_ids));
    return $pool_posts;
    // vde($pool_posts);
    // return new Collection('PoolPost', 'find_by_sql', $sql, $post_ids);
  }
  
  function get_pools_from_pool_posts($pool_posts) {
    // if(empty_obj($pool_posts))
    if (!(array)$pool_posts)
      return array();
    // vde($pool_posts);
    $pool_ids = array();
    
    foreach($pool_posts as $pp)
      $pool_ids[] = $pp->pool_id;
    
    $pool_ids = array_unique($pool_ids);
    // pool_ids = pool_posts.map { |pp| pp.pool_id }.uniq
    
    if(!$pool_ids)
      return array();
    // return [] if pool_ids.empty?

    $sql = "SELECT p.* FROM pools p WHERE p.id IN (??)";
    // $pools = 
    // $pools = Pool::$_->find_by_sql($sql, array($pool_ids), array('return_array' => true));
    $pools = Pool::$_->find_by_sql($sql, array($pool_ids));
    // vde($pools);
    return $pools;
  }
  
  function pretty_name() {
    return str_replace('_', ' ', $this->name);
  }
  
  function normalize_name() {
    $this->name = str_replace(' ', '_', $this->name);
  }
  
  function get_sample() {
    # By preference, pick the first post (by sequence) in the pool that isn't hidden from
    # the index.
    $pool_post = PoolPost::$_->collection('find', array(
              'order' => "posts.is_shown_in_index DESC, pools_posts.sequence, pools_posts.post_id",
              'joins' => "JOIN posts ON posts.id = pools_posts.post_id",
              'conditions' => array("pool_id = ? AND posts.status = 'active'", $this->id)));
    // $pool_post = new Collection('PoolPost', 'find', array(
              // 'order' => "posts.is_shown_in_index DESC, pools_posts.sequence, pools_posts.post_id",
              // 'joins' => "JOIN posts ON posts.id = pools_posts.post_id",
              // 'conditions' => array("pool_id = ? AND posts.status = 'active'", $this->id)));
               // AND pools_posts.active
    // vde($pool_post);
    
    foreach ($pool_post as $pp) {
      if ($pp->post->can_be_seen_by(User::$current)) {
        return $pp->post;
      }
    }
  }
  
  function has_jpeg_zip() {
    
  }
  
  function can_change_is_public($user) {
    return $user->has_permission($this);
  }

  function can_change($user, $attribute) {
    if (!$user->is('>=20'))
      return false;
    
    elseif ($this->is_public)
      return true;
    elseif ($user->has_permission($this))
      return true;
  }
  
  function add_post($post_id, $options = array()) {
    if (isset($options['user']) && !$this->can_be_updated_by($options['user']))
      throw new Exception('Access Denied');
    
    $seq = isset($options['sequence']) ? $options['sequence'] : $this->next_sequence();
    
    // $pool_post = null;
    $pool_post = $this->all_pool_posts ? $this->all_pool_posts->search('post_id', $post_id) : null;
    
    if ($pool_post) {
      # If :ignore_already_exists, we won't raise PostAlreadyExistsError; this allows
      # he sequence to be changed if the post already exists.
      if ($pool_post->active && empty($options['ignore_already_exists']))
        throw new Exception('Post already exists');
      $pool_post->active = true;
      $pool_post->sequence = $seq;
      $pool_post->save();
    } else {
      
      PoolPost::$_->create(array('pool_id' => $this->id, 'post_id' => $post_id, 'sequence' => $seq));
      // new PoolPost('create', array('pool_id' => $this->id, 'post_id' => $post_id, 'sequence' => $seq));
    }
    
    if (empty($options['skip_update_pool_links'])) {
      $this->reload();
      $this->update_pool_links();
    }
  }
  
  function transfer_post_to_parent($post_id, $parent_id) {
    $pool_post = $this->pool_posts->find('first', array('conditions' => array("post_id = ?", $post_id)));
    $parent_pool_post = $this->pool_posts->find('first', array('conditions' => array("post_id = ?", $parent_id)));
    // return if not parent_pool_post.nil?
    if (!empty($parent_pool_post))
      return;

    $sequence = $pool_post->sequence;
    $this->remove_post($post_id);
    $this->add_post($parent_id, array('sequence' => $sequence));
  }
  
  function update_pool_links() {
    if (!$this->pool_posts)
      return;
    
    $pp = &$this->pool_posts; //(true) # force reload
    
    $count = count((array)$pp);
    
    foreach ($pp as $i => &$v) {
      $v->next_post_id = ($i == $count - 1) ? null : isset($pp->{$i + 1}) ? $pp->{$i + 1}->post_id : null;
      $v->prev_post_id = $i == 0 ? null : isset($pp->{$i - 1}) ? $pp->{$i - 1}->post_id : null;
      $pp->$i->save();
      unset($v);
    }
  }
  
  function next_sequence() {
    $seq = 0;
    
    foreach ($this->pool_posts as $pp) {
      $seq = max(array($seq, $pp->sequence));
    }
    
    return $seq + 1;
  }
  
  function remove_post($post_id, $options = array()) {
    if (!empty($options['user']) && !$this->can_be_updated_by($options['user']))
      throw new Exception('Access Denied');
    
    if (!$pool_post = $this->all_pool_posts->search('post_id', $post_id))
      return;
    
    $pool_post->delete();
    $this->reload(); # saving pool_post modified us
    $this->update_pool_links();
  }
}
?>