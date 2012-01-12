<?php
function tag_header($tags) {
  if(!$tags)
    return;
  $tags = explode(' ', $tags);
  foreach($tags as $k => $tag)
    $tags[$k] = link_to($tag, '/post', array('get_params' => array('tags' => $tag)));
  
  return '/'.implode('+', $tags);
}

function time_ago_in_words($time) {
  $from_time = strtotime($time);
  
  $to_time = strtotime(gmd());
  $distance_in_minutes = round((($to_time - $from_time))/60);
  
  if ($distance_in_minutes < 0)
    return (string)$distance_in_minutes.'E';
  
  if (between($distance_in_minutes, 0, 1))
    return '1 minute';
    
  elseif (between($distance_in_minutes, 2, 44))
    return $distance_in_minutes.' minutes';
    
  elseif (between($distance_in_minutes, 45, 89))
    return '1 hour';
    
  elseif (between($distance_in_minutes, 90, 1439))
    return round($distance_in_minutes/60).' hours';
    
  elseif (between($distance_in_minutes, 1440, 2879))
    return '1 day';
    
  elseif (between($distance_in_minutes, 2880, 43199))
    return round($distance_in_minutes/1440).' days';
    
  elseif (between($distance_in_minutes, 43200, 86399))
    return '1 month';
    
  elseif (between($distance_in_minutes, 86400, 525959))
    return round($distance_in_minutes/43200).' months';
    
  elseif ($distance_in_minutes > 525959)
    return number_format(round(($distance_in_minutes/525960), 1), 1).' years';
}

function format_text($text, $options = array()) {
  return DText::parse($text);
}

// def format_inline(inline, num, id, preview_html=nil)
  // if inline.inline_images.empty? then
    // return ""
  // end

  // url = inline.inline_images.first.preview_url
  // if not preview_html
    // preview_html = %{<img src="#{url}">}
  // end
  // id_text = "inline-%s-%i" % [id, num]
  // block = %{
    // <div class="inline-image" id="#{id_text}">
      // <div class="inline-thumb" style="display: inline;">
      // #{preview_html}
      // </div>
      // <div class="expanded-image" style="display: none;">
        // <div class="expanded-image-ui"></div>
        // <span class="main-inline-image"></span>
      // </div>
    // </div>
  // }
  // inline_id = "inline-%s-%i" % [id, num]
  // script = 'InlineImage.register("%s", %s);' % [inline_id, inline.to_json]
  // return block, script, inline_id
// end

function format_inlines($text, $id) {
  $num = 0;
  $list = array();
  
  // preg_match('/image #(\d+)/i', $text, $m);
  // foreach ($m as $t) {
    // $i = new Inline('find', $m[1]);
    // if ($i) {
      // list($block, $script) = format_inline($i, $num, $id);
      // $list[] = $script;
      // $num++;
      // return $block;
    // } else
      // return $t;
  // }

  if ($num > 0 )
    $text .= '<script language="javascript">' . implode("\n", $list) . '</script>';

  return $text;
}

function paginator(){
  global $pages;
  
  $page = Request::$params->page - 1;
  
  if (!isset($pages) || $pages <= 1)
    return;
  
  $url = Request::$url;
  
  $get_params = Request::$get_params;
  
  if (isset($get_params['page']))
    unset($get_params['page']);
  
  $get_params = http_build_query($get_params);
  $get_params && $get_params .= '&';
  $url .= '?'.$get_params.'page=';
?>
<div class="paginator">
  <?php
  if ( $page )
    echo '<a href="'  . $url . $page . '">&lt;&lt;</a> ';
  else
    echo '<span class="disabled">&lt;&lt;</span> ';

  if ($page == 0)
    echo '<span class="current">1</span> ';
  else
    echo '<a href="'  . $url .'1">1</a> ';

  if($pages < 10){

    for($i = 2; $i <= $pages; $i++){
      if($i == $page+1)
        echo '<span class="current">' . $i . '</span> ';
      else
        echo '<a href="'  . $url . $i . '">' . $i . '</a> ';
    }
    
  } elseif ($page > ($pages - 4)) {

    echo '... ';
    for($i = ($pages - 4); $i < ($pages); $i++) {
      if($i == $page+1)
        echo '<span class="current">' . $i . '</span> ';
      else
        echo '<a href="'  . $url . $i . '">' . $i . '</a> ';
    }
    
  } elseif ($page > 4) {

    echo '... ';
    for ($i = ($page - 1); $i <= ($page + 3); $i++) {
      if($i == $page+1)
        echo '<span class="current">' . $i . '</span> ';
      else
        echo '<a href="'  . $url . $i . '">' . $i . '</a> ';
    }
    echo '... ';
    
  } else {

    if ($page >= 3){
      for ($i = 2; $i <= $page+3; $i++) {
        if ($i == $page+1)
          echo '<span class="current">' . $i . '</span> ';
        else
          echo '<a href="'  . $url . $i . '">' . $i . '</a> ';
      }
    } else {
      for ($i = 2; $i <= 5; $i++) {
        if($i == $page+1)
          echo '<span class="current">' . $i . '</span> ';
        else
          echo '<a href="'  . $url . $i . '">' . $i . '</a> ';
      }
    }
    echo '... ';
  }

  if ($pages >= 10) {
    if($pages == $page+1)
      echo '<span class="current">' . $i . '</span> ';
    else
      echo '<a href="'  . $url . $pages . '">' . $pages . '</a> ';
  }

  if($pages != $page+1)
    echo '<a href="'  . $url . ($page + 2) . '">&gt;&gt;</a> ';
  else
    echo ' <span class="disabled">&gt;&gt;</span>';
?>
</div>
<?php
}

// function make_menu_item($label, $url_options = array(), $options = array()) {
  // $item = array(
    // 'label' => $label,
    // 'dest' => url_for($url_options),
    // 'class_names' => !empty($options['class_names']) ? $options['class_names'] : array()
  // );
  
  // if (!empty($options['html_id']))
    // $item['html_id'] = $options['html_id'];
  
  // if(!empty($options['name']))
    // $item['name'] = $options['name'];

  // if (!empty($options['level']) && need_signup($options['level']))
    // $item['login'] = true;

  // if ($url_options['controller'] == Request::$controller)
    // $item['class_names'][] "current-menu";

  // return $item;
// }

// function make_main_item($options) {
  // global $top_menu_items;
  // $item = make_menu_item($options)
  
  // $top_menu_items[] = $item;
  // return to_json($item);
// }

// function make_sub_item($options) {
  // $item = make_menu_item($options)
  // return to_json($item);
// }
?>