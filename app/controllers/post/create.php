<?php
required_params('post');

if (User::is('<=20') && Post::count(array('conditions' => array("user_id = ? AND created_at > ? ", User::$current->id, gmd_math('sub', '1D')))) >= CONFIG::member_post_limit) {
  respond_to_error("Daily limit exceeded", "#error", array('status' => 421));
}

auto_set_params(array('md5'));

$status = User::is('>=30') ? 'active' : 'pending';

Request::$params->post = array_merge(Request::$params->post, array(
  'updater_user_id' => User::$current->id,
  'updater_ip_addr' => Request::$remote_ip,
  'user_id'         => User::$current->id,
  'ip_addr'         => Request::$remote_ip,
  'status'          => $status,
  'tempfile_path'   => $_FILES['post']['tmp_name']['file'],
  'tempfile_name'   => $_FILES['post']['name']['file'],
  'is_upload'       => true
));

$post = Post::create(Request::$params->post);

if ($post->record_errors->blank()) {
  
  if (Request::$params->md5 && $post->md5 != strtolower(Request::$params->md5)) {
    $post->destroy();
    respond_to_error("MD5 mismatch", '#error', array('status' => 420));
    
  } else {
    $api_data = array('post_id' => $post->id, 'location' => url_for('post#show', array('id' => $post->id)));
    
    if (CONFIG::dupe_check_on_upload && $post->is_image() && empty($post->parent_id)) {
      // if (Request::$format == "xml" || Request::$format == "json") {
        // $options = array('services' => SimilarImages::get_services('local'), 'type' => 'post', 'source' => $post);

        // $res = SimilarImages::similar_images($options);
        // if (!empty($res['posts'])) {
          // $post->tags .= " possible_duplicate";
          // $post->save();
          // $api_data['has_similar_hits'] = true;
        // }
      // }
      
      $api_data['similar_location'] = url_for('post#similar', array('id' => $post->id, 'initial' => 1));
      respond_to_success("Post uploaded", array('#similar', array('id' => $post->id, 'initial' => 1)), array('api' => $api_data));
    } else {
      respond_to_success("Post uploaded", array('#show', array('id' => $post->id, 'tag_title' => $post->tag_title())), array('api' => $api_data));
    }
  }
} elseif ($post->record_errors->invalid('md5')) {
  $p = Post::find_by_md5($post->md5);
  
  if (!empty(Request::$params->post['tags'])) {
    $p->old_tags = $p->tags;
    $p->tags .= ' ' . Request::$params->post['tags'];
  }
  
  # TODO: what are these attributes for?
  $update = array('updater_user_id' => User::$current->id, 'updater_ip_addr' => Request::$remote_ip);
  
  if (empty($p->source) && !empty($post->source))
    $p->source = $post->source;
    
  $p->save();

  $api_data = array(
    'location' => url_for("post#show", array('id' => $p->id)),
    'post_id' => $p->id
  );
  respond_to_error("Post already exists", array("post#show", array('id' => $p->id, 'tag_title' => $post->tag_title())), array('api' => $api_data, 'status' => 423));
} else {
  respond_to_error($post, "#error");
}
?>