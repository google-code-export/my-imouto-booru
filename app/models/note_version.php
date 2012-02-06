<?php
belongs_to('author', array('model_name' => 'User'));

class NoteVersion extends ActiveRecord {
  
  function to_xml($options = array()) {
    // {:created_at => created_at, :updated_at => updated_at, :creator_id => user_id, :x => x, :y => y, :width => width, :height => height, :is_active => is_active, :post_id => post_id, :body => body, :version => version}.to_xml(options.reverse_merge(:root => "note_version"))
  }

  function to_json($args = null) {
    return to_json(array('created_at' => $this->created_at, 'updated_at' => $this->updated_at, 'creator_id' => $this->user_id, 'x' => $this->x, 'y' => $this->y, 'width' => $this->width, 'height' => $this->height, 'is_active' => $this->is_active, 'post_id' => $this->post_id, 'body' => $this->body, 'version' => $this->version));
  }
}
?>