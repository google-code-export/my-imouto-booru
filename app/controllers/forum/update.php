<?php
if (!$forum_post = ForumPost::find(request::$params->id))
  return 404;

if (!User::$current->has_permission($forum_post, 'creator_id'))
  access_denied();

$forum_post->add_attributes(request::$params->forum_post);
if ($forum_post->save()) {
  notice("Post updated");
  redirect_to("#show", array('id' => $forum_post->root_id, 'page' => ceil($forum_post->root->response_count / 30.0)));
} else
  render_error($forum_post);
?>