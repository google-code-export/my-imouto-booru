<?php
if (isset(Request::$params->version)) {
  # HTTP caching is unreliable for XHR.  If a version is supplied, and the version
  # hasn't changed since then, return an empty response.  
  $version = Tag::$_->get_summary_version();
  if (Request::$params->version == $version) {
    render('json', array('version' => $version, 'unchanged' => true));
    return;
  }
}

# This string is already JSON-encoded, so don't call to_json.
render('json', Tag::$_->get_json_summary());
?>