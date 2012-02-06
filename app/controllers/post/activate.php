<?php
$ids = Request::$params->post_ids;
$changed = Post::batch_activate((User::is('>=40') ? null: User::$current->id), $ids);
respond_to_success("Posts activated", "#moderate", array('api' => array('count' => $changed)));
?>