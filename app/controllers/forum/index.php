<?php
set_title(CONFIG::app_name . " Forum");
create_page_params();
auto_set_params(array('query', 'parent_id'));

if (isset(request::$params->parent_id)) {
  $forum_posts = ForumPost::find_all(array('order' => "is_sticky desc, updated_at DESC", 'per_page' => 100, 'conditions' => array("parent_id = ?", request::$params->parent_id), 'page' => request::$params->page));
} else {
  $forum_posts = ForumPost::find_all(array('order' => "is_sticky desc, updated_at DESC", 'per_page' => 30, 'conditions' => array("parent_id IS NULL"), 'page' => request::$params->page));
}
calc_pages();

respond_to_list($forum_posts);
?>