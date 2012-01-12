<?php
function only_user($level) {
  if (!User::$_->is(">=$level"))
    access_denied();
}

function post_only_user($level) {
  if (Request::$method != 'POST')
    return;
  
  if (User::$_->is("<$level"))
    access_denied();
}

function render_error($record = 'Error!') {
  render(array('status' => 500, 'layout' => 'bare'));
}

function respond_to_success($notice, $redirect_to_params, $options = array()) {
  check_array($redirect_to_params);
  $extra_api_params = isset($options['api']) ? $options['api'] : array();
  
  switch (Request::$format) {
    case 'html':
      notice($notice);
      call_user_func_array('redirect_to', $redirect_to_params);
    break;
    
    case 'json':
      render('json', to_json(array_merge($extra_api_params, array('success' => true))));
    break;
  }
}

function notice($notice){
  setcookie('notice', $notice, time() + 31556926, '/');
}

function respond_to_error($obj, $redirect_to_params, $options = array()) {
  $extra_api_params = isset($options['api']) ? $options['api'] : array();
  $status = isset($options['status']) ? $options['status'] : 500;

  if (is_object($obj) && is_subclass_of($obj, 'ActiveRecord')) {
    $obj = implode(', ', $obj->record_errors->full_messages());
    $status = 420;
  }
  
  if ($status == 420)
    $status = '420 Invalid Record';
  elseif ($status == 421)
    $status = '421 User Throttled';
  elseif ($status == 422)
    $status = '422 Locked';
  elseif ($status == 423)
    $status = '423 Already Exists';
  elseif ($status == 424)
    $status = '424 Invalid Parameters';

  switch (Request::$format) {
    case 'html':
      notice('Error: '.$obj);
      check_array($redirect_to_params);
      call_user_func_array('redirect_to', $redirect_to_params);
    
    case 'json':
      render('json', to_json(array_merge($extra_api_params, array('success' => false, 'reason' => $obj))), array('status' => $status));
    
    case 'xml':
      // fmt.xml {render :xml => extra_api_params.merge(:success => false, :reason => obj).to_xml(:root => "response"), :status => status}
    break;
  }
}

function respond_to_list($inst_var) {
  // $inst_var = instance_variable_get("@#{inst_var_name}")
  // global $$inst_var_name;
  // $inst_var = &$$inst_var_name;
  
  switch (Request::$format) {
    case 'html':
    break;
    
    case 'json':
      if (method_exists($inst_var, 'to_json'))
        render('json', $inst_var->to_json());
      else
        render('json', to_json($inst_var));
    break;
    
    case 'xml':
    break;
  }
}

function redirect_homepage() {
  // if(!CONFIG::show_homepage)
    // redirect_to('controller=>post', 'action=>index');
}

function numbers_to_imoutos($number){
	if(!CONFIG::show_homepage_imoutos)
		return;

	$number = str_split($number);
	$output = '<div style="margin-bottom: 1em;">'."\r\n";
  
  foreach($number as $num)
    $output .=	'    <img alt="' . $num . '" src="/images/' . $num . ".gif\" />\r\n";
	
	$output .= "  </div>\r\n";
	return $output;
}

function init_cookies() {
  if(Request::$format != 'html' || Request::$format == 'json')
    return;
  
  // $forum_posts = ForumPost::$_->find('all', array('order' => "updated_at DESC", 'limit' => 10, 'conditions' => "parent_id IS NULL"));
  // foreach($forum_posts as $fp) {
    // $updated = User::$current->is_anonymous ? false : $fp->updated_at > User::$current->last_forum_topic_read_at;
    // $fp_cookies[] = array($fp->updated_at, $fp->id, $updated, ceil($fp->response_count/30));
  // }
  // Cookies::$list["current_forum_posts"] = to_json($fp_cookies);

  // Cookies::$list["country"] = $current_user_country;
  // vde(User::$current->is_anonymous);
  if(!User::$current->is_anonymous) {
    Cookies::put("user_id", (string)User::$current->id);
    Cookies::put("user_info", User::$current->user_info_cookie());
    // Cookies::$list["has_mail"] = User::$current->has_mail() ? "1" : "0";
    // Cookies::$list["forum_updated"] = User::$current->is(">=30") && ForumPost::$_->updated(User::$current) ? "1" : "0";
    // Cookies::$list["comments_updated"] = User::$current->is(">=30") && Comment::$_->updated(User::$current) ? "1" : "0";
    
    // if(User::$current->is(">=35")) {
      // $mod_pending = Post::$_->count(array('conditions' => array("status = 'flagged' OR status = 'pending'")));
      // cookies["mod_pending"] = $mod_pending;
    // }
    
    // if(User::$current->is_blocked()) {
      // if(User::$current->ban)
        // Cookies::$list["block_reason"] = "You have been blocked. Reason: ".User::$current->ban->reason.". Expires: ".substr(User::$current->ban->expires_at, 0, 10);
      // else
        // Cookies::$list["block_reason"] = "You have been blocked.";
    // } else
      // Cookies::$list["block_reason"] = "";
    
    Cookies::put("resize_image", User::$current->always_resize_images ? "1" : "0");
    
    Cookies::put('show_advanced_editing', User::$current->show_advanced_editing ? "1" : "0" );
    
    // Cookies::$list["my_tags"] = User::$current->my_tags;
    
    // $a = explode("\r\n", User::$current->blacklisted_tags());
    // vde($a);
    cookie_rawput('blacklisted_tags', str_replace('%0D%0A', '&', urlencode(User::$current->blacklisted_tags())));
    
    
    // ["blacklisted_tags"] = User::$current->blacklisted_tags_array;
    Cookies::put("held_post_count", User::$current->held_post_count());
  } else {
    Cookies::delete('user_info');
    Cookies::delete('login');
    // Cookies::$list["blacklisted_tags"] = str_replace('%0D%0A', '&', urlencode(implode("\r\n", CONFIG::$default_blacklists)));
    // Cookies::rawput
    cookie_rawput('blacklisted_tags', str_replace('%0D%0A', '&', urlencode(implode("\r\n", CONFIG::$default_blacklists))));
  }
  // if flash[:notice] then
    // cookies["notice"] = flash[:notice]
}

