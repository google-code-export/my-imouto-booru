<?php
if (!empty(Request::$params->id))
  $tag = Tag::find(Request::$params->id);
elseif (!empty(Request::$params->name))
  $tag = Tag::find_by_name(Request::$params->name);

if (empty($tag))
  $tag = Tag::blank();
?>