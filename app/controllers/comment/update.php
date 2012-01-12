<?php
if (!$comment = Comment::$_->find(Request::$params->id))
  exit;

if (User::$current->has_permission($comment)) {
  $comment->update_attributes(Request::$params->comment);
  respond_to_success("Comment updated", "#index");
} else
  access_denied();
?>