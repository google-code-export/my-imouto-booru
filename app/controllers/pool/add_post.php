<?php
required_params('post_id');
auto_set_params('pool_id');

if (Request::$post) {
  if (!Request::$params->pool_id)
    return;
  
  // $pool = new Pool('find', Request::$params->pool_id);
  $pool = Pool::find(Request::$params->pool_id);
  
  $_SESSION['last_pool_id'] = $pool->id;
  
  if (isset(Request::$params->pool) && !empty(Request::$params->pool['sequence']))
    $sequence = Request::$params->pool['sequence'];
  else
    $sequence = null;
  
  try {
    $pool->add_post(Request::$params->post_id, array('sequence' => $sequence, 'user' => User::$current->id));
    respond_to_success('Post added', array('post#show', 'id' => Request::$params->post_id));
    
  } catch (Exception $e) {
    if ($e->getMessage() == 'Post already exists')
      respond_to_error($e->getMessage(), array('post#show', array('id' => Request::$params->post_id)), array('status' => 423));
    elseif ($e->getMessage() == 'Access Denied')
      access_denied();
    else
      respond_to_error($e->getMessage(), array('post#show', array('id' => Request::$params->post_id)));
  }
  
} else {
  if (User::$current->is_anonymous)
    $pools =  Pool::find_all(array('order' => "name", 'conditions' => "is_active = TRUE AND is_public = TRUE"));
  else
    $pools = Pool::find_all(array('order' => "name", 'conditions' => array("is_active = TRUE AND (is_public = TRUE OR user_id = ?)", User::$current->id)));
  
  $post = Post::find(Request::$params->post_id);
}
?>