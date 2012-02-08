<?php
auto_set_params(array('type', 'parent_id'));
$forum_post = ForumPost::blank();

if (request::$params->parent_id)  
  $forum_post->parent_id = request::$params->parent_id;

if (request::$params->type == "alias") {
  $forum_post->title = "Tag Alias: ";
  $forum_post->body = "Aliasing ___ to ___.\n\nReason: ";
} elseif (request::$params->type == "impl") {
  $forum_post->title = "Tag Implication: ";
  $forum_post->body = "Implicating ___ to ___.\n\nReason: ";
}
?>