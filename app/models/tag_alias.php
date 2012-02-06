<?php
$table_name = 'tag_aliases';
before('create', 'normalize, validate_uniqueness, generate_alias_id');

class TagAlias extends ActiveRecord {
  
  # Strips out any illegal characters and makes sure the name is lowercase.
  function normalize() {
    $this->name = trim(str_replace(' ', '_', strtolower($this->name)), '-~');
  }

  # Makes sure the alias does not conflict with any other aliases.
  function validate_uniqueness() {
    if ($this->exists("name = ?", $this->name)) {
      $this->record_errors->add_to_base("{$this->name} is already aliased to something");
      return false;
    }
    
    if ($this->exists("alias_id = (select id from tags where name = ?)", $this->name)) {
      $this->record_errors->add_to_base("{$this->name} is already aliased to something");
      return false;
    }
    
    if ($this->exists("name = ?", $this->alias_name)) {
      $this->record_errors->add_to_base("{$this->alias_name} is already aliased to something");
      return false;
    }
  }
  
  function generate_alias_id() {
    if (empty($this->alias)) {
      return false;
    }
    $this->alias($this->alias);
  }
  
  function alias($name) {
    $alias_tag = Tag::find_or_create_by_name($name);
    $tag = Tag::find_or_create_by_name($this->name);
    
    if ($alias_tag->tag_type != $tag->tag_type)
      $alias_tag->update_attribute('tag_type', $tag->tag_type);
    
    $this->alias_id = $alias_tag->id;
  }
  
  static function to_aliased($tags) {
    check_array($tags);
    $aliased_tags = array();
    foreach ($tags as $tag_name)
      $aliased_tags[] = self::to_aliased_helper($tag_name);
    
    return $aliased_tags;
  }
  
  static function to_aliased_helper($tag_name) {
    # TODO: add memcached support
    $tag = self::find_first(array('select' => "tags.name AS name", 'joins' => "JOIN tags ON tags.id = tag_aliases.alias_id", 'conditions' => array("tag_aliases.name = ? AND tag_aliases.is_pending = FALSE", $tag_name)));
    return isset($tag['name']) ? $tag['name'] : $tag_name;
  }
  
  function alias_name() {
    if (isset($this->alias_name))
      return $this->alias_name;
    
    $name = Tag::find_name(array('conditions' => array('id = ?', $this->alias_id)));
    $this->alias_name = $name;
    // vde($name);
    return $this->alias_name;
  }
  
  function alias_tag() {
    return Tag::find_or_create_by_name($this->name);
  }
  
  # Destroys the alias and sends a message to the alias's creator.
  #TODO:
  function destroy_and_notify($current_user, $reason) {
    if ($this->creator_id && $this->creator_id != $current_user->id) {
      $msg = "A tag alias you submitted ({$this->name} &rarr; " . $this->alias_name() . ") was deleted for the following reason: {$this->reason}.";
      // Dmail.create(:from_id => current_user.id, :to_id => creator_id, :title => "One of your tag aliases was deleted", :body => msg)
    }
    
    $this->destroy();
  }
  
  function approve($user_id, $ip_addr) {
    // DB::show_query();
    DB::update("tag_aliases SET is_pending = FALSE WHERE id = ?", $this->id);
    // exit;
    
    // Post.find(:all, :conditions => ["tags_index @@ to_tsquery('danbooru', ?)", QueryParser.generate_sql_escape(name)]).each do |post|
      // post.reload
      // post.update_attributes(:tags => post.cached_tags, :updater_user_id => user_id, :updater_ip_addr => ip_addr)
    // end

    // Cache.expire_tag_version
  }
}
?>