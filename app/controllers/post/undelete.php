<?php
required_params('id');

$post = Post::$_->find(Request::$params->id);
$post->undelete();

$affected_posts = array($post);
if ($post->parent_id) $affected_posts[] = $post->get_parent();
if (Request::$format == "json" || Request::$format == "xml")
  $api_data = Post::$_->batch_api_data($affected_posts);
else
  $api_data = array();
respond_to_success("Post was undeleted", array('#show', array('id' => Request::$params->id), array('api' => $api_data)));
?>