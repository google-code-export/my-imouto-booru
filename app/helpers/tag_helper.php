<?php
function tag_links($tags, $options = array()) {
  if(!$tags)
    return null;
  
  $prefix = !empty($options['prefix']) ? $options['prefix'] : '';
  
  if(is_string($tags)) {
    $tags = explode(' ', $tags);
    
    $tags = Tag::find(array('conditions' => array('name in (??)', $tags), 'select' => 'name, tag_type, post_count'));
  
  } elseif(is_array($tags)) {
    
    if (is_indexed_arr($tags) && count(current($tags)) == 2) {
      # We're getting a tag's cached_related tags. We need to find the tag type.
      $i = 0;
      $t = array();
      foreach ($tags as $tag) {
        $t[] = array(
          'name' => current($tag),
          'type' => Tag::type_name_helper(current($tag)),
          'post_count' => end($tag)
        );
      }
    } else {
      # We're getting a post's cached_tags. We need to find the count for each tag.
      $names = array_keys($tags);
      
      # We may have a misstyped metatag. Better return.
      if (!isset($names[0]) || !is_string($names[0]))
        return;
      
      $count = Tag::find_post_count_and_name('all', array('conditions' => array('name in (??)', $names), 'order' => 'name', 'return_array' => true));
      // vde($count);
      $i = 0;
      # There's a possibility a tag was deleted and cached_tags wasn't updated.
      # This will cause errors, so we'll just skip tags that weren't found.
      $t = array();
      foreach ($count as $tag) {
        $t[] = array(
          'name' => $tag['name'],
          'type' => $tags[$tag['name']],
          'post_count' => $tag['post_count']
        );
      }
    }
    $tags = $t;
    unset($t);
    
  } elseif(is_a($tags, 'Tag')) {
    // 
  }
  
  $tag_query = !empty(Request::$params->tags) ? Request::$params->tags : null;
  $html = '';
  
  foreach($tags as $tag) {
    list($name, $type, $count) = array_values($tag);
    
    if (ctype_digit($type))
      $type = Tag::type_name($type);
    
    $html .= '<li class="tag-type-'.$type.'"><a href="/tag?name='.$name.'">?</a>';
    
    if(User::is('>=30')) 
      $html .= ' <a href="/post?tags='.$name.'+'.$tag_query.'">+</a> <a href="/post?tags=-'.$name.$tag_query.'">&ndash;</a>';
    
    $hover = !empty($options['with_hover_highlight']) ? " onmouseover='Post.highlight_posts_with_tag(\"$name\")' onmouseout='Post.highlight_posts_with_tag(null)'" : '';
      
    $html .= ' <a href="/post?tags='.$name.'"'.$hover.'>'.str_replace('_', ' ', $name).'</a> <span class="post-count">'.$count.'</span></li>';
  }
  
  return $html;
}
?>