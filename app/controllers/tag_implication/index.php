<?php
auto_set_params(array('commit', 'query'));
set_title("Tag Implications");
create_page_params();

if (Request::$params->commit == "Search Aliases")
  redirect_to("tag_alias#index", array('query' => Request::$params->query));

if (Request::$params->query) {
  $name = "%" . Request::$params->query . "%";
  $implications = TagImplication::$_->collection('Paginate->found_rows', 'find', array('order' => "is_pending DESC, (SELECT name FROM tags WHERE id = tag_implications.predicate_id), (SELECT name FROM tags WHERE id = tag_implications.consequent_id)", 'per_page' => 20, 'conditions' => array("predicate_id IN (SELECT id FROM tags WHERE name LIKE ?) OR consequent_id IN (SELECT id FROM tags WHERE name LIKE ?)", $name, $name), 'page' => Request::$params->page));
} else {
  $implications = TagImplication::$_->collection('Paginate->found_rows', 'find', array('order' => "is_pending DESC, (SELECT name FROM tags WHERE id = tag_implications.predicate_id), (SELECT name FROM tags WHERE id = tag_implications.consequent_id)", 'per_page' => 20, 'page' => Request::$params->page));
}

calc_pages();
respond_to_list("implications");
?>