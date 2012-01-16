<?php
function link_to($link = null, $url_params = null, $attrs = array()) {
  
  if ($url_params === null && $link === null){
    return "<span>Invalid link_to</span>";
  }
  
  if (is_array($url_params) || (strpos($url_params, 'http') !== 0 && strpos($url_params, '/') !== 0)) {
    if (is_array($url_params)) {
      $url_to = array_shift($url_params);
      
    } else {
      $url_to = $url_params;
      $url_params = array();
    }
    
    $url_to = url_for($url_to, $url_params);
    
  } else
    $url_to = $url_params;
  
  if(!empty($attrs)){
    foreach ($attrs as $attr_name => $attr_value)
      $attr[] = "$attr_name=\"$attr_value\"";
    
    $attr = isset($attr) ? ' '.implode(' ', $attr) : null;
  } else
    $attr = null;
  
  return "<a href=\"$url_to\"$attr>$link</a>";
}

# Accepts special attribute: multipart.
function form_tag($action_url, $method = 'post', $attrs = array()) {
  if (is_array($method)) {
    $attrs = $method;
    $method = 'post';
  }
  
  # Check special attribute 'multipart'.
  if (!empty($attrs['multipart'])) {
    $attrs['enctype'] = 'multipart/form-data';
    unset($attrs['multipart']);
  }
  
  $attrs['method'] = $method;
  $attrs['action'] = url_for($action_url, array('compare_current_uri' => true));
  
  return HTMLTags::create('form', 'no_inner_html', $attrs);
}

function submit_tag($tag_value, $attrs = array()) {
  $attrs = array_merge(array('value' => $tag_value, 'type' => 'submit', 'name' => 'commit'), $attrs);
  return HTMLTags::create('input', $attrs);
}

/**
 * If $tag_value is an array, it's taken as $attrs.
 * In this case, the value to check would be the name of the tag.
 */
function text_field_tag($tag_name, $tag_value = 'no_value_attribute', $attrs = array()) {
  return HTMLTags::create_form_tag('text', $tag_name, $tag_value, $attrs);
}

/**
 * Note: For options to recognize the $tag_value, it must be identical to the option's value.
 */
function option_tag($options, $tag_value = 'no_value_attribute') {
  foreach ($options as $name => $value) {
    $tags[] = HTMLTags::create('option', $name, array('value' => $value, 'selected' => $value === $tag_value ? '1' : null));
  }
  
  return implode($tags);
}

function checkbox_tag($tag_name, $tag_value = 'no_value_attribute', $attrs = array()) {
  $hidden_tag = hidden_field_tag($tag_name, 0, array('id' => null));
  
  $checkbox_tag = HTMLTags::create_form_tag('checkbox', $tag_name, $tag_value, array_merge($attrs, array('value' => '1')));
  
  return $hidden_tag."\r\n".$checkbox_tag;
}

function hidden_field_tag($tag_name, $tag_value, $attrs = array()) {
  return HTMLTags::create_form_tag('hidden', $tag_name, $tag_value, $attrs);
}

/* (Tags with innerHTML) { */
function select_tag($tag_name, $options = array(), $tag_value = 'no_value_attribute', $attrs = array()) {
  if (is_array($tag_value)) {
    $attrs = $tag_value;
    $tag_value = 'no_value_attribute';
  }
  
  # It's needed to check the $tag_value here to pass it to option_tag()
  if ($tag_value === 'no_value_attribute')
    $tag_value = HTMLTags::find_tag_value($tag_name);
  
  $options = option_tag($options, $tag_value);
  
  $attrs['value'] = null;
  
  $select_tag = HTMLTags::create_form_tag('select', $tag_name, null, $options, $attrs);
  return $select_tag;
}

function text_area($tag_name, $tag_value = 'no_value_attribute', $attrs = array()) {
  if (is_array($tag_value)) {
    $attrs = $tag_value;
    $tag_value = 'no_value_attribute';
  }
  
  # It's needed to check the $tag_value here to pass as $innerHTML to create_form_tag
  if ($tag_value === 'no_value_attribute')
    $tag_value = HTMLTags::find_tag_value($tag_name);
  
  $attrs['value'] = null;
  
  $textarea = HTMLTags::create_form_tag('textarea', $tag_name, null, $tag_value, $attrs);
  return $textarea;
}
/* } (end Tags with innerHTML) */

function tag_attribute($attr, $val) {
  $attr = strstr($attr, '=') ? $attr : "$attr=\"true\"";
  if($val)
    return $attr;
}

function tag_attr_checked($val) {
  return tag_attribute('checked', $val);
}

function tag_has_value($val) {
  return $val ? 'value="'.$val.'"' : null;
}

