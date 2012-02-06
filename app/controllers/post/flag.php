<?php
required_params('id');
auto_set_params('reason');

if (!$post = Post::find(Request::$params->id))
  exit_with_status(404);

if (!empty(Request::$params->unflag)) {
  # Allow the user who flagged a post to unflag it.
  #
  # posts 
  # "approve" is used both to mean "unflag post" and "approve pending post".
  if ($post->status != "flagged")
    respond_to_error("Can only unflag flagged posts", array("#show", 'id' => Request::$params->id));

  if (!User::is('>=40') and User::$current->id != $post->flag_detail->user_id)
    access_denied();

  $post->approve(User::$current->id);
  $message = "Post approved";
} else {
  if ($post->status != "active")
    respond_to_error("Can only flag active posts", array("#show", 'id' => Request::$params->id));

  $post->flag(Request::$params->reason, User::$current->id);
  $message = "Post flagged";
}

# Reload the post to pull in post.flag_reason.
$post->reload();

if (Request::$format == "json" || Request::$format == "xml")
  $api_data = Post::batch_api_data(array($post));
respond_to_success($message, array("#show", 'id' => Request::$params->id), array('api' => $api_data));
?>