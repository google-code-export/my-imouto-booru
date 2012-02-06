<?php
set_title("Moderation queue");

auto_set_params('commit', 'reason', 'reason2');

if (Request::$post) {
  $posts = array();
  
  if (!empty(Request::$params->ids)) {
    foreach (array_keys(Request::$params->ids) as $post_id) {
      
      $post = Post::find($post_id);
      
      if (Request::$params->commit == "Approve")
        $post->approve(User::$current->id);
      elseif (Request::$params->commit == "Delete") {
        $post->destroy_with_reason(Request::$params->reason ? Request::$params->reason : Request::$params->reason2, User::$current);

        # Include post data for the parent: deleted posts aren't counted as children, so
        # their has_children attribute may change.
        if (!empty($post->parent_id))
          $posts[] = $post->get_parent();
      }
      $post->reload();
      $posts[] = $post;
    }
  }
  
  $posts = array_unique($posts);
  
  if (Request::$format == "json" || Request::$format == "xml")
    $api_data = Post::batch_api_data($posts);
  else
    $api_data = array();

  if (Request::$params->commit == "Approve")
    respond_to_success("Post approved", "#moderate", array('api' => $api_data));
  elseif (Request::$params->commit == "Delete")
    respond_to_success("Post deleted", "#moderate", array('api' => $api_data));
  
} else {
  if (!empty(Request::$params->query)) {
    $pending_posts = Post::find_by_sql(Post::generate_sql(Request::$params->query, array('pending' => true, 'order' => "id desc")));
    $flagged_posts = Post::find_by_sql(Post::generate_sql(Request::$params->query, array('flagged' => true, 'order' => "id desc")));
  } else {
    $pending_posts = Post::find('all', array('conditions' => "status = 'pending'", 'order' => "id desc"));
    $flagged_posts = Post::find('all', array('conditions' => "status = 'flagged'", 'order' => "id desc"));
  }
}
?>