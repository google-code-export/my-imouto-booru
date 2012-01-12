<?php
auto_set_params(array('name', 'order', 'type' => 'any'));
create_page_params();

set_title("Tags");

if (!empty(Request::$params->limit) && Request::$params->limit == 0)
  $limit = null;
elseif (!empty(Request::$params->limit) && ctype_digit(Request::$params->limit))
  $limit = (int)Request::$params->limit;
else
  $limit = 50;

$order = 'name';

if (!empty(Request::$params->order)) {
  if (Request::$params->order == 'count')
    $order = 'post_count desc';
  elseif (Request::$params->order == 'date')
    $order = 'id desc';
}

$conds = array('true');
$cond_params = array();

if (!empty(Request::$params->name)) {
  $conds[] = 'name LIKE ?';
  $cond_params[] = is_int(strpos(Request::$params->name, '*')) ? Request::$params->name : '%'.Request::$params->name.'%';
}

if (ctype_digit(Request::$params->type)) {
  Request::$params->type = (int)Request::$params->type;
  $conds[] = 'tag_type = ?';
  $cond_params[] = Request::$params->type;
}

if (!empty(Request::$params->after_id)) {
  $conds[] = 'id >= ?';
  $cond_params[] = Request::$params->after_id;
}

if (!empty(Request::$params->id)) {
  $conds[] = 'id = ?';
  $cond_params[] = Request::$params->id;
}

array_unshift($cond_params, implode(" AND ", $conds));

switch (Request::$format) {
  case 'json':
    // $tags = new Collection ('Tag', 'find', 'all', array('order' => $order, 'limit' => $limit, 'conditions' => array(implode(" AND ", $conds), $cond_params)));
    $tags = Tag::$_->collection('find',  array('order' => $order, 'limit' => $limit, 'conditions' => array(implode(" AND ", $conds), $cond_params)));
    render('json', to_json($tags));
  break;
  
  case 'xml':
    // order = nil if params[:order] == nil
    // conds = conds.join(" AND ")
    // if conds == "true" && CONFIG["web_server"] == "nginx" && File.exists?("#{RAILS_ROOT}/public/tags.xml")
      // # Special case: instead of rebuilding a list of every tag every time, cache it locally and tell the web
      // # server to stream it directly. This only works on Nginx.
      // response.headers["X-Accel-Redirect"] = "#{RAILS_ROOT}/public/tags.xml"
      // render :nothing => true
    // else
      // render :xml => Tag.find(:all, :order => order, :limit => limit, :conditions => [conds, *cond_params]).to_xml(:root => "tags")
    // end
  break;
}
// vde(Request::$params->page);
$params = array(
  'order'       => $order,
  'per_page'    => 50,
  'page'        => Request::$params->page,
  'conditions'  => $cond_params
);
// $tags = new Collection('Paginate->found_posts', 'Tag', 'find', $params);
$tags = Tag::$_->collection('Paginate->found_posts', 'find', $params);
calc_pages();
?>