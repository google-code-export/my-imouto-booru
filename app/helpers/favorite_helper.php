<?php
function favorite_list($post) {
  $users = $post->favorited_by();
  if(!$users)
    return "no one";
  
  for($i = 0; $i < 6; $i++) {
    if(!isset($users[$i]))
      break;
    $html[] = '<a href="/user/show/' . $users[$i]['id'] . '">' . $users[$i]['name'] . '</a>';
  }
  
  $output = implode(', ', $html);
  $html = array();
  
  if(count($users) > 6) {
    for($i = 6; $i < count($users); $i++)
      $html[] = '<a href="/user/show/' . $users[$i]['id'] . '">' . $users[$i]['name'] . '</a>';
    $html = '<span id="remaining-favs" style="display: none;">, '.implode(', ', $html).'</span>';
    $output .= $html.' <span id="remaining-favs-link">(<a href="#" onclick="$(\'remaining-favs\').show(); $(\'remaining-favs-link\').hide(); return false;">'.(count($users)-6).' more</a>)</span>';
  }
  return $output;
}
?>