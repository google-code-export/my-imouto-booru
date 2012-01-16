<?php
required_params('id');

$post = Post::$_->find(Request::$params->id);

if ($post) {
  if ($post->parent_id)
    $post_parent = Post::$_->find($post->parent_id);
  else
    $post_parent = null;
} else {
  render("#show_empty", array('status' => 404));
}  
?>