<?php
create_page_params();

if (request::$params->query) {
  $query = '%' . str_replace(' ', '%', request::$params->query) . '%';
  $forum_posts = ForumPost::find_all(array('order' => "id desc", 'per_page' => 30, 'conditions' => array('title LIKE ? OR body LIKE ?', $query, $query), 'page' => request::$params->page));
} else
  $forum_posts = ForumPost::find_all(array('order' => "id desc", 'per_page' => 30, 'page' => request::$params->page));
calc_pages();

respond_to_list("forum_posts");
?>