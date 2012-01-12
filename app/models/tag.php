<?php
include_model('tag_alias');

class Tag extends ActiveRecord {
  static $_;

  function _construct() {
    $this->tag_type = (int)$this->tag_type;
    $this->type_name = $this->type_name($this->tag_type);
  }
  
  function batch_get_tag_types_for_posts(&$posts) {
    $tags = array();
    
    foreach($posts as &$post) {
      $tags = array_merge($tags, $post->parsed_cached_tags);
    }
    return $tags;
  }
  
  function find_or_create_by_name($name) {
    # Reserve ` as a field separator for tag/summary.
    $name = str_replace(array(' ', '`'), array('_', ''),  ltrim((strtolower($name)), '-~'));
    
    $ambiguous = false;
    $tag_type = CONFIG::$tag_types['General'];
    
    if (strpos($name, 'ambiguous:') === 0) {
      $name = str_replace('ambiguous:', '', $name);
      $ambiguous = true;
    }
    
    if (is_int(strpos($name, ':'))) {
      list($type, $tag_name) = explode(':', $name);
      
      if (isset(CONFIG::$tag_types[$type])) {
        $tag_type = CONFIG::$tag_types[$type];
        $name = $tag_name;
      }
    }
    
    $tag = Tag::$_->find_by_name($name);
    
    if ($tag) {
      if ($tag_type)
        $tag->update_attribute('tag_type', $tag_type);
      if ($ambiguous)
        $tag->update_attribute('is_ambiguous', $ambiguous);
      
      return $tag;
    } else {
      return Tag::$_->create(array('name' => $name, 'tag_type' => $tag_type, 'is_ambiguous' => $ambiguous));
    }
  }
  
  function count_by_period($start, $stop, $options = array()) {
    $options['limit'] = !isset($options['limit']) || !ctype_digit((string)$options['limit']) ? 50 : (int)$options['limit'];
    !isset($options['exclude_types']) && $options['exclude_types'] = array();
    $sql = '
        (SELECT name FROM tags WHERE id = pt.tag_id) AS name,
        COUNT(pt.tag_id) AS post_count
      FROM posts p, posts_tags pt, tags t
      WHERE p.created_at BETWEEN ? AND ? AND
            p.id = pt.post_id AND
            pt.tag_id = t.id AND
            t.tag_type IN (??)
      GROUP BY pt.tag_id
      ORDER BY post_count DESC
      LIMIT '.$options['limit'];
    
    $tag_types_to_show = array_diff(Tag::$_->tag_type_indexes(), $options['exclude_types']);
    
    $counts = DB::select($sql, $start, $stop, $tag_types_to_show);
    // vde($counts);
    return $counts;
  }
  
  function tag_type_indexes() {
    // $all = array_filter(array_map(function($x) {
      // if (preg_match('/^[A-Z]/', $x))
        // return $x;
    // }, array_keys(CONFIG::$tag_types)));
    // foreach (
    $all = array_unique(array_values(CONFIG::$tag_types));
    sort($all);
    // vde($all);
    return $all;
  }
  
  function find_related($tags) {
    if (is_array($tags) && count($tags) > 1)
      return $this->calculate_related($tags);
    elseif (($tags = current($tags)) && !empty($tags)) {
      $t = Tag::$_->find_by_name($tags);
      if ($t)
        return $t->related();
    }
    
    return array();
  }
  
  function related() {
    if (gmd() > $this->cached_related_expires_on) {
      $length = ceil($this->post_count / 3);
      $length < 12 && $length = 12;
      $length > 8760 && $length = 8760;

      DB::update("tags SET cached_related = ?, cached_related_expires_on = ? WHERE id = ?", implode(",", array_flat($this->calculate_related($this->name))), gmd_math('add', 'T'.$length.'H'), $this->id);
      $this->reload();
    }

    $related = explode(',', $this->cached_related);
    
    $i = 0;
    $groups = array();
    foreach ($related as $rel) {
      $group[] = $rel;
      if ($i &1) {
        $groups[] = $group;
        $group = array();
      }
      $i++;
    }
    
    return $groups;
  }
  
