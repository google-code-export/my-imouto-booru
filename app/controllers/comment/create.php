<?php
if (User::is('<=20') && Request::$params->commit == "Post" && Comment::count(array('conditions' => array("user_id = ? AND created_at > ?", User::$current->id, gmd_math('sub', '1H'))) >= CONFIG::member_comment_limit)) {
  # TODO: move this to the model
  respond_to_error("Hourly limit exceeded", "#index", array('status' => 421));
}

$user_id = User::$current->id;

Request::$params->comment = array_merge(Request::$params->comment, array('ip_addr' => Request::$remote_ip, 'user_id' => $user_id));

// $comment = new Comment('empty', Request::$params->comment);
$comment = Comment::blank(Request::$params->comment);
// vde(Request::$params->comment);
// vde($comment);
if (Request::$params->commit == "Post without bumping")
  $comment->do_not_bump_post = true;

if ($comment->save())
  respond_to_success("Comment created", "#index");
else
  respond_to_error($comment, "#index");
?>