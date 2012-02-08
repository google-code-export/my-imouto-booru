<?php
if (!$forum_post = ForumPost::find(request::$params->id))
  return 404;

if (!User::$current->has_permission($forum_post, 'creator_id'))
  access_denied();
?>