function save_tags_to_cookie() {
  if (!empty(Request::$params->tags))
    $tags = Request::$params->tags;
  elseif (!empty(Request::$params->post) && !empty(Request::$params->post['tags']))
    $tags = explode(' ', strtolower(Request::$params->post['tags']));
  else
    return;
  
  $tags = TagAlias::$_->to_aliased($tags);
  if (!empty($_COOKIE["recent_tags"]))
    $tags = array_merge($tags, explode(' ', $_COOKIE["recent_tags"]));
  
  $tags = array_unique(array_filter($tags));
  
  Cookies::put("recent_tags", implode(' ', array_slice($tags, 0, 20)));
}

function set_current_user() {
  $AnonymousUser = array(
    'id'                  => null,
    'level'               => 0,
    'name'                => "Anonymous",
    'pretty_name'         => "Anonymous",
    'is_anonymous'        => true,
    'show_samples'        => true,
    'has_avatar'          => false,
    'language'            => '',
    'secondary_languages' => '',
    'secondary_language_array'  => array(),
    'ip_addr'             => $_SERVER['REMOTE_ADDR'],
    'pool_browse_mode'    => 1
  );
  
  // if(!empty(User::$current)) {
  if (!empty($_SESSION[CONFIG::app_name]['user_id']))
    User::$current = User::$_->find($_SESSION[CONFIG::app_name]['user_id']);
    // User::$current = new User('find', $_SESSION[CONFIG::app_name]['user_id']);
  elseif (isset($_COOKIE['login']) && isset($_COOKIE['pass_hash']))
    User::$current = User::$_->authenticate_hash($_COOKIE['login'], $_COOKIE['pass_hash']);
  elseif (isset(Request::$params->login) && isset(Request::$params->password_hash))
    User::$current = User::$_->authenticate(Request::$params->login, Request::$params->password_hash);
  elseif (isset(Request::$params->user['name']) && isset(Request::$params->user['password']))
    User::$current = User::$_->authenticate(Request::$params->user['name'], Request::$params->user['password']);
  // vde(User::$current);
  if(User::$current) {
    # TODO:
    // if(User::$current->is_blocked && User::$current->ban && User::$current->ban->expires_at < gmd()) {
      // User::$current->update_attribute(array('level'->CONFIG["starting_level"]));
      // Ban::$_->destroy_all("user_id = #{@current_user.id}")
    // }
  } else
    User::$current = User::$_->create_from_array($AnonymousUser);
    // User::$current = new User('from_array', $AnonymousUser);
  // vde(User::$current);
}

function access_denied($page = '/user/login'){
  $previous_url = !empty(Request::$params->url) ? Request::$params->url : Request::$url;
  
  switch (Request::$format) {
    case 'html':
      notice('Access denied');
      redirect_to('user#login', array('url' => $previous_url));
    break;
    
    case 'json':
      render('json', array('success' => false, 'reason' => 'access denied'), array('status' => 403));
    break;
    
    case 'xml':
      render('xml', array('success' => false, 'reason' => 'access denied'),array('status' => 403));
    break;
  }
  
  // respond_to(array(
    // 'html' => array('notice' => "Access denied", 'redirect_to' => array('user#login', array('url' => $previous_url))),
    // 'json' => array('render' => array('json' => array('success' => false, 'reason' => 'access denied'), 'status' => 403))
    //'xml' => array('render' => array('xml' => array('success' => false, 'reason' => 'access denied'), 'status' => 403))
  // ));
}
?>