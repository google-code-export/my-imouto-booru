<?php
if (isset(ActionView::$render_args['status'])) {
  ActionView::$set_status(ActionView::$args['status']);
  unset(ActionView::$args['status']);
}

if (!empty(ActionView::$args['layout'])) {
  ActionView::$layout = ActionView::$args['layout'];
  unset(ActionView::$args['layout']);
}

if (array_key_exists('nothing', ActionView::$args) && ActionView::$args['nothing'] === true)
  exit;

if (empty(ActionView::$args)) {
  if(Request::$format == 'html' || Request::$format == 'xml')
    require ACTVIEW . 'render_markup_default.php';
}

# If we got here and format is json, for now, this means the action doesn't support json.
if (Request::$format == 'json') {
  exit_with_status(400);
}
?>