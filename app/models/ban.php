<?php
before('create', 'save_level');
after('create', 'save_to_record, update_level');
after('destroy', 'restore_level');

class Ban extends ActiveRecord {
  static $_;
  
  function restore_level($user_id, $old_level) {
    // User::$_->update_attribute_by_id($user_id, array('level' => $old_level));
    // User::$_->find($user_id).update_attribute(:level, old_level)
  }
  
  function save_level() {
    $this->old_level = User::$_->find_level($this->user_id);
  }
  
  function update_level($user_id) {
    $user = User::$_->find($user_id);
    $user->level = CONFIG::$user_levels['Blocked'];
    $user->save();
  }
  
  function save_to_record() {
    // UserRecord.create(:user_id => self.user_id, :reported_by => self.banned_by, :is_positive => false, :body => "Blocked: #{self.reason}")
  }
  
  function duration($dur) {
    // $this->expires_at = (dur.to_f * 60*60*24).seconds.from_now
    // @duration = dur
  }
  
  // def duration
    // @duration
  // end
}
?>