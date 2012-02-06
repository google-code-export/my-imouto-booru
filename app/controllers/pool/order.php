<?php
required_params('id');

$pool = Pool::find(Request::$params->id);

if (!$pool->can_be_updated_by(User::$current))
  access_denied();

if (Request::$post) {
  foreach (Request::$params->pool_post_sequence as $i => $seq)
    PoolPost::update($i, array('sequence' => $seq));
  
  $pool->reload();
  $pool->update_pool_links();
  
  notice("Ordering updated");
  // flash[:notice] = "Ordering updated"
  redirect_to('#show', array('id' => Request::$params->id));
} else
  $pool_posts = $pool->pool_posts;
?>