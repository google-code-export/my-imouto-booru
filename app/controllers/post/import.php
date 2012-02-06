<?php
$import_dir = ROOT . 'public/data/import/';

if (Request::$post) {

  if (Request::$format == 'json') {
    foreach (explode('::', Request::$params->dupes) as $file) {
      $file = utf8_decode($file);
      $file = $import_dir . $file;
      if (file_exists($file))
        unlink($file);
      else
        $error = true;
    }
    
    $resp = !empty($error) ? array('reason' => 'Some files could not be deleted') : array('success' => true);
    render('json', $resp);
  }

  layout(false);
  
  $errors = $dupe = false;
  
  Request::$params->post['filename'] = utf8_decode(Request::$params->post['filename']);
  
  $filepath = $import_dir . Request::$params->post['filename'];
  
  Request::$params->post = array_merge(Request::$params->post, array(
    // 'updater_user_id' => User::$current->id,
    // 'updater_ip_addr' => Request::$remote_ip,
    // 'ip_addr'         => Request::$remote_ip,
    'user_id'         => User::$current->id,
    'status'          => 'active',
    'tempfile_path'   => $filepath,
    'tempfile_name'   => Request::$params->post['filename']
  ));
  
  $post = Post::create(Request::$params->post);
  
  if ($post->record_errors->blank()) {
    $status = 'Posted';
    
  } elseif ($post->record_errors->invalid('md5')) {
    $post = Post::find_by_md5($post->md5);
    $post->status = 'flagged';
    $dupe = true;
    $status = 'Already exists';
    
  } else {
    $errors = implode('<br />', $post->record_errors->full_messages());
    $status = 'Error';
  }
  
} else {
  set_title('Import');
  $invalid_files = $files = array();

  if ($fh = opendir($import_dir)) {
    while (false !== ($file = readdir($fh))) {
      if ($file == '.' || $file == '..')
        continue;
      
      if (is_int(strpos($file, '?'))) {
        $invalid_files[] = $file;
        continue;
      }
      
     $files[] = utf8_encode($file);
    }
    closedir($fh);
  }

  $pools = Pool::find_all_name_by_is_active(1);
  
  if ($pools) {
    $pool_list = '<datalist id="pool_list">';
    foreach ($pools as $pool)
      $pool_list .= '<option value="' . str_replace('_', ' ', $pool['name']) . '" />';
    $pool_list .= '</datalist>';
  } else
    $pool_list = null;

  $chkbox_e = $chkbox_q = $chkbox_s = '';
  CONFIG::default_rating_import == 'e' && $chkbox_e = ' checked="1"';
  CONFIG::default_rating_import == 'q' && $chkbox_q = ' checked="1"';
  CONFIG::default_rating_import == 's' && $chkbox_s = ' checked="1"';
}
?>