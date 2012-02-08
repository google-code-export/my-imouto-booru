<?php
create_page_params(true);
required_params('id');

if (isset(Request::$params->samples) && Request::$params->samples == 0)
  unset(Request::$params->samples);

$pool = Pool::find(array(Request::$params->id));

if (!$pool)
  return 404;

$browse_mode = User::$current->pool_browse_mode;

// $q = Tag::parse_query("");

$q = array();
$q['pool'] = (int)Request::$params->id;
$q['show_deleted_only'] = false;
if ($browse_mode == 1) {
  $q['limit'] = 1000;
  $q['order'] = "portrait_pool";
} else {
  $q['limit'] = 24;
}

// $count = Post::count_by_sql(Post::generate_sql($q, array('from_api' => true, 'count' => true)));

// WillPaginate::Collection.new(params[:page], q[:limit], count)

$sql = Post::generate_sql($q, array('from_api' => true, 'offset' => $offset, 'limit' => $pagination_limit));

$posts = Post::find_by_sql(array($sql), array('calc_rows' => 'found_posts'));

calc_pages();

set_title($pool->pretty_name());

switch (Request::$format) {
  case 'json':
    render('json', $pool->to_json());
  break;
  
  case 'xml':
  break;
}
?>