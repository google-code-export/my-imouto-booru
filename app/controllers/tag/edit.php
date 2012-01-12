<?php
if (!empty(Request::$params->id))
  $tag = Tag::$_->find(Request::$params->id);
elseif (!empty(Request::$params->name))
  $tag = Tag::$_->find_by_name(Request::$params->name);

if (empty($tag))
  $tag = Tag::$_->blank();
?>