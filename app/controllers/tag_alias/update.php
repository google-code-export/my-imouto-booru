<?php
required_params('aliases', 'commit');
auto_set_params('reason');

$ids = array_keys(Request::$params->aliases);

switch (Request::$params->commit) {
  case "Delete":
    $validate_all = true;
    
    foreach ($ids as $id) {
      $ta = TagAlias::$_->find($id);
      if (!$ta->is_pending || $ta->creator_id != User::$current->id) {
        $validate_all = false;
        break;
      }
    }
    
    if (User::$current->is('>=40') || $validate_all) {
      foreach ($ids as $x) {
        $ta = TagAlias::$_->find($x);
        $ta->destroy_and_notify(User::$current, Request::$params->reason);
      }
    
      notice("Tag aliases deleted");
      redirect_to("#index");
    } else
      access_denied();
  break;

  case "Approve":
    if (User::$current->is('>=40')) {
      foreach ($ids as $x) {
        // if (CONFIG::enable_asynchronous_tasks) {
          // JobTask.create(:task_type => "approve_tag_alias", :status => "pending", :data => {"id" => x, "updater_id" => @current_user.id, "updater_ip_addr" => request.remote_ip})
        // } else {
        // vde($x);
          // DB::show_query();
          $ta = TagAlias::$_->find($x);
          // vde($ta);
          // exit;
          $ta->approve(User::$current->id, Request::$remote_ip);
        // }
      }
      
      notice("Tag alias approval jobs created");
      #TODO: redirecting to #index instead of job_task#index as job_task doesn't yet exist.
      redirect_to('#index');
    } else
      access_denied();
  break;
}
?>