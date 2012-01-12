<?php
empty(Request::$params->id) && access_denied();
$comment = Comment::$_->find(Request::$params->id);
if (User::$current->has_permission($comment)) {
  $comment->destroy();
  respond_to_success("Comment deleted", array('post#show', 'id' => $comment->post_id));
} else
  access_denied();
?>