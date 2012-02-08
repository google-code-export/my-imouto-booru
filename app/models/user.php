<?php
include_model('ban, tag, user_blacklisted_tag');

has_one('ban', array('foreign_key' => 'user_id'));
has_one('user_blacklisted_tag');
belongs_to('avatar_post', array('model_name' => "Post", 'foreign_key' => 'avatar_post_id'));

before('validation', 'commit_secondary_languages');
before('create', 'can_signup, set_role');
before('save', 'encrypt_password');

after('create', 'set_default_blacklisted_tags, increment_count');
after('save', 'commit_blacklists');
after('destroy', 'decrement_count');

validates(array(
  'name' => array(
    'length' => '2..20',
    'format' => array('/\A[^\s;,]+\Z/', 'on' => 'create', 'message' => 'cannot have whitespace, commas, or semicolons'),
    'uniqueness' => array(true,'on' => 'create')
  ),
  'password' => array(
    'length' => array('>=5', 'if' => array('property_exists' => 'password')),
    'confirmation' => true,
  )
));

// #      validates_format_of :name, :with => /^(Anonymous|[Aa]dministrator)/, :on => :create, :message => "this is a disallowed username"
// m.after_save :update_cached_name if CONFIG["enable_caching"]


// m.has_many :tag_subscriptions, :dependent => :delete_all, :order => "name"
// m.validates_format_of :language, :with => /^([a-z\-]+)|$/
// m.validates_format_of :secondary_languages, :with => /^([a-z\-]+(,[a-z\0]+)*)?$/
class User extends ActiveRecord {
  static $current;
  
  function _construct() {
    if(isset($this->is_anonymous))
      return;
    elseif(isset($this->id))
      $this->is_anonymous = false;
    
    // if(CONFIG::show_samples)
      // $this->show_samples = false;
  }
  
  static function authenticate($name, $pass) {
    return self::authenticate_hash($name, md5($name.$pass));
  }
  
  function user_info_cookie() {
    return $this->id . ';' . $this->level . ';' . ($this->use_browser ? "1":"0");
  }

  static function authenticate_hash($name, $pass) {
    $user = parent::find('first', array('conditions' => array("lower(name) = lower(?) AND password_hash = ?", $name, $pass)));
    
    return $user;
  }
  
  function has_permission($record, $foreign_key = 'user_id') {
    if($this->level >= 40 || $record->$foreign_key == $this->id)
      return true;
    else
      return false;
  }
  
  function pretty_name($name = null) {
    // $name = !isset($this->name) ? $name : $this->name;
    return str_replace('_', ' ', $this->name);
  }
  
  function pretty_level() {
    return array_search($this->level, CONFIG::$user_levels);
  }
  
  static function save_cookies($user) {
    cookie_put('login', $user->name);
    cookie_put('pass_hash', $user->password_hash);
    $_SESSION[CONFIG::app_name]['user_id'] = $user->id;
  }
  
  function secondary_language_array() {
    if (!isset($this->secondary_language_array))
      $this->secondary_language_array = explode(',', $this->secondary_languages);
    return $this->secondary_language_array;
  }
  
  function blacklisted_tags() {
    if ($this->user_blacklisted_tag)
      return $this->user_blacklisted_tag->tags;
    else
      return null;
  }

  function blacklisted_tags_array() {
    if ($this->user_blacklisted_tag)
      return explode("\r\n", $this->user_blacklisted_tag->tags);
    else
      return array();
    // user_blacklisted_tags.map {|x| x.tags}
  }

  function commit_blacklists() {
    if (!empty($this->user_blacklisted_tag) && isset($this->blacklisted_tags))
      $this->user_blacklisted_tag->update_attribute('tags', $this->blacklisted_tags);
    
    // if ($this->blacklisted_tags) {
      // $this->user_blacklisted_tags = null;

      // @blacklisted_tags.scan(/[^\r\n]+/).each do |tags|
        // user_blacklisted_tags.create(:tags => tags)
      // end
    // }
  }
  
  function can_change($record, $attribute) {
    if (self::is('>=40'))
      return true;
    
    $method = "can_change_${attribute}";
    
    if (method_exists($record, $method))
      return $record->$method($this);
    elseif (method_exists($record, 'can_change'))
      return $record->can_change($this, $attribute);
    else
      return false;
  }
  
  static function is($comparison) {
    $current_level = (int)self::$current->level;
    $r = num_compare($current_level, $comparison);
    return $r;
  }
  
  function set_default_blacklisted_tags() {
    // foreach (CONFIG::$default_blacklists as $b)
    UserBlacklistedTag::create(array('user_id' => $this->id, 'tags' => implode("\r\n", CONFIG::$default_blacklists)));
    // CONFIG["default_blacklists"].each do |b|
      // UserBlacklistedTag.create(:user_id => self.id, :tags => b)
    // end
  }

