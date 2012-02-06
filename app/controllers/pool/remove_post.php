<?php
required_params(array('pool_id', 'post_id'));

$pool = Pool::find(Request::$params->pool_id);
$post = Post::find(Request::$params->post_id);
if (!$pool || !$post)
  die_404();

if (Request::$post) {
  
  try {
    $pool->remove_post(Request::$params->post_id, array('user' => User::$current));
  } catch (Exception $e) {
    if ($e->getMessage() == 'Access Denied')
      access_denied();
  }
  
  $api_data = Post::batch_api_data(array($post));

  // response.headers["X-Post-Id"] = params[:post_id]
  respond_to_success("Post removed", array('post#show', 'id' => Request::$params->post_id), array('api' => $api_data));
}
?>