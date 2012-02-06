<?php
if (Request::$params->commit == "Cancel")
  redirect_to('#show', array('id' => Request::$params->id));

$post = Post::find(Request::$params->id);

if (!$post)
  respond_to_error('Post doesn\'t exist', array('#show', array('id' => Request::$params->id)));

if (!$post->can_user_delete(User::$current))
  access_denied();

if ($post->status == "deleted") {
  if (!empty(Request::$params->destroy)) {
    $post->delete_from_database();
    respond_to_success("Post deleted permanently", array("#show", array('id' => Request::$params->id)));
  } else
    respond_to_success("Post already deleted", array("#delete", array('id' => Request::$params->id)));
} else {
  Post::static_destroy_with_reason($post->id, Request::$params->reason, User::$current);
  
  # Destroy in one request.
  if (!empty(Request::$params->destroy)) {
    $post->delete_from_database();
    respond_to_success("Post deleted permanently", array("#show", array('id' => Request::$params->id)));
  }
  
  respond_to_success("Post deleted", array("#show", array('id' => Request::$params->id)));
}
  
?>