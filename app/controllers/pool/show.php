<?php
create_page_params(true);
required_params('id');

if (isset(Request::$params->samples) && Request::$params->samples == 0)
  unset(Request::$params->samples);

$pool = Pool::$_->find(array(Request::$params->id));

if (!$pool)
  die_404();

$browse_mode = User::$current->pool_browse_mode;

// $q = Tag::$_->parse_query("");
// vde($q);
$q = array();
$q['pool'] = (int)Request::$params->id;
$q['show_deleted_only'] = false;
if ($browse_mode == 1) {
  $q['limit'] = 1000;
  $q['order'] = "portrait_pool";
} else {
  $q['limit'] = 24;
}

// $count = Post::$_->count_by_sql(Post::$_->generate_sql($q, array('from_api' => true, 'count' => true)));

// $posts = new Collection('Paginate->found_posts', 'Post', array());
// WillPaginate::Collection.new(params[:page], q[:limit], count)

$sql = Post::$_->generate_sql($q, array('from_api' => true, 'offset' => $offset, 'limit' => $pagination_limit));
// vde($params);
// vde($sql);
// $posts = new Collection('Paginate->found_posts', 'Post', 'find_by_sql', $sql);
$posts = Post::$_->collection('Paginate->found_posts', 'find_by_sql', $sql);

calc_pages();

set_title($pool->pretty_name());

switch (Request::$format) {
  case 'json':
    render('json', $pool->to_json());
  break;
  
  case 'xml':
  break;
}

// respond_to(array(
  // 'html' => null,
  // 'xml' => null,
    // builder = Builder::XmlMarkup.new(:indent => 2)
    // builder.instruct!

    // xml = @pool.to_xml(:builder => builder, :skip_instruct => true) do
      // builder.posts do
        // @posts.each do |post|
          // post.to_xml(:builder => builder, :skip_instruct => true)
        // end
      // end
    // end
    // render :xml => xml
  // end
  // 'json' => array('render' => array('json', $pool->to_json()))
// ));
?>