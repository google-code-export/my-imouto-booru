<?php
required_params('implications', 'commit');
auto_set_params('reason');

$ids = array_keys(Request::$params->implications);

switch(Request::$params->commit) {
  case "Delete":
    $can_delete = true;
    
    # Dunno where 'creator_id' comes from.
    foreach ($ids as $x) {
      $ti = TagImplication::$_->find($x);
      // $can_delete = ($ti->is_pending && $ti->creator_id == User::$current->id);
      $tis[] = $ti;
    }
    
    if (User::$current->is('>=40') && $can_delete) {
      foreach ($tis as $ti)
        $ti->destroy_and_notify(User::$current, Request::$params->reason);
    
      notice("Tag implications deleted");
      redirect_to("#index");
    } else
      access_denied();
  break;
  
  case "Approve":
    if (User::$current->is('>=40')) {
      foreach ($ids as $x) {
        if (CONFIG::enable_asynchronous_tasks) {
          // JobTask.create(:task_type => "approve_tag_implication", :status => "pending", :data => {"id" => x, "updater_id" => @current_user.id, "updater_ip_addr" => request.remote_ip})
        } else {
          $ti = TagImplication::$_->find($x);
          $ti->approve(User::$current, Request::$remote_ip);
        }
      }
      
      notice("Tag implication approval jobs created");
      redirect_to('job_task#index');
    } else
      access_denied();
  break;
}
?>