  function calculate_related($tags) {
    if (!$tags)
      return array();
    check_array($tags);
    $from = array("posts_tags pt0");
    $cond = array("pt0.post_id = pt1.post_id");
    $sql = "";

    # Ignore deleted posts in pt0, so the count excludes them.
    $cond[] = "(SELECT TRUE FROM POSTS p0 WHERE p0.id = pt0.post_id AND p0.status <> 'deleted')";
    
    foreach (range(1, count($tags)) as $i)
      $from[] = "posts_tags pt${i}";
    if (count($tags) > 1) {
      foreach (range(2, count($tags)) as $i)
        $cond[] = "pt1.post_id = pt${i}.post_id";
    }
    foreach (range(1, count($tags)) as $i)
      $cond[] = "pt${i}.tag_id = (SELECT id FROM tags WHERE name = ?)";
    
    $sql .= "(SELECT name FROM tags WHERE id = pt0.tag_id) AS tag, COUNT(*) AS tag_count";
    $sql .= " FROM " . implode(', ', $from);
    $sql .= " WHERE " . implode(' AND ', $cond);
    $sql .= " GROUP BY pt0.tag_id";
    $sql .= " ORDER BY tag_count DESC LIMIT 25";
    $params = array_merge(array($sql), $tags);
    $tags = call_user_func_array('DB::select', $params);
    
    if (!$tags)
      return array();
    
    $calc = array();
    foreach ($tags as $tag)
      $calc[] = array($tag['tag'], $tag['tag_count']);
    
    return $calc;
  }
  
  /**
   * Parse methods.
   */
  function scan_query($query) {
    return array_unique(array_filter(explode(' ', strtolower($query))));
  }
  
  function parse_cast($x, $type) {
    if ($type == 'int')
      return (int)$x;
    elseif ($type == 'float')
      return (float)$x;
    elseif ($type == 'date') {
      return $x;
    }
  }
  
  function parse_helper($range, $type = 'int') {
    # "1", "0.5", "5.", ".5":
    # (-?(\d+(\.\d*)?|\d*\.\d+))
    // case range
    if (preg_match('/^(.+?)\.\.(.+)/', $range, $m))
      return array('between', $this->parse_cast($m[1], $type), $this->parse_cast($m[2], $type));

    elseif (preg_match('/^<=(.+)|^\.\.(.+)/', $range, $m))
      return array('lte', $this->parse_cast($m[1], $type));

    elseif (preg_match('/^<(.+)/', $range, $m))
      return array('lt', $this->parse_cast($m[1], $type));

    elseif (preg_match('/^>=(.+)|^(.+)\.\.$/', $range, $m))
      return array('gte', $this->parse_cast($m[1], $type));

    elseif (preg_match('/^>(.+)/', $range, $m))
      return array('gt', $this->parse_cast($m[1], $type));

    elseif (preg_match('/^(.+?)|(.+)/', $range, $m)) {
      $items = explode(',', $range);
      foreach ($items as &$val) {
        $val = $this->parse_cast($val, $type);
        unset($val);
      }
      return array('in', $items);

    } else
      return array('eq', $this->parse_cast($range, $type));
  }
  
  function parse_query($query, $options = array()) {
    $q = array();

    foreach ($this->scan_query($query) as $token) {
      if (preg_match('/^([qse])$/', $token, $m)) {
        $q['rating'] = $m[1];
        continue;
      }
      
      if (preg_match('/^(unlocked|deleted|ext|user|sub|vote|-vote|fav|md5|-rating|rating|width|height|mpixels|score|source|id|date|pool|-pool|parent|order|change|holds|pending|shown|limit):(.+)$/', $token, $m))
      {
        if ($m[1] == "user")
          $q['user'] = $m[2];
        elseif ($m[1] == "vote") {
          list($vote, $user) = explode(':', $m[2]);
          if ($user = User::$_->find_by_name($user))
            $user_id = $user->id;
          else
            $user_id = null;
          $q['vote'] = array($this->parse_helper($vote), $user_id);
        } elseif ($m[1] == "-vote") {
        
          if ($user = User::$_->find_by_name($m[2]))
            $user_id = $user->id;
          else
            $user_id = null;
          $q['vote_negated'] = $user_id;
          // $q['vote_negated'] = User.find_by_name_nocase($m[2]).id rescue nil
          if (!$q['vote_negated'])
            $q['error'] = "no user named ".$m[2];
        } elseif ($m[1] == "fav")
          $q['fav'] = $m[2];
        elseif ($m[1] == "sub")
          $q['subscriptions'] = $m[2];
        elseif ($m[1] == "md5")
          $q['md5'] = $m[2];
        elseif ($m[1] == "-rating")
          $q['rating_negated'] = $m[2];
        elseif ($m[1] == "rating")
          $q['rating'] = $m[2];
        elseif ($m[1] == "id")
          $q['post_id'] = $this->parse_helper($m[2]);
        elseif ($m[1] == "width")
          $q['width'] = $this->parse_helper($m[2]);
        elseif ($m[1] == "height")
          $q['height'] = $this->parse_helper($m[2]);
        elseif ($m[1] == "mpixels")
          $q['mpixels'] = $this->parse_helper($m[2], 'float');
        elseif ($m[1] == "score")
          $q['score'] = $this->parse_helper($m[2]);
        elseif ($m[1] == "source")
          $q['source'] = $m[2].'%';
        elseif ($m[1] == "date")
          $q['date'] = $this->parse_helper($m[2], 'date');
        elseif ($m[1] == "pool") {
          $q['pool'] = $m[2];
          if (preg_match('/^(\d+)$/', $q['pool']))
            $q['pool'] = (int)$q['pool'];
        } elseif ($m[1] == "-pool") {
          $pool = $m[2];
          
          if (preg_match('/^(\d+)$/', $pool))
            $pool = (int)$pool;
          
          $q['exclude_pools'][] = $pool;
        } elseif ($m[1] == "parent")
          $q['parent_id'] = $m[2] == "none" ? false : (int)$m[2];
        elseif ($m[1] == "order")
          $q['order'] = $m[2];
        elseif ($m[1] == "unlocked")
          $m[2] == "rating" && $q['unlocked_rating'] = true;
        elseif ($m[1] == "deleted") {
          # This naming is slightly odd, to retain API compatibility with Danbooru's "deleted:true"
          # search flag.
          if ($m[2] == "true")
            $q['show_deleted_only'] = true;
          elseif ($m[2] == "all")
            $q['show_deleted_only'] = false; # all posts, deleted or not
        } elseif ($m[1] == "ext")
          $q['ext'] = $m[2];
        elseif ($m[1] == "change")
          $q['change'] = $this->parse_helper($m[2]);
        elseif ($m[1] == "shown")
          $q['shown_in_index'] = ($m[2] == "true");
        elseif ($m[1] == "holds") {
          if ($m[2] == "true" or $m[2] == "only")
            $q['show_holds'] = 'only';
          elseif ($m[2] == "all")
            $q['show_holds'] = 'yes'; # all posts, held or not
          elseif ($m[2] == "false")
            $q['show_holds'] = 'hide';
        } elseif ($m[1] == "pending") {
          if ($m[2] == "true" or $m[2] == "only")
            $q['show_pending'] = 'only';
          elseif ($m[2] == "all")
            $q['show_pending'] = 'yes'; # all posts, pending or not
          elseif ($m[2] == "false")
            $q['show_pending'] = 'hide';
        } elseif ($m[1] == "limit")
          $q['limit'] = $m[2];
      } elseif ($token[0] == '-' && strlen($token) > 1)
        $q['exclude'][] = substr($token, 1);
      elseif ($token[0] == '~' && count($token) > 1)
        $q['include'][] = substr($token, 1);
      elseif (strstr('*', $token)) {
        // $tags = new Collection($this->cn(false), 'find', array('all', 'conditions' => array("name LIKE ?", $token), 'select' => "name, post_count", 'limit' => 25, 'order' => "post_count DESC"));
        $tags = Tag::$_->collection('find', array('all', 'conditions' => array("name LIKE ?", $token), 'select' => "name, post_count", 'limit' => 25, 'order' => "post_count DESC"));
        foreach ($tags as $i)
          $matches = $i->name;
        !$matches && $matches = array('~no_matches~');
        $q['include'] += $matches;
      } else
        $q['related'][] = $token;
    }
    
    if (!isset($options['skip_aliasing'])) {
      isset($q['exclude']) && $q['exclude'] = TagAlias::$_->to_aliased($q['exclude']);
      isset($q['include']) && $q['include'] = TagAlias::$_->to_aliased($q['include']);
      isset($q['related']) && $q['related'] = TagAlias::$_->to_aliased($q['related']);
    }
    
    return $q;
  }
  
  function type_name($code) {
    $type = array_search($code, CONFIG::$tag_types);
    // vd($code, $type);
    if ($type)
      return strtolower($type);
    // else
      // return $this->type_name_helper($tag_name);
  }
  
  function type_name_helper($tag_name) { # :nodoc:
    $type = Tag::$_->find_tag_type(array('conditions' => array("name = ?", $tag_name)));
    
    if (!$type)
      return "general";
    else
      return $this->type_name($type);
  }
  
  function type_code($name) {
    return isset(CONFIG::$tag_types[$name]) ? CONFIG::$tag_types[$name] : null;
  }
  
  function get_summary_version() {
    return 1;
    // return Cache.get("$tag_version") do 0 end
  }
  
  function get_json_summary() {
    $summary_version = $this->get_summary_version();
    // key = "tag_summary/#{summary_version}"

    // $data = Cache.get(key, 3600) do
      // data = Tag.get_json_summary_no_cache
      // data.to_json
    // end
    
    $data = $this->get_json_summary_no_cache();
    $data = to_json($data);
    
    return $data;
  }
  
  function compact_tags($tags, $max_len) {
    if (count($tags) < $max_len)
      return $tags;

    $split_tags = explode(' ', $tags);

    # Put long tags first, so we don't remove every tag because of one very long one.
    // split_tags.sort! do |a,b| b.length <=> a.length end
    usort($split_tags, function($a, $b) {return strlen($b) > strlen($a) ? $b : $a;});

    # Tag types that we're allowed to remove:
    $length = count($tags);
    // split_tags.each_index do |i|
    foreach (range(0, count($split_tags)-1) as $i) {
      $length -= strlen($split_tags[$i]) + 1;
      $split_tags[$i] = null;
      if ($length <= $max_len)
        break;
    }

    // split_tags.compact!
    // split_tags.sort!
    sort($split_tags);
    return implode(' ', $split_tags);
  }
  
  function get_json_summary_no_cache() {
    $version = Tag::$_->get_summary_version();

    $tags = DB::select("
      t.id, t.name, t.tag_type, ta.name AS alias
      FROM tags t LEFT JOIN tag_aliases ta ON (t.id = ta.alias_id AND NOT ta.is_pending)
      WHERE t.post_count > 0
      ORDER BY t.id, ta.name");

    $tags_with_type = array();
    $current_tag = "";
    $last_tag_id = null;
    foreach($tags as $tag) {
      $id = $tag["id"];
      if ($id != $last_tag_id) {
        if (!empty($last_tag_id))
          $tags_with_type[] = $current_tag;

        $last_tag_id = $id;
        $current_tag = "{$tag['tag_type']}`{$tag['name']}`";
        // $current_tag = "%s`%s`" % [tag["tag_type"], tag["name"]]
      }

      if (!empty($tag["alias"]))
        $current_tag .= $tag["alias"] . "`";
    }
    if (!empty($last_tag_id))
      $tags_with_type[] = $current_tag;

    $tags_string = implode(' ', $tags_with_type) . " ";
    return array('version' => $version, 'data' => $tags_string);
  }
}
?>