<?php
if (!isset(Request::$params->score)) {
  $vote =  PostVotes::$_->find_by_user_id_and_post_id(User::$current->id, Request::$params->id);
  $score = $vote ? $vote->score : 0;
  respond_to_success("", array(), array('vote' => $score));
  return;
}

$p = Post::$_->find(Request::$params->id);
// vde($p);
$score = (int)Request::$params->score;

if (!User::$current->is('>=40') && ($score < 0 || $score > 3)) {
  respond_to_error("Invalid score", "#show", array('id' => Request::$params->id, 'tag_title' => $p->tag_title(), 'status' => 424));
  return;
}
// vde($p);
$vote_successful = $p->vote($score, User::$current);
// vde($p);
$api_data = Post::$_->batch_api_data(array($p));
$api_data['voted_by'] = $p->voted_by();

if ($vote_successful)
  respond_to_success("Vote saved", array("#show", 'id' => Request::$params->id, 'tag_title' => $p->tag_title()), array('api' => $api_data));
else
  respond_to_error("Already voted", array("#show", array('id' => Request::$params->id, 'tag_title' => $p->tag_title())), array('api' => $api_data, 'status' => 423));
?>