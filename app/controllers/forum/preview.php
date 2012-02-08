<?php
render(false);

if (!empty(Request::$params->forum_post)) {
  $preview = true;
  $forum_post = ForumPost::blank(array_merge(request::$params->forum_post, array('creator_id' => User::$current->id)));
  $forum_post->created_at = gmd();
  render_partial("post", array('post' => $forum_post));
}
?>