  function uploaded_tags($options = array()) {
    $type = !empty($options['type']) ? $options['type'] : null;

    // if CONFIG["enable_caching"]
      // uploaded_tags = Cache.get("uploaded_tags/#{id}/#{type}")
      // return uploaded_tags unless uploaded_tags == nil
    // end

    // if RAILS_ENV == "test"
      // # disable filtering in test mode to simplify tests
      // popular_tags = ""
    // else
      $popular_tags = implode(', ', DB::select_values("id FROM tags WHERE tag_type = " . CONFIG::$tag_types['General'] . " ORDER BY post_count DESC LIMIT 8"));
      if ($popular_tags)
        $popular_tags = "AND pt.tag_id NOT IN (${popular_tags})";
    // end

    if ($type) {
      $type = (int)$type;
      $sql = "
        (SELECT name FROM tags WHERE id = pt.tag_id) AS tag, COUNT(*) AS count
        FROM posts_tags pt, tags t, posts p
        WHERE p.user_id = {$this->id}
        AND p.id = pt.post_id
        AND pt.tag_id = t.id
        {$popular_tags}
        AND t.tag_type = {$type}
        GROUP BY pt.tag_id
        ORDER BY count DESC
        LIMIT 6
      ";
    } else {
      $sql = "
        (SELECT name FROM tags WHERE id = pt.tag_id) AS tag, COUNT(*) AS count
        FROM posts_tags pt, posts p
        WHERE p.user_id = {$this->id}
        AND p.id = pt.post_id
        ${popular_tags}
        GROUP BY pt.tag_id
        ORDER BY count DESC
        LIMIT 6
      ";
    }

    $uploaded_tags = DB::select($sql);

    // if CONFIG["enable_caching"]
      // Cache.put("uploaded_tags/#{id}/#{type}", uploaded_tags, 1.day)
    // end

    return $uploaded_tags;
  }
  
  function voted_tags($options = array()) {
    $type = !empty($options['type']) ? $options['type'] : null;

    // if (CONFIG::enable_caching) {
      // favorite_tags = Cache.get("favorite_tags/#{id}/#{type}")
      // return favorite_tags unless favorite_tags == nil
    // }

    // if RAILS_ENV == "test"
      // # disable filtering in test mode to simplify tests
      // popular_tags = ""
    // else
      $popular_tags = implode(', ', DB::select_values("id FROM tags WHERE tag_type = " . CONFIG::$tag_types['General'] . " ORDER BY post_count DESC LIMIT 8"));
      if ($popular_tags)
        $popular_tags = "AND pt.tag_id NOT IN (${popular_tags})";
    // end

    if ($type) {
      $type = (int)$type;
      $sql = "
        (SELECT name FROM tags WHERE id = pt.tag_id) AS tag, SUM(v.score) AS sum
        FROM posts_tags pt, tags t, post_votes v
        WHERE v.user_id = {$this->id}
        AND v.post_id = pt.post_id
        AND pt.tag_id = t.id
        {$popular_tags}
        AND t.tag_type = {$type}
        GROUP BY pt.tag_id
        ORDER BY sum DESC
        LIMIT 6
      ";
    } else {
      $sql = "
        (SELECT name FROM tags WHERE id = pt.tag_id) AS tag, SUM(v.score) AS sum
        FROM posts_tags pt, post_votes v
        WHERE v.user_id = {$this->id}
        AND v.post_id = pt.post_id
        ${popular_tags}
        GROUP BY pt.tag_id
        ORDER BY sum DESC
        LIMIT 6
      ";
    }

    $favorite_tags = DB::select($sql);

    // if CONFIG["enable_caching"]
      // Cache.put("favorite_tags/#{id}/#{type}", favorite_tags, 1.day)
    // end

    return $favorite_tags;
  }
  
  function logout(){
    cookie_remove("login");
    cookie_remove("pass_hash");
    cookie_remove("user_info");
    cookie_remove("held_post_count");
    cookie_remove("show_advanced_editing");
    session_unset();
  }
  
  /* Post Methods { */
  function recent_uploaded_posts() {
    return Post::find_by_sql(array("SELECT p.* FROM posts p WHERE p.user_id = {$this->id} AND p.status <> 'deleted' ORDER BY p.id DESC LIMIT 6"));
  }

  function recent_favorite_posts() {
    return Post::find_by_sql(array("SELECT p.* FROM posts p, post_votes v WHERE p.id = v.post_id AND v.user_id = {$this->id} AND v.score = 3 AND p.status <> 'deleted' ORDER BY v.updated_at DESC LIMIT 6"));
  }

  function favorite_post_count($options = array()) {
    return DB::count("post_votes v WHERE v.user_id = {$this->id} AND v.score = 3");
  }
  
  function post_count() {
    return Post::count(array('conditions' => array("user_id = ? AND status = 'active'", $id)));
    // @post_count ||= Post.count(:conditions => ["user_id = ? AND status = 'active'", id])
  }

  function held_post_count() {
    // version = Cache.get("$cache_version").to_i
    // key = "held-post-count/v=#{version}/u=#{self.id}"

    // return Cache.get(key) {
      // Post.count(:conditions => ["user_id = ? AND is_held AND status <> 'deleted'", self.id])
    // }.to_i
    $count = Post::count(array('conditions' => array("user_id = ? AND is_held AND status <> 'deleted'", $this->id)));
    
    return $count;
  }
  
  /* } level methods { */
  function can_signup() {
    if (!CONFIG::enable_signups) {
      $this->record_errors->add('signups', 'are disabled');
      return false;
    }
    return true;
  }
  
  function set_role() {
    
    if (CONFIG::enable_account_email_activation)
      $this->level = CONFIG::$user_levels["Unactivated"];
    else
      $this->level = CONFIG::starting_level;

    $this->last_logged_in_at = gmd();
  }
  /* } Count methods { */
  function fast_count() {
    return DB::select_value("row_count FROM table_data WHERE name = 'users'");
  }
  
  function increment_count() {
    DB::update("table_data set row_count = row_count + 1 where name = 'users'");
  }

  function decrement_count() {
    DB::update("table_data set row_count = row_count - 1 where name = 'users'");
  }
  /* } Password methods { */
  
  function encrypt_password() {
    if ($this->password)
      $this->password_hash = md5($this->name.$this->password);
  }
  
  function reset_password() {
    $consonants = "bcdfghjklmnpqrstvqxyz";
    $vowels = "aeiou";
    $pass = "";

    foreach (range(1, 4) as $i) {
      $pass .= substr($consonants, rand(0, 20), 1);
      $pass .= substr($vowels, rand(0, 4), 1);
    }

    $pass .= rand(0, 100);
    DB::update("users SET password_hash = ? WHERE id = ?", md5($pass), $this->id);
    return $pass;
  }
  
  /* } Avatar methods { */

  static function clear_avatars($post_id) {
    return DB::update("users SET avatar_post_id = NULL WHERE avatar_post_id = ?", $post_id);
  }

  function avatar_url() {
    return CONFIG::url_base . "/data/avatars/{$this->id}.jpg";
  }

  function has_avatar() {
    return !empty($this->avatar_post_id);
  }

  function avatar_path() {
    return ROOT . "public/data/avatars/" . $this->id . ".jpg";
  }
  
  function set_avatar($params) {
    $post = Post::find($params['post_id']);
    if (!$post->can_be_seen_by($this)) {
      $this->record_errors->add('access', "denied");
      return false;
    }
    
    // vde($params);
    
    if ($params['top'] < 0 or $params['top'] > 1 or
      $params['bottom'] < 0 or $params['bottom'] > 1 or
      $params['left'] < 0 or $params['left'] > 1 or
      $params['right'] < 0 or $params['right'] > 1 or
      $params['top'] >= $params['bottom'] or
      $params['left'] >= $params['right'])
    {
      $this->record_errors->add('parameter', "error");
      return false;
    }

    $tempfile_path = ROOT . "public/data/" . $this->id . ".avatar.jpg";
    
    $use_sample = $post->has_sample();
    if ($use_sample) {
      $image_path = $post->sample_path();
      $image_ext = "jpg";
      $size = $this->reduce_and_crop($post->sample_width, $post->sample_height, $params);

      # If we're cropping from a very small region in the sample, use the full
      # image instead, to get a higher quality image.
      if (($size['crop_bottom'] - $size['crop_top'] < CONFIG::avatar_max_height) or
        ($size['crop_right'] - $size['crop_left'] < CONFIG::avatar_max_width))
        $use_sample = false;
    }

    if (!$use_sample) {
      $image_path = $post->file_path();
      $image_ext = $post->file_ext;
      $size = $this->reduce_and_crop($post->width, $post->height, $params);
    }
    
    try {
      Danbooru::resize($image_ext, $image_path, $tempfile_path, $size, 95);
    } catch (Exception $x) {
      if (file_exists($tempfile_path))
        unlink($tempfile_path);

      $this->record_errors->add("avatar", "couldn't be generated (" . $x->getMessage() . ")");
      return false;
    }
    
    rename($tempfile_path, $this->avatar_path());
    chmod($this->avatar_path(), 0775);
    
    $this->update_attributes(array(
      'avatar_post_id' => $params['post_id'],
      'avatar_top' => $params['top'],
      'avatar_bottom' => $params['bottom'],
      'avatar_left' => $params['left'],
      'avatar_right' => $params['right'],
      'avatar_width' => $size['width'],
      'avatar_height' => $size['height'],
      'avatar_timestamp' => gmd()
    ));
    
    return true;
  }
  
  function reduce_and_crop($image_width, $image_height, $params) {
    $cropped_image_width = $image_width * ($params['right'] - $params['left']);
    $cropped_image_height = $image_height * ($params['bottom'] - $params['top']);

    $size = Danbooru::reduce_to(array('width' => $cropped_image_width, 'height' => $cropped_image_height), array('width' => CONFIG::avatar_max_width, 'height' => CONFIG::avatar_max_height), 1, true);
    $size['crop_top'] = $image_height * $params['top'];
    $size['crop_bottom'] = $image_height * $params['bottom'];
    $size['crop_left'] = $image_width * $params['left'];
    $size['crop_right'] = $image_width * $params['right'];
    return $size;
  }
}
?>