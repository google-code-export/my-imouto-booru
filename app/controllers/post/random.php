<?php
$max_id = Post::maximum('id');

foreach(range(1, 10) as $i) {
  $post = Post::find('first', array('conditions' => array("id = ? AND status <> 'deleted'", rand(1, $max_id) + 1)));

  if ($post && $post->can_be_seen_by(User::$current))
    redirect_to("#show", array('id' => $post->id, 'tag_title' => $post->tag_title));
}

// flash[:notice] = "Couldn't find a post in 10 tries. Try again."
notice("Couldn't find a post in 10 tries. Try again.");
redirect_to("#index");
?>