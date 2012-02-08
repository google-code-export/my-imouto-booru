<?php
if (isset(ActionView::$params['status'])) {
  ActionView::$set_status(ActionView::$params['status']);
  unset(ActionView::$params['status']);
}

if (!empty(ActionView::$params['layout'])) {
  ActionView::$layout = ActionView::$params['layout'];
  unset(ActionView::$params['layout']);
}

if (array_key_exists('nothing', ActionView::$params) && ActionView::$params['nothing'] === true)
  exit;

if (empty(ActionView::$params)) {
  if(Request::$format == 'html' || Request::$format == 'xml')
    require ACTVIEW . 'render_markup_default.php';
}

# If we got here and format is json, for now, this means the action doesn't support json.
if (Request::$format == 'json') {
  exit_with_status(400);
}
?>