<?php
if (!$forum_post = ForumPost::find(request::$params->id))
  return 404;

if (User::$current->has_permission($forum_post, 'creator_id')) {
  $forum_post->destroy();
  notice("Post destroyed");

  if ($forum_post->is_parent)
    redirect_to("#index");
  else
    redirect_to("#show", array('id' => $forum_post->root_id));
} else {
  notice("Access denied");
  redirect_to("#show", array('id' => $forum_post->root_id));
}
?>