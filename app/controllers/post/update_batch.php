<?php
$user_id = User::$current->id;

$ids = array();
foreach (Request::$params->post as $post) {
  if (isset($post[0])) {
    # We prefer { :id => 1, :rating => 's' }, but accept ["123", {:rating => 's'}], since that's
    # what we'll get from HTML forms.
    $post_id = $post[0];
    $post = $post[1];
  } else {
    $post_id = $post['id'];
    unset($post['id']);
  }

  $p = Post::find($post_id);
  $ids[] = $p->id;
  
  # If an entry has only an ID, it was just included in the list to receive changes to
  # a post without changing it (for example, to receive the parent's data after reparenting
  # a post under it).
  if (empty($post)) continue;

  $old_parent_id = $p->parent_id;

  Post::filter_api_changes($post);
  
  if ($p->update_attributes(array_merge($post, array('updater_user_id' => $user_id, 'updater_ip_addr' => Request::$remote_ip)))) {
    // post.merge(:updater_user_id => user_id, :updater_ip_addr => request.remote_ip))
    # Reload the post to send the new status back; not all changes will be reflected in
    # @post due to after_save changes.
    // $p->reload();
  }

  if ($p->parent_id != $old_parent_id) {
    $p->parent_id && $ids[] = $p->parent_id;
    $old_parent_id && $ids[] = $old_parent_id;
  }
}

# Updates to one post may affect others, so only generate the return list after we've already
# updated everything.
# TODO: need better SQL functions.
$ids = implode(', ', $ids);

$posts = Post::find_all(array('conditions' => array("id IN ($ids)")));
$api_data = Post::batch_api_data($posts);

$url = !empty(Request::$params->url) ? Request::$params->url : '#index';
// $url = Request::$params->url;
// $url = {:action => "index"} if not url
respond_to_success("Posts updated", $url, array('api' => $api_data));
?>