function image_tag($src, $attrs = array()) {
  return HTMLTags::create('img', array_merge(array('src' => $src), $attrs));
}

class HTMLTags {

  static $selfclose_tags = array(
    'br',
    'input',
    'img'
  );
  
  static $formtags_attrs = array(
    'checkbox'  => 'checked',
    'radio'     => 'checked',
    'option'    => 'selected'
  );
  
  # if $tag_name is like '>post' at the beginning, id of the tag will be 'post'.
  # if $tag_name is like 'post->id', the name of the input will be 'post[id]' and the id will be 'post_id'.
  # if $tag_value is left to null, the value will be automatically taken from a variable
  #   according to the $tag_name. taking the above example, value would be taken from $post->id (if exists).
  static function create_form_tag($input_type, $tag_name, $tag_value, $innerHTML = null, $attrs = array()) {
    if (is_array($innerHTML)) {
      $attrs = $innerHTML;
      $innerHTML = null;
    }
    
    $autoattrs = self::parse_form_tag_name($tag_name);
    $autoattrs['type'] = $input_type;
    
    if (is_array($tag_value)) {
      $attrs = $tag_value;
      $tag_value = 'no_value_attribute';
    } else
      $autoattrs['value'] = $tag_value;
    
    # Take value from external array/object based on tag_name.
    if ($tag_value === 'no_value_attribute')
      $autoattrs['value'] = self::find_tag_value($tag_name);
    
    # Add specific attribute to form tags.
    if (!empty($autoattrs['value']) && array_key_exists($input_type, self::$formtags_attrs))
      $autoattrs[self::$formtags_attrs[$input_type]] = '1';

    $attrs = array_merge($autoattrs, $attrs);
    
    # Set the proper tag type.
    if (!in_array($input_type, array('select', 'textarea')))
      $tag_type = 'input';
    else
      $tag_type = $input_type;
    // $tag_type = $input_type == 'select' ? 'select' : 'input';
    
    return self::create($tag_type, $innerHTML, $attrs);
  }
  
  static function find_tag_value($tag_name) {
    if (is_int(strpos($tag_name, '->'))) {
      // $name = explode('->', $tag_name);
      // $value = isset_array($name);
      
      $name = explode('->', $tag_name);
      $var_name = array_shift($name);
      $name = $var_name.'['.implode('][', $name).']';
      $value = isset_array($name);
      
    } else {
      global ${$tag_name};
      $value = isset(${$tag_name}) ? ${$tag_name} : null;
    }
    
    return $value;
  }
  
  static function create($tag_name, $innerHTML = null, $attrs = array()) {
    if (is_array($innerHTML)) {
      $attrs = $innerHTML;
      $innerHTML = null;
    }
    
    $attrs = self::parse_attributes($attrs);
    
    if (in_array($tag_name, self::$selfclose_tags)) {
      $tag = '<'.$tag_name.$attrs.' />';
    } elseif ($innerHTML === 'no_inner_html') {
      $tag = '<'.$tag_name.$attrs.'>';
    } else {
      $tag = '<'.$tag_name.$attrs.'>'.$innerHTML.'</'.$tag_name.'>';
    }
    
    return $tag."\r\n";
  }
  
  static function parse_attributes($attrs) {
  
    if (!empty($attrs['size'])) {
    
      $size = explode('x', $attrs['size']);
      
      if (count($size) == 2) {
        $attrs['cols'] = $size[0];
        $attrs['rows'] = $size[1];
        unset($attrs['size']);
      } else
        $attrs['size'] = $size[0];
    }
    
    $attrs_string = array();
    
    foreach ($attrs as $attr => $value) {
      # TODO: removing $value === false check...
      if ($value === null)
        continue;
      elseif (is_bool($value))
        $value = (int)$value;
      
      $attrs_string[] = $attr.'="'.$value.'"';
    }
    
    return ' '.implode(' ', $attrs_string);
  }
  
  /**
   * Parses form tags' names, specifically to check if it contains
   * the '->' characters in it, and create proper id and name attributes.
   */
  static function parse_form_tag_name(&$tag_name) {
    $autoattrs = array();
    
    if (is_int(strpos($tag_name, '->'))) {
      $tag_name_p = explode('->', $tag_name);
      
      $name = $tag_name_p[0];
      foreach (range(1, count($tag_name_p)-1) as $k)
        $name .= '['.$tag_name_p[$k].']';
      
      $id = implode('_', $tag_name_p);
      
      $autoattrs = array('id' => $id, 'name' => $name);
      
    } else {
      if (strpos($tag_name, '>') === 0)
        $autoattrs['id'] = $tag_name = str_replace('>', '', $tag_name);
      
      $autoattrs['name'] = $tag_name;
    }
    
    return $autoattrs;
  }
}
?>