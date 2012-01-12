<?php
include ROOT . 'lib/application_functions.php';
include ROOT . 'lib/dtext.php';

function cookie_rawput($name, $value){
  setrawcookie($name, $value, time() + 31556926, '/');
}

function cookie_put($name, $value){
  setcookie($name, $value, time() + 31556926, '/');
}

function cookie_remove($name){
  setcookie($name, '', time() - 31556926, '/');
}

/**
 * If $params === true, it becomes $lower_page, which means that
 * the page param will be lowered by 1 for the LIMIT clause of the query.
 */
function create_page_params($params = array()){
  if ($params === true) {
    $lower_page = true;
    $params = array();
  } else
    $lower_page = false;
  
  global $page, $offset, $pagination_limit;
  
  if (!isset(Request::$params->page))
    Request::$params->page = 1;
  
  $pagination_limit = isset(Request::$params->limit) && ctype_digit(Request::$params->limit) ? Request::$params->limit : CONFIG::default_index_limit;
  $page = Request::$params->page;
  
  if ($lower_page && $page > 0)
    $page--;
  
  $offset = $pagination_limit * $page;
  
  if(isset($params['limit']))
    $pagination_limit = $params['limit'];
  if(isset($params['page']))
    $page = $params['page'];
  if(isset($params['offset']))
    $offset = $params['offset'];
}

function calc_pages() {
  global $found_posts, $found_rows, $found_pools, $pages, $pagination_limit;
  
  if (!isset($found_rows)) {
    if (isset($found_posts))
      $found_rows = $found_posts;
    elseif (isset($found_pools))
      $found_rows = $found_pools;
  }
  
  if (!isset($found_rows))
    return;
  
  $pages = ceil($found_rows/$pagination_limit);
}

function compact_time($datetime) {
  $datetime = new DateTime($datetime);
  
  if ($datetime->format('M d, Y') == gmd('M d, Y'))
    return $datetime->format('H:i');
  else
    return $datetime->format('M d');
}
?>