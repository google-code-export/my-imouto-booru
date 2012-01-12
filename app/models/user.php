<?php
include_model('ban', 'tag', 'user_blacklisted_tag');

has_one('ban', array('foreign_key' => 'user_id'));
has_one('user_blacklisted_tag');
belongs_to('avatar_post', array('model_name' => "Post"));

before('validation', 'commit_secondary_languages');
before('create', 'set_role');
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

// m.has_many :user_blacklisted_tags, :dependent => :delete_all, :order => :id


// #      validates_format_of :name, :with => /^(Anonymous|[Aa]dministrator)/, :on => :create, :message => "this is a disallowed username"
// m.after_save :update_cached_name if CONFIG["enable_caching"]


// m.has_many :tag_subscriptions, :dependent => :delete_all, :order => "name"
// m.validates_format_of :language, :with => /^([a-z\-]+)|$/
// m.validates_format_of :secondary_languages, :with => /^([a-z\-]+(,[a-z\0]+)*)?$/
class User extends ActiveRecord {
  static $_;
  static $current;
  
  function _construct() {
    if(isset($this->is_anonymous))
      return;
    elseif(isset($this->id))
      $this->is_anonymous = false;
    
    // if(CONFIG::show_samples)
      // $this->show_samples = false;
  }
  
  
  function has_avatar() {
    return $this->avatar_post_id ? true : false;
  }
  
  function avatar_post(&$user = null) {
    if($user === null)
      $user =& User::$current;
    
    return Post::$_->find($user->avatar_post_id);
  }
  
  function avatar_url() {
    return "/data/avatars/".$this->id.".jpg";
  }
  
  static function clear_avatars($post_id) {
    DB::update("users SET avatar_post_id = NULL WHERE avatar_post_id = ?", $post_id);
  }
  
  function authenticate($name, $pass) {
    return $this->authenticate_hash($name, md5($name.$pass));
  }
  
  function user_info_cookie() {
    return $this->id . ';' . $this->level . ';' . ($this->use_browser ? "1":"0");
  }

  function authenticate_hash($name, $pass) {
    $user = User::$_->find('first', array('conditions' => array("lower(name) = lower(?) AND password_hash = ?", $name, $pass)));
    
    return $user;
  }
  
  function has_permission($record, $foreign_key = 'user_id') {
    if(self::$current->is('>=40') || $record->$foreign_key == $this->id)
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
  
  function save_cookies($user) {
    Cookies::put('login', $user->name);
    Cookies::put('pass_hash', $user->password_hash);
    $_SESSION[CONFIG::app_name]['user_id'] = $user->id;
    // Cookies::$list['login'] = {:value => user.name, :expires => 1.year.from_now}
    // Cookies::$list['pass_hash'] = {:value => user.password_hash, :expires => 1.year.from_now}
    // session['user_id'] = user.id
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
  
  function can_change(&$record, $attribute) {
    if ($this->is('>=40'))
      return true;
    
    $method = "can_change_${attribute}";
    
    if (method_exists($record, $method))
      return $record->$method($this);
    elseif (method_exists($record, 'can_change'))
      return $record->can_change($this, $attribute);
    else
      return false;
  }
  
  function is($comparison) {
    $current_level = !empty($this->level) ? (int)$this->level : (int)User::$current->level;
    
    $r = num_compare($current_level, $comparison);
    
    return $r;
  }
  
  function set_default_blacklisted_tags() {
    // foreach (CONFIG::$default_blacklists as $b)
    UserBlacklistedTag::$_->create(array('user_id' => $this->id, 'tags' => implode("\r\n", CONFIG::$default_blacklists)));
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
    // return new Collection('Post', 'find_by_sql', "SELECT p.* FROM posts p WHERE p.user_id = {$this->id} AND p.status <> 'deleted' ORDER BY p.id DESC LIMIT 6");
    return Post::$_->collection('find_by_sql', "SELECT p.* FROM posts p WHERE p.user_id = {$this->id} AND p.status <> 'deleted' ORDER BY p.id DESC LIMIT 6");
  }

  function recent_favorite_posts() {
    // return new Collection('Post', 'find_by_sql', "SELECT p.* FROM posts p, post_votes v WHERE p.id = v.post_id AND v.user_id = {$this->id} AND v.score = 3 AND p.status <> 'deleted' ORDER BY v.updated_at DESC LIMIT 6");
    return Post::$_->collection('find_by_sql', "SELECT p.* FROM posts p, post_votes v WHERE p.id = v.post_id AND v.user_id = {$this->id} AND v.score = 3 AND p.status <> 'deleted' ORDER BY v.updated_at DESC LIMIT 6");
  }

  function favorite_post_count($options = array()) {
    return DB::count("post_votes v WHERE v.user_id = {$this->id} AND v.score = 3");
  }
  
  function post_count() {
    return Post::$_->count(array('conditions' => array("user_id = ? AND status = 'active'", $id)));
    // @post_count ||= Post.count(:conditions => ["user_id = ? AND status = 'active'", id])
  }

  function held_post_count() {
    // version = Cache.get("$cache_version").to_i
    // key = "held-post-count/v=#{version}/u=#{self.id}"

    // return Cache.get(key) {
      // Post.count(:conditions => ["user_id = ? AND is_held AND status <> 'deleted'", self.id])
    // }.to_i
    return Post::$_->count(array('conditions' => array("user_id = ? AND is_held AND status <> 'deleted'", $this->id)));
  }
  
  /* } level methods { */
  function set_role() {
    // if (User::$_->fast_count == 0)
      // $this->level = CONFIG::$user_levels["Admin"]
    // else
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
}
?>