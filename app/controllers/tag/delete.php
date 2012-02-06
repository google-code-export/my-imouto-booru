<?php
required_params('tag_name');

$tag = Tag::find_by_name(Request::$params->tag_name);

if ($tag)
  $tag->delete();
  
unset(Request::$get_params['tag_name']);

respond_to_success('Tag deleted', array('#index', Request::$get_params));
?>