<?php
set_title("Tag Aliases");
create_page_params();

auto_set_params(array('commit', 'query'));

if (Request::$params->commit == "Search Implications")
  redirect_to('tag_implication#index', array('query' => Request::$params->query));

if (Request::$params->query) {
  $name = "%" . Request::$params->query . "%";
  $aliases = TagAlias::find_all(array('order' => "is_pending DESC, name", 'per_page' => 20, 'conditions' => array("name LIKE ? OR alias_id IN (SELECT id FROM tags WHERE name LIKE ?)", $name, $name), 'page' => Request::$params->page));
} else
  $aliases = TagAlias::find_all(array('order' => "is_pending DESC, name", 'per_page' => 20, 'page' => Request::$params->page));

respond_to_list($aliases);

calc_pages();
?>