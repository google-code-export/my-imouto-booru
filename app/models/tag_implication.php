<?php
belongs_to('predicate', array('model_name' => 'Tag'));
belongs_to('consequent', array('model_name' => 'Tag'));
before('create', 'validate_uniqueness');

class TagImplication extends ActiveRecord {
  static $_;
  
  function validate_uniqueness() {
    $this->predicate_is($this->predicate);
    $this->consequent_is($this->consequent);
    
    if (self::$_->find('first', array('conditions' => array("(predicate_id = ? AND consequent_id = ?) OR (predicate_id = ? AND consequent_id = ?)", $this->predicate_id, $this->consequent_id, $this->consequent_id, $this->predicate_id)))) {
      $this->record_errors->add_to_base("Tag implication already exists");
      return false;
    }
  }
  
  function predicate_is($name) {
    $t = Tag::$_->find_or_create_by_name($name);
    $this->predicate_id = $t->id;
  }

  function consequent_is($name) {
    $t = Tag::$_->find_or_create_by_name($name);
    $this->consequent_id = $t->id;
  }
  
  function destroy_and_notify($current_user, $reason) {
    # TODO:
    if (!empty($this->creator_id) && $this->creator_id != $current_user->id) {
      // msg = "A tag implication you submitted (#{predicate.name} &rarr; #{consequent.name}) was deleted for the following reason: #{reason}."
      
      // Dmail.create(:from_id => current_user.id, :to_id => creator_id, :title => "One of your tag implications was deleted", :body => msg)
    }
    
    $this->destroy();
  }
  
  function approve($user_id, $ip_addr) {
    DB::update("tag_implications SET is_pending = FALSE WHERE id = {$this->id}");
    
    $t = Tag::$_->find($this->predicate_id);
    $implied_tags = implode(' ', $this->with_implied(array($t->name)));
    
    foreach (Post::$_->find('all', array('conditions' => array("id IN (SELECT pt.post_id FROM posts_tags pt WHERE pt.tag_id = ?)", $t->id))) as $post) {
      $post->update_attributes(array('tags' => $post->tags . " " . $implied_tags, 'updater_user_id' => $user_id, 'updater_ip_addr' => $ip_addr));
    }
  }
  
  function with_implied($tags) {
    if (!$tags)
      return array();
    
    $all = array();

    foreach ($tags as $tag) {
      $all[] = $tag;
      $results = array($tag);

      foreach(range(1, 10) as $i) {
        // $results = DB::select_values(sanitize_sql([<<-SQL, results]))
          // SELECT t1.name 
          // FROM tags t1, tags t2, tag_implications ti 
          // WHERE ti.predicate_id = t2.id 
          // AND ti.consequent_id = t1.id 
          // AND t2.name IN (?)
          // AND ti.is_pending = FALSE
        // SQL
        // DB::show_query();
        // vd($results);
        $results = DB::select_row('
          t1.name 
          FROM tags t1, tags t2, tag_implications ti 
          WHERE ti.predicate_id = t2.id 
          AND ti.consequent_id = t1.id 
          AND t2.name IN (??)
          AND ti.is_pending = FALSE
        ', $results);
        
        if (is_array($results)) {
          $results = array_values($results);
          $all = array_merge($all, $results);
        } else
          break;
      }
    }
    
    return $all;
  }
}
?>