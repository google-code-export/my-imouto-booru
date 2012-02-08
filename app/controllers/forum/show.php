<?php
if (!$forum_post = ForumPost::find(request::$params->id))
  return 404;

include_model('comment');
create_page_params();

set_title($forum_post->title);
$children = ForumPost::find_all(array('order' => "id", 'per_page' => 30, 'conditions' => array("parent_id = ?", request::$params->id), 'page' => request::$params->page));

if (!User::$current->is_anonymous && User::$current->last_forum_topic_read_at < $forum_post->updated_at && $forum_post->updated_at < gmd_math('sub', 'T3S'))
  User::$current->update_attribute('last_forum_topic_read_at', $forum_post->updated_at);
calc_pages();

respond_to_list($forum_post);
?>