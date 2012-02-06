<?php
create_page_params();
set_title('Pools');

$options = array( 
  'per_page' => 20,
  'page' => Request::$params->page
);

$order = !empty(Request::$params->order) ? Request::$params->order : 'id';

$conds = array();
$cond_params = array();

$search_tokens = array();

if (!empty(Request::$params->query)) {
  set_title(Request::$params->query . " - Pools");
  // $query = array_map(function($v){return addslashes($v);}, explode(Request::$params->query);
  // $query = Tokenize.tokenize_with_quotes(params[:query] || "")
  $query = explode(' ', addslashes(Request::$params->query));

  foreach ($query as &$token) {
    if (preg_match('/^(order|limit|posts):(.+)$/', $token, $m)) {
      if ($m[1] == "order") {
        $order = $m[2];
      } elseif ($m[1] == "limit") {
        $options['per_page'] = (int)$m[2];
        $options['per_page'] = min($options['per_page'], 100);
      } elseif ($m[1] == "posts") {
        
        Post::generate_sql_range_helper(Tag::parse_helper($m[2]), "post_count", $conds, $cond_params);
      }
    } else {
      // # TODO: removing ^\w- from token.
      // $token = preg_replace('~[^\w-]~', '', $token);
      $search_tokens[] = $token;
    }
  }
}

if (!empty($search_tokens)) {
  // $value_index_query = QueryParser.escape_for_tsquery($search_tokens);
  $value_index_query = implode('_', $search_tokens);
  if ($value_index_query) {
    // $conds[] = "search_index @@ to_tsquery('pg_catalog.english', ?)";
    // $cond_params[] = implode(' & ', $value_index_query);

    # If a search keyword contains spaces, then it was quoted in the search query
    # and we should only match adjacent words.  tsquery won't do this for us; we need
    # to filter results where the words aren't adjacent.
    #
    # This has a side-effect: any stopwords, stemming, parsing, etc. rules performed
    # by to_tsquery won't be done here.  We need to perform the same processing as
    # is used to generate search_index.  We don't perform all of the stemming rules, so
    # although "jump" may match "jumping", "jump beans" won't match "jumping beans" because
    # we'll filter it out.
    #
    # This also doesn't perform tokenization, so some obscure cases won't match perfectly;
    # for example, "abc def" will match "xxxabc def abc" when it probably shouldn't.  Doing
    # this more correctly requires Postgresql support that doesn't exist right now.
    foreach ($query as $q) {
      # Don't do this if there are no spaces in the query, so we don't turn off tsquery
      # parsing when we don't need to.
      // if (!strstr($q, ' ')) continue;
      // $conds[] = "(position(LOWER(?) IN LOWER(replace_underscores(name))) > 0 OR position(LOWER(?) IN LOWER(description)) > 0)";
      #TODO: binding.
      $conds[] = "(position(LOWER(?) IN LOWER(REPLACE(name, '_', ' '))) > 0 OR position(LOWER(?) IN LOWER(description)) > 0)";
      $cond_params[] = $q;
      $cond_params[] = $q;
    }
  }
}

// $options['conditions'] = array(implode(' AND ', $conds), $cond_params);
!empty($conds) && $options['conditions'][] = implode(' AND ', $conds);
!empty($cond_params) && $options['conditions'] = array_merge($options['conditions'], $cond_params);

if (empty($order))
  $order = empty($search_tokens) ? 'date' : 'name';

switch ($order) {
  case "name":  
    $options['order'] = "name asc";
    break;
  case "date":
    $options['order'] = "created_at desc";
    break;
  case "updated":
    $options['order'] = "updated_at desc";
    break;
  case "id":
    $options['order'] = "id desc";
    break;
  default:
    $options['order'] = "created_at desc";
    break;
}

$options['calc_rows'] = 'found_pools';
$pools = Pool::find_all($options);

calc_pages();

$samples = array();
foreach($pools as $k => &$p) {
  if (!$post = $p->get_sample())
    continue;
  $samples[$k] = $post;
}

respond_to_list($pools);
?>