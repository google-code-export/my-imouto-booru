<?php
belongs_to('post');
belongs_to('user');
before('save', 'blank_body');
after('save', 'update_post');

// acts_as_versioned :order => "updated_at DESC"

class Note extends ActiveRecord {
  static $_;
  
  function blank_body() {
    if (empty($this->body))
      $this->body = "(empty)";
  }

  # TODO: move this to a helper
  function formatted_body() {
    return $this->body = nl2br(preg_replace('/<tn>(.+?)<\/tn>/m', '<br /><p class="tn">\1</p>', $this->body));
  }
  
  function update_post() {
    $active_notes = DB::select_value("1 FROM notes WHERE is_active AND post_id = ? LIMIT 1", $this->post_id);
    
    if ($active_notes)
      DB::update("posts SET last_noted_at = ? WHERE id = ?", $this->updated_at, $this->post_id);
    else
      DB::update("posts SET last_noted_at = ? WHERE id = ?", null, $this->post_id);
  }
  
  function is_locked() {
    return (bool)DB::select_value("1 FROM posts WHERE id = ? AND is_note_locked = ?", $this->post_id, true);
  }
}
?>