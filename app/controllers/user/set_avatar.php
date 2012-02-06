<?php
required_params('id');

$user = User::$current;
if (!empty(Request::$params->user_id)) {
  $user = User::find(Request::$params->user_id);
  if (!$user)
    respond_to_error("Not found", "#index", array('status' => 404));
}

if (!$user->is_anonymous && !User::$current->has_permission($user, 'id'))
  access_denied();

if (Request::$post) {
  if ($user->set_avatar((array)Request::$params))
    redirect_to("#show", array('id' => $user->id));
  else
    respond_to_error($user, "#home");
}

if (!$user->is_anonymous && Request::$params->id && Request::$params->id == $user->avatar_post_id)
  $old = Request::$params;

$params = Request::$params;
$post = Post::find(Request::$params->id);

if (!$post)
  exit_with_status(400);
?>