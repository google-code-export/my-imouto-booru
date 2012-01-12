<?php
// vde(Request::$params);
required_params(array('id', 'post'));

if (!$post = Post::$_->find(Request::$params->id)) {
  render("#show_empty", array('status' => 404));
  return;
}

Post::$_->filter_api_changes(Request::$params->post);

Request::$params->post['updater_user_id'] = User::$current->id;
Request::$params->post['updater_ip_addr'] = Request::$remote_ip;

if ($post->update_attributes(Request::$params->post)) {
  # Reload the post to send the new status back; not all changes will be reflected in
  # @post due to after_save changes.
  // $post->reload();

  $api_data = Request::$format == "json" || Request::$format == "xml" ? $post->api_data() : array();
  respond_to_success("Post updated", array('#show', array('id' => $post->id, 'tag_title' => $post->tag_title())), $api_data);
} else
  respond_to_error($post, array('#show', array('id' => Request::$params->id)));
?>