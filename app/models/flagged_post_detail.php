<?php
belongs_to('post');
belongs_to('user');

class FlaggedPostDetail extends ActiveRecord {
  static $_;
  
  function _construct() {
    // if ($this->found()) {
      // $this->author = $this->user->name;
    // }
    $this->create_api_attributes();
  }
  
  function flagged_by() {
    if (empty($this->flagged_by)) {
      if (empty($this->user_id))
        $this->flagged_by = "system";
      else
        $this->flagged_by = (User::$_->find_name($this->user_id));
    }
    
    return $this->flagged_by;
  }
  
  private function create_api_attributes() {
    $api_attributes = array('post_id', 'reason', 'created_at', 'user_id', 'flagged_by');
    
    $this->api_attributes = array_fill_keys($api_attributes, null);
    
    foreach (array_keys($this->api_attributes) as $attr) {
      if ($attr == 'flagged_by') {
        $this->flagged_by();
      } elseif (!empty($this->$attr))
        $this->api_attributes[$attr] = &$this->$attr;
    }
    // vde($this->flagged_by);
    // $this->api_attributes = array(
      // 'post_id' => $this->post_id,
      
    // );

    // ret = {
      // :post_id => post_id,
      // :reason => reason,
      // :created_at => created_at,
    // }

    // if not hide_user then
      // ret[:user_id] = user_id
      // ret[:flagged_by] = flagged_by
    // end

    // return ret
  }
}
?>