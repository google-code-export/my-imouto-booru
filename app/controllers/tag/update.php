<?php
if (!empty(Request::$params->tag['name'])) {
  $tag = Tag::find_by_name(Request::$params->tag['name']);
  
  if ($tag)
    $tag->update_attributes(Request::$params->tag);
}
respond_to_success("Tag updated", '#index');
?>