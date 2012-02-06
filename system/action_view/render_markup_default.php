<?php
if (Request::$format == 'html') {
  header('Content-Type: text/html; charset=UTF-8');
  !ActionView::$render && ActionView::$render = VIEWPATH . Request::$controller . '/' . Request::$action . '.php';
  
} elseif (Request::$format == 'xml') {
  header('Content-type: application/rss+xml; charset=UTF-8');
  !ActionView::$render && ActionView::$render = VIEWPATH . Request::$controller . '/' . Request::$action . '.xml.php';
}

ob_start();
# TODO: change die for a nicer way to exit.
if (false === include ActionView::$render) {
  if (System::$conf->system_error_reporting)
    die('Unable to find View file.');
  else
    exit_with_status(500);
}

ActionView::$content_for['layout'] = ob_get_clean();

if (Request::$format == 'html' && !empty(ActionView::$layout)) {
  if (!include LAYOUTS . ActionView::$layout . '.php') {
    if (System::$conf->system_error_reporting)
      die('Unable to load Layout.');
    else
      exit_with_status(500);
  }
  
} else {
  if (Request::$format == 'html')
    content_for('layout');
  elseif (Request::$format == 'xml')
    content_for('layout');
}

exit;
?>