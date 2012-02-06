<?php
create_page_params(true);
$tags = isset(Request::$params->tags) ? Request::$params->tags : null;
$split_tags = $tags ? explode(' ', $tags) : array();

#    if @current_user.is_member_or_lower? && split_tags.size > 2
#      respond_to_error("You can only search up to two tags at once with a basic account", :action => "error")
#      return
#    elsif split_tags.size > 6
if (count($split_tags) > 6)
  respond_to_error("You can only search up to six tags at once", "#error");

$q = Tag::parse_query($tags);

if (!empty(Request::$params->limit))
  $limit = (int)Request::$params->limit;
elseif (!empty($q['limit']))
  $limit = (int)$q['limit'];
else
  $limit = 16;
$limit > 1000 && $limit == 1000;

$count = 0;

// begin
  // count = Post.fast_count(tags)
// rescue => x
  // respond_to_error("Error: #{x}", :action => "error")
  // return
// end

set_title('/'.str_replace('_', ' ', $tags));

$tag_suggestions = array();
// if ($count < 16 && count($split_tags) == 1)
  // $tag_suggestions = Tag::find_suggestions($tags);

$ambiguous_tags = array();
// $ambiguous_tags = Tag::select_ambiguous($split_tags);

$searching_pool = (isset($q['pool']) && is_int($q['pool'])) ? Pool::find_by_id($q['pool']) : null;

$from_api = Request::$format == "json" || Request::$format == "xml";

// @posts = WillPaginate::Collection.new(page, limit, count)
// offset = @posts.offset
// posts_to_load = @posts.per_page

if (!$from_api) {
  # For forward preloading:
  // posts_to_load += @posts.per_page

  # If we're not on the first page, load the previous page for prefetching.  Prefetching
  # the previous page when the user is scanning forward should be free, since it'll already
  # be in cache, so this makes scanning the index from back to front as responsive as from
  # front to back.
  // if page and page > 1 then
    // offset -= @posts.per_page
    // posts_to_load += @posts.per_page
  // end
}

$showing_holds_only = !empty($q['show_holds']) && $q['show_holds'] == 'only';
$sql = Post::generate_sql($q, array('original_query' => $tags, 'from_api' => $from_api, 'order' => "p.id DESC", 'offset' => $offset, 'limit' => $limit));

$results = Post::find_by_sql(array($sql), array('calc_rows' => 'found_posts'));

$preload = array();
// if not from_api then
  // if page && page > 1 then
    // @preload = results[0, limit] || []
    // results = results[limit..-1] || []
  // end
  // @preload += results[limit..-1] || []

  // results = results[0..limit-1]
// end

# Apply can_be_seen_by filtering to the results.  For API calls this is optional, and
# can be enabled by specifying filter=1.
if (!$from_api or (isset(Request::$params->filter) && Request::$params->filter == "1")) {
  foreach ($results as $k => $post) {
    if (!$post->can_be_seen_by(User::$current, array('show_deleted' => true)))
      unset($results->$k);
  }
  // @preload = @preload.delete_if { |post| not post.can_be_seen_by?(@current_user) }
}

if ($from_api and (isset(Request::$params->api_version) && Request::$params->api_version == "2") and Request::$format != "json")
  respond_to_error("V2 API is JSON-only", array(), array('status' => 424));

// @posts.replace(results)
$posts = $results;
unset($results);

switch (Request::$format) {
  case 'json':
    if (empty(Request::$params->api_version) || Request::$params->api_version != "2") {
      render('json', to_json(array_map(function($p){return $p->api_attributes();}, (array)$posts)));
      return;
    }

    $api_data = Post::batch_api_data($posts, array(
      'exclude_tags' => !empty(Request::$params->include_tags) ? false : true,
      'exclude_votes' => !empty(Request::$params->include_votes) ? false : true,
      'exclude_pools' => !empty(Request::$params->include_pools) ? false : true,
    ));

    render('json', to_json($api_data));
  break;
  
  case 'xml':
    ActionView::$layout = false;
    return;
  break;
}

if (!empty($split_tags))
  $tags = Tag::parse_query($tags);
else {
  $tags['include'] = Tag::count_by_period(gmd_math('sub', '1D'), gmd(), array('limit' => 25, 'exclude_types' => CONFIG::$exclude_from_tag_sidebar));
}

calc_pages();
?>