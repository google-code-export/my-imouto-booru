<?php
auto_set_params(array('forum_post' => array()));

$forum_post = ForumPost::create(array_merge(request::$params->forum_post, array('creator_id' => user::$current->id)));

if ($forum_post->record_errors->blank()) {
  if (empty(request::$params->forum_post['parent_id'])) {
    notice("Forum topic created");
    redirect_to("#show", array('id' => $forum_post->root_id));
  } else {
    notice("Response posted");
    redirect_to("#show", array('id' => $forum_post->root_id, 'page' => ceil($forum_post->root->response_count / 30)));
  }
} else
  render_error($forum_post);
?>