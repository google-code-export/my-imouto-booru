<?php
include_model('note', 'flagged_post_detail', 'post_votes', 'TagImplication');

belongs_to('user');

has_one('flag_detail', array('model_name' => "FlaggedPostDetail"));
has_many('notes', array('order' => 'id DESC', 'conditions' => array('is_active = 1')));
before('save', 'commit_tags');
before('create', 'before_creation, set_index_timestamp');
after('create', 'after_creation');
after('delete', 'clear_avatars, give_favorites_to_parent');
has_many('comments', array('order' => "id"));
// m.after_save :save_post_history
// m.has_many :tag_history, :model_name => "PostTagHistory", :table_name => "post_tag_histories", :order => "id desc"
// m.versioned :source, :default => ""
// m.versioned :cached_tags

/* Parent parameters */
after('save', 'update_parent');
// m.validate :validate_parent
// m.versioned :parent_id, :default => nil
has_many('children', array('model_name' => 'Post', 'order' => 'id', 'foreign_key' => 'parent_id', 'conditions' => array("status != 'deleted'")));

before('validation_on_create', 'download_source, ensure_tempfile_exists, determine_content_type, validate_content_type, generate_hash, set_image_dimensions, set_image_status, check_pending_count, generate_sample, generate_jpeg, generate_preview, move_file');

class Post extends ActiveRecord {
  static $_;
  
  function _construct() {
    $prefix = !CONFIG::download_filename_prefix ? null : CONFIG::download_filename_prefix.' ';
    $abmd5 = substr($this->md5, 0, 2);
    
    if ($this->id) {
      $row = DB::select_row("u.name AS author, GROUP_CONCAT(CONCAT(t.name,':',t.tag_type) SEPARATOR ' ') AS cached_tags
        FROM posts p
        JOIN posts_tags pt ON p.id = pt.post_id
        JOIN tags t ON pt.tag_id = t.id
        JOIN users u ON p.user_id = u.id
        WHERE pt.post_id = " . $this->id);
      
      $this->cached_tags = $row['cached_tags'];
      $this->author = $row['author'];
    }
    
    $this->parsed_cached_tags = $this->parse_cached_tags();
    
    $this->tags = $this->tag_names();
    
    $this->parent_id = $this->parent_id ? (int)$this->parent_id : null;
    
    $this->file_url = $this->file_url();
    $this->jpeg_url = $this->jpeg_url();
    
    $this->sample_url = $this->sample_url();
    $this->preview_url = $this->preview_url();
    
    if ($this->source == null)
      $this->source = '';
    
    $bools = array('is_held', 'has_children', 'is_shown_in_index');
    foreach ($bools as $bool)
      isset($this->$bool) && $this->$bool = (bool)$this->$bool;
    
    foreach($this as $n => $p) {
      if(is_numeric($p))
        $this->$n = (int)$p;
    }
    
    # For /post/browse
    !$this->sample_width && $this->sample_width = $this->width;
    !$this->sample_height && $this->sample_height = $this->height;
    !$this->jpeg_width && $this->jpeg_width = $this->width;
    !$this->jpeg_height && $this->jpeg_height = $this->height;
  }
  
  function api_attributes() {
    $api_attributes = array('id', 'tags', 'created_at', 'creator_id', 'author', 'change', 'source', 'score', 'md5', 'file_size', 'file_url', 'is_shown_in_index', 'preview_url', 'preview_width', 'preview_height', 'actual_preview_width', 'actual_preview_height', 'sample_url', 'sample_width', 'sample_height', 'sample_file_size', 'jpeg_url', 'jpeg_width', 'jpeg_height', 'jpeg_file_size', 'rating', 'has_children', 'parent_id', 'status', 'width', 'height', 'is_held', 'frames_pending_string', 'frames_string');
    
    $api_attributes = array_fill_keys($api_attributes, '');
    
    # Creating these manually because they're not implemented yet.
    $api_attributes['frames_pending'] = $api_attributes['frames'] = array();
    # Column ´change_seq´ still not created in database.
    $api_attributes['change'] = 0;
    
    foreach (array_keys(get_object_vars($this)) as $name) {
      if ($name == 'user_id')
        $api_attributes['creator_id'] = $this->$name;
      elseif ($name == 'sample_size')
        $api_attributes['sample_file_size'] = $this->$name;
      elseif ($name == 'jpeg_size')
        $api_attributes['jpeg_file_size'] = $this->$name;
      elseif ($name == 'cached_tags')
        $api_attributes['tags'] = $this->tags;
      elseif ($name == 'created_at') {
        $api_attributes['created_at'] = datetime_to_timestamp($this->created_at);
      } elseif (array_key_exists($name, $api_attributes))
        $api_attributes[$name] = $this->$name;
    }
    
    if ($this->status == "deleted") {
      unset($api_attributes['sample_url']);
      unset($api_attributes['jpeg_url']);
      unset($api_attributes['file_url']);
    }

    if (($this->status == "flagged" or $this->status == "deleted" or $this->status == "pending") && $this->flag_detail) {
      $api_attributes['flag_detail'] = $this->flag_detail->api_attributes();
      $this->flag_detail->hide_user = ($this->status == "deleted" and !User::$current->is('>=40'));
    }
    
    # For post/similar results:
    // if not similarity.nil?
      // ret[:similarity] = similarity
    // end
    
    return $api_attributes;
  }
  
  # TODO: this function could be somewhere else.
  # Tries to verify that the requests are for /post/browse AND if CONFIG::fake_sample_url is enabled.
  private function fake_samples_for_browse() {
    if (!CONFIG::fake_sample_url)
      return false;
    if (CONFIG::fake_sample_url && Request::$get && Request::$controller == 'post' && Request::$action == 'index' && Request::$format == 'json')
      return true;
  }
  
  function _get($prop) {
    // if($prop == 'author') {
      // $this->api_attributes['author'] = $this->user->name;
      // return $this->api_attributes['author'];
    // }
    
    // elseif(isset($this->api_attributes) && array_key_exists($prop, $this->api_attributes))
      // return $this->api_attributes[$prop];
  }
  
  function _call($n, $p) {
    switch($n) {
      # Checking status: $post->is_pending();
      case (strpos($n, 'is_') === 0):
        $status = str_replace('is_', '', $n);
        return $this->status == $status;
      break;
    }
  }
  
  function to_json() {
    return to_json($this->api_attributes());
  }
  
  function to_xml($opts = array()) {
    return to_xml($this->api_attributes(), 'post', $opts);
  }
  
  function recalculate_cached_tags() {
    // {"saki_(ar_tonelico)":"character","ar_tonelico_3":"copyright","ar_tonelico":"copyright","tagme":"general"}
  }
  
  function normalized_source() {
    if(preg_match('~pixiv\.net\/img\/~', $this->source)) {
      preg_match('~/(\d+)\.\w+$/~', $this->source, $m);
      return "http://www.pixiv.net/member_illust.php?mode=medium&illust_id=".$m[1];
    } else
      return $this->source;
  }
  
  function set_index_timestamp() {
    $this->index_timestamp = gmd();
  }
  
  private function parse_moe_filename_tags() {
    if (empty($this->tempfile_name) || !preg_match("/^moe [\d]+ (.*)/", $this->tempfile_name, $m))
      return;
    
    if ($this->tags)
      $tags = explode(' ', $this->tags);
    
    foreach (explode(' ', $m[1]) as $tag)
      $tags[] = $tag;
    
    $this->tags = implode(' ', array_filter(array_unique($tags)));
  }
  
  private function parse_moe_filename_source() {
    if (!preg_match("/^moe ([\d]+) /", $this->tempfile_name, $m))
      return;
    
    $this->source = 'http://oreno.imouto.org/post/show/'.$m[1];
  }
  
  protected function before_creation() {
    $this->upload = !empty($_FILES['post']['tmp_name']['file']) ? true : false;
    
    if (CONFIG::tags_from_moe_filename)
      $this->parse_moe_filename_tags();
    
    if (CONFIG::source_from_moe_filename)
      $this->parse_moe_filename_source();
    
    if (!$this->rating)
      $this->rating = CONFIG::default_rating_upload;
    
    $this->rating = strtolower($this->rating);
    
    if (!empty($this->tags))
      $this->creation_tags = $this->tags;
    
    $this->cached_tags = 'tagme:0';
    $this->parsed_cached_tags = $this->parse_cached_tags();
    
    !$this->parent_id && $this->parent_id = null;
    !$this->source && $this->source = null;
    
    $this->random = mt_rand();
  }
  
  protected function after_creation() {
    $tagme = Tag::$_->find_or_create_by_name('tagme');
    DB::insert('posts_tags VALUES (?, ?)', $this->id, $tagme->id);
    
    if (!empty($this->creation_tags)) {
      $this->old_tags = 'tagme';
      $this->tags = $this->creation_tags;
      $this->commit_tags();
    }
    
    $this->save();
  }
  
  function can_user_delete(&$user = null) {
    if(!$user)
      $user =& User::$current;
    
    if(!$user->has_permission($this))
      return false;
    elseif (!$user->is('>=40') && !$this->is_held && (gmd() - strtotime($this->created_at)) > 60*60*24)
      return false;

    return true;
  }
  
  function static_destroy_with_reason($id, $reason, $current_user) {
    $post = Post::$_->find($id);
    $post->destroy_with_reason($reason, $current_user);
  }
  
  function destroy_with_reason($reason, $current_user) {
    $this->flag($reason, $current_user->id);
    
    // if ($this->flag_detail)
      // $this->flag_detail->update_attributes(array('is_resolved' => true));
    
    $this->first_delete();
  }
  
  function first_delete() {
    $this->update_attribute('status', "deleted");
    $this->run_callbacks('after_delete');
  }
  
  function delete_from_database() {
    $this->delete_file();
    DB::delete("posts WHERE id = ?", $this->id);
  }
  
  function undelete() {
    if ($this->status == "active") return;
    $this->update_attribute('status', "active");
    $this->run_callbacks('after_undelete');
  }

  function clear_avatars() {
    User::clear_avatars($this->id);
  }
  
  function flag($reason, $creator_id) {
    $this->update_attribute('status', "flagged");
    $this->set_flag_detail($reason, $creator_id);
  }
  
  function set_flag_detail($reason, $creator_id) {
    if ($this->flag_detail) {
      $this->flag_detail->update_attributes(array('reason' => $reason, 'user_id' => $creator_id, 'created_at' => gmd()));
    } else {
      FlaggedPostDetail::$_->create(array('post_id' => $this->id, 'reason' => $reason, 'user_id' => $creator_id, 'is_resolved' => false));
    }
  }
  

  
  // function is($status) {
    // if($this->status == $status)
      // return true;
  // }
  
  function is_flash() {
    if(in_array($this->file_ext, array('swf', 'flv')))
      return true;
  }
  
  function can_be_seen_by(&$user = null, $options = array()) {
    if (empty($options['show_deleted']) && $this->status == 'deleted')
      return;
    
    return CONFIG::can_see_post($user, $this);
  }
  
  function active_notes() {
    $notes = array();
    
    if ($this->notes)
      $notes = $this->notes->select(array('is_active' => 1));
    
    return $notes;
  }
  
  /**
   * Comment methods {
   */
  
  function recent_comments() {
    // return new Collection('Comment', 'find', 'all', array('conditions' => array("post_id = ?", $this->id), 'order' => "id desc", 'limit' => 6));
    return Comment::$_->collection('find', array('conditions' => array("post_id = ?", $this->id), 'order' => "id desc", 'limit' => 6));
  }
  
  /** }
   * File methods? {
   */
  // function has_sample() {
    // if($this->sample_width)
      // return true;
  // }
  
  function pretty_file_name($options = array()) {
    # Include the post number and tags.  Don't include too many tags for posts that have too
    # many of them.
    empty($options['type']) && $options['type'] = 'image';
    $tags = null;
    # If the filename is too long, it might fail to save or lose the extension when saving.
    # Cut it down as needed.  Most tags on moe with lots of tags have lots of characters,
    # and those tags are the least important (compared to tags like artists, circles, "fixme",
    # etc).
    #
    # Prioritize tags:
    # - remove artist and circle tags last; these are the most important
    # - general tags can either be important ("fixme") or useless ("red hair")
    # - remove character tags first; 

   
    if ($options['type'] == 'sample') {
      $tags = "sample";
    } else
     $tags = Tag::$_->compact_tags($this->tags, 150);
    
    # Filter characters.
    $tags = str_replace(array('/', '?'), array('_', ''), $tags);

    $name = "{$this->id} $tags";
    if (CONFIG::download_filename_prefix)
      $name = CONFIG::download_filename_prefix . " " . $name;
    
    return $name;
  }
  
  function use_sample(&$user = null) {
    if(!$user)
      $user = &User::$current;
    
    if ($user && !$user->show_samples)
      return false;
    else
      return CONFIG::image_samples && $this->has_sample();
  }
   
  function get_sample_width(&$user = null) {
    if(!$user)
      $user = &User::$current;
    return $this->use_sample($user) ? $this->sample_width : $this->width;
  }
  
  function get_sample_height(&$user = null) {
    if(!$user) $user = &User::$current;
    return $this->use_sample() ? $this->sample_height : $this->height;
  }
  


  function is_image() {
    if(in_array($this->file_ext, array('jpg', 'jpeg', 'gif', 'png')))
      return true;
  }
  
  function raw_preview_dimensions($prop = null) {
    if ($this->is_image()) {
      $dim = Danbooru::reduce_to(array('width' => $this->width, 'height' => $this->height), array('width' => 300, 'height' => 300));
      $dim = array($dim['width'], $dim['height']);
    } else
      $dim = array(300, 300);
    
    if (!$prop)
      return $dim;
    elseif ($prop == 'w')
      return $dim[0];
    elseif ($prop == 'h')
      return $dim[1];
  }
  
  function preview_dimensions($prop = null) {
    if ($this->is_image()) {
      $dim = Danbooru::reduce_to(array('width' => $this->width, 'height' => $this->height), array('width' => 150, 'height' => 150));
      $dim = array($dim['width'], $dim['height']);
    } else
      $dim = array(150, 150);
    
    if (!$prop)
      return $dim;
    elseif ($prop == 'w')
      return $dim[0];
    elseif ($prop == 'h')
      return $dim[1];
  }
  
  # Automatically download from the source if it's a URL.
  function download_source() {
    if (empty($this->source))
      return;
    
    if ((strpos('http://', $this->source) !== 0) || !empty($this->file_ext))
      return;
    // return; if received_file
    
    // begin
      // Danbooru.http_get_streaming(source) do |response|
        // File.open(tempfile_path, "wb") do |out|
          // response.read_body do |block|
            // out.write(block)
          // end
        // end
      // end

      // if self.source.to_s =~ /^http/ and self.source.to_s !~ /pixiv\.net/ then
        // #self.source = "Image board"
        // self.source = ""
      // end

      // return true
    // rescue SocketError, URI::Error, Timeout::Error, SystemCallError => x
      // delete_tempfile
      // record_errors.add "source", "couldn't be opened: #{x}"
      // return false
    // end
  }
  
  function ensure_tempfile_exists() {
    if (empty($_FILES['post']['name']['file']) || $_FILES['post']['error']['file'] === UPLOAD_ERR_OK)
      return;
    
    $this->record_errors->add('file', "not found, try uploading again");
    return false;
  }
  
  function determine_content_type() {
    if (!file_exists($this->tempfile_path)) {
      $this->record_errors->add_to_base("No file received");
      return false;
    }
    
    $this->tempfile_ext = pathinfo($this->tempfile_name, PATHINFO_EXTENSION);
    $this->tempfile_name = pathinfo($this->tempfile_name, PATHINFO_FILENAME);
    
    if (class_exists('finfo')) {
      $finfo = new finfo(FILEINFO_MIME_TYPE);
      $this->mime_type = $finfo->file($this->tempfile_path);
    } else {
      $this->tempfile_ext == 'jpeg' && $this->tempfile_ext = 'jpg';
      $this->mime_type = array_search($this->tempfile_ext, CONFIG::$allowed_mime_types);
    }
    
    is_bool($this->mime_type) && $this->mime_type = 'Unkown mime-type';
  }
  
  function validate_content_type() {
    if (!array_key_exists($this->mime_type, CONFIG::$allowed_mime_types)) {
      $this->record_errors->add('file', 'is an invalid content type: ' . $this->mime_type);
      return false;
    }
    
    $this->file_ext = CONFIG::$allowed_mime_types[$this->mime_type];
  }
  
  # TODO: enable crc32.
  function regenerate_hash() {
    
    $path = !empty($this->tempfile_path) ? $this->tempfile_path : $this->file_path();
    
    if (!file_exists($path)) {
      
      $this->record_errors->add('file', "not found");
      return false;
    }
    
    $this->md5 = md5_file($path);
    // $this->crc32 = ...............
    return true;
  }
  
  function generate_hash() {
    if (!$this->regenerate_hash())
      return false;
    
    if (Post::$_->exists("md5 = ?", $this->md5)) {
      $this->record_errors->add('md5', "already exists");
      return false;
    } else
      return true;
  }
  
  function set_image_dimensions() {
    
     // or $this->is_flash()
    if ($this->is_image()) {
      list($this->width, $this->height) = getimagesize($this->tempfile_path);
    }
    $this->file_size = filesize($this->tempfile_path);
  }
  
  function set_image_status() {
    if(!$this->image_is_too_small())
      return true;
    
    if ($this->user->is('>=33'))
      return;

    $this->status = "pending";
    $this->status_reason = "low-res";
    return true;
  }
  
  function image_is_too_small() {
    if (!CONFIG::min_mpixels) return false;
    if (empty($this->width)) return false;
    if ($this->width * $this->height >= CONFIG::min_mpixels) return false;
    return true;
  }
  
  # TODO: add_to_base
  function check_pending_count() {
    if (!CONFIG::max_pending_images) return;
    if ($this->status != "pending") return;
    if ($this->user->is('>=33')) return;

    $pending_posts = Post::$_->count(array('conditions' => array("user_id = ? AND status = 'pending'", $this->user_id)));
    if ($pending_posts < CONFIG::max_pending_images) return;

    $this->record_errors->add_to_base("You have too many posts pending moderation");
    return false;
  }
  
  function tempfile_preview_path() {
    return ROOT . "public/data/{$this->md5}-preview.jpg";
  }
  
  function tempfile_sample_path() {
    return ROOT . "public/data/{$this->md5}-sample.jpg";
  }
  
  function tempfile_jpeg_path() {
    return ROOT . "public/data/{$this->md5}-jpeg.jpg";
  }
  
  function generate_sample($force_regen = false) {
    
    if (!$this->is_image()) return true;
    elseif (!CONFIG::image_samples) return true;
    elseif (!$this->width && !$this->height) return true;
    elseif ($this->file_ext == "gif") return true;

    # Always create samples for PNGs.
    $ratio = $this->file_ext == 'png' ? 1 : CONFIG::sample_ratio;

    $size = array('width' => $this->width, 'height' => $this->height);
    if (CONFIG::sample_width)
      $size = Danbooru::reduce_to($size, array('width' => CONFIG::sample_width, 'height' => CONFIG::sample_height), $ratio);
    
    $size = Danbooru::reduce_to($size, array('width' => CONFIG::sample_max, 'height' => CONFIG::sample_min), $ratio, false, true);
    
    # We can generate the sample image during upload or offline.  Use tempfile_path
    # if it exists, otherwise use file_path.
    $path = $this->tempfile_path;
    // $path = file_path unless File.exists?(path)
    if (!file_exists($path)) {
      $this->record_errors->add('file', "not found");
      return false;
    }

    # If we're not reducing the resolution for the sample image, only reencode if the
    # source image is above the reencode threshold.  Anything smaller won't be reduced
    # enough by the reencode to bother, so don't reencode it and save disk space.
    if ($size['width'] == $this->width && $size['height'] == $this->height && filesize($path) < CONFIG::sample_always_generate_size) {
      $this->sample_width = null;
      $this->sample_height = null;
      return true;
    }
    
    # If we already have a sample image, and the parameters havn't changed,
    # don't regenerate it.
    if (!$force_regen && ($size['width'] == $this->sample_width && $size['height'] == $this->sample_height))
      return true;

    try {
      Danbooru::resize($this->file_ext, $path, $this->tempfile_sample_path(), $size, CONFIG::sample_quality);
    } catch (Exception $e) {
      $this->record_errors->add('sample', 'couldn\'t be created: '. $e->getMessage());
      return false;
    }
    
    $this->sample_width = $size['width'];
    $this->sample_height = $size['height'];
    $this->sample_size = filesize($this->tempfile_sample_path());
    
    # TODO: enable crc32 for samples.
    $crc32_accum = 0;
    // File.open(tempfile_sample_path, 'rb') { |fp|
      // buf = ""
      // while fp.read(1024*64, buf) do
        // crc32_accum = Zlib.crc32(buf, crc32_accum)
      // end
    // }
    // self.sample_crc32 = crc32_accum

    return true;
  }
  
  # If the JPEG version needs to be generated (or regenerated), output it to tempfile_jpeg_path.  On
  # error, return false; on success or no-op, return true.
  function generate_jpeg($force_regen = false) {
  
    // return true;
  
    if (!$this->is_image()) return true;
    elseif (!CONFIG::jpeg_enable) return true;
    elseif (!$this->width && !$this->height) return true;
    
    # Only generate JPEGs for PNGs.  Don't do it for files that are already JPEGs; we'll just add
    # artifacts and/or make the file bigger.  Don't do it for GIFs; they're usually animated.
    if ($this->file_ext != "png") return true;

    # We can generate the image during upload or offline.  Use tempfile_path
    # if it exists, otherwise use file_path.
    $path = $this->tempfile_path;
    // path = file_path unless File.exists?(path)
    // unless File.exists?(path)
      // record_errors.add(:file, "not found")
      // return false
    // end
    
    # If we already have the image, don't regenerate it.
    if (!$force_regen && ctype_digit($this->jpeg_width))
      return true;

    $size = Danbooru::reduce_to(array('width' => $this->width, 'height' => $this->height), array('width' => CONFIG::jpeg_width, 'height' => CONFIG::jpeg_height), CONFIG::jpeg_ratio);
    try {
      Danbooru::resize($this->file_ext, $path, $this->tempfile_jpeg_path(), $size, CONFIG::$jpeg_quality);
    } catch (Exception $e) {
      $this->record_errors->add("jpeg", "couldn't be created: {$e->getMessage()}");
      return false;
    }

    $this->jpeg_width = $size['width'];
    $this->jpeg_height = $size['height'];
    $this->jpeg_size = filesize($this->tempfile_jpeg_path());
    
    # TODO: enable crc32 for jpg.
    $crc32_accum = 0;
    // File.open(tempfile_jpeg_path, 'rb') { |fp|
      // buf = ""
      // while fp.read(1024*64, buf) do
        // crc32_accum = Zlib.crc32(buf, crc32_accum)
      // end
    // }
    // self.jpeg_crc32 = crc32_accum

    return true;
  }
  
  function generate_preview() {
    if (!$this->is_image() && !$this->width && !$this->height)
      return true;

    $size = Danbooru::reduce_to(array('width' => $this->width, 'height' => $this->height), array('width' => 300, 'height' => 300));

    # Generate the preview from the new sample if we have one to save CPU, otherwise from the image.
    if (file_exists($this->tempfile_sample_path()))
      list($path, $ext) = array($this->tempfile_sample_path(), "jpg");
    elseif (file_exists($this->sample_path()))
      list($path, $ext) = array($this->sample_path(), "jpg");
    elseif (file_exists($this->tempfile_path))
      list($path, $ext) = array($this->tempfile_path, $this->file_ext);
    elseif (file_exists($this->file_path()))
      list($path, $ext) = array($this->file_path(), $this->file_ext);
    else {
      return false;
    }
    
    try {
      Danbooru::resize($ext, $path, $this->tempfile_preview_path(), $size, 85);
    } catch (Exception $e) {
      $this->record_errors->add("preview", "couldn't be generated ({$e->getMessage()})");
      return false;
    }
    
    $this->actual_preview_width = $this->raw_preview_dimensions('w');
    $this->actual_preview_height = $this->raw_preview_dimensions('h');
    $this->preview_width = $this->preview_dimensions('w');
    $this->preview_height = $this->preview_dimensions('h');

    return true;
  }
  
  function file_name() {
    return $this->md5 . "." . $this->file_ext;
  }
  
  function get_file_image($user = null) {
    return array(
      'url' => $this->file_url,
      'ext' => $this->file_ext,
      'size' => $this->file_size,
      'width' => $this->width,
      'height' => $this->height
    );
  }
  
  function get_file_jpeg($user = null) {
    if ($this->status == "deleted" or !$this->use_jpeg($user))
      return $this->get_file_image($user);

    return array(
      'url' => $this->jpeg_url(),
      'size' => $this->jpeg_size,
      'ext' => "jpg",
      'width' => $this->jpeg_width,
      'height' => $this->jpeg_height
    );
  }
  
  function get_file_sample($user = null) {
    if ($this->status == "deleted" or !$this->use_sample($user))
      return $this->get_file_jpeg($user);
    
    return array(
      'url' => $this->sample_url(),
      'size' => $this->sample_size,
      'ext' => "jpg",
      'width' => $this->sample_width,
      'height' => $this->sample_height
    );
  }
  
  // # Custom url_encode.
  // function u($str) {
    // $str = translate_chars($str);
    // foreach (
  // }
  
  /** }
   * Local Hierarchy methods {
   */
  
  # Preview {
  function preview_path() {
    return ROOT . "public/data/preview/".$this->file_hierarchy()."/{$this->md5}.jpg";
  }
  
  function preview_url() {
    if ($this->status == "deleted")
      return "/deleted-preview.png";
    elseif ($this->is_image())
      return CONFIG::url_base . "/data/preview/".$this->file_hierarchy()."/{$this->md5}.jpg";
    else
      return CONFIG::url_base . "/download-preview.png";
  }
  
  # } Sample {
  function has_sample() {
    return !empty($this->sample_size);
  }

  function sample_path() {
    return ROOT . "public/data/sample/".$this->file_hierarchy()."/" . CONFIG::sample_filename_prefix . $this->md5 . ".jpg";
  }
  
  function fake_sample_url() {
    if (CONFIG::use_pretty_image_urls) {
      $path = "/data/image/".$this->md5."/".$this->pretty_file_name(array('type' => 'sample')).'.'.$this->file_ext;
    } else
      $path = "/data/image/" . CONFIG::sample_filename_prefix . $this->md5 . '.' . $this->file_ext;
    
    return CONFIG::url_base . $path;
  }
  
  function sample_url() {
    if ($this->fake_samples_for_browse()) {
      return $this->fake_sample_url();
    }
  
    if (!$this->has_sample())
      return $this->jpeg_url();
    
    if (CONFIG::use_pretty_image_urls)
      $path = "/sample/{$this->md5}/".$this->pretty_file_name(array('type' => 'sample')).'.jpg';
    else
      $path = "/data/sample/" . CONFIG::sample_filename_prefix . $this->md5.'.jpg';

    return CONFIG::url_base . $path;
  }
  
  # } Jpeg {
  function has_jpeg() {
    if($this->jpeg_size)
      return true;
  }
  
  function use_jpeg(&$user = null) {
    return CONFIG::jpeg_enable && $this->has_jpeg();
  }
  
  function file_name_jpg() {
    return "{$this->md5}.jpg";
  }
  
  function jpeg_path() {
    return ROOT . "public/data/jpeg/{$this->file_hierarchy()}/".$this->md5.".jpg";
  }

  function jpeg_url() {
    if (!$this->has_jpeg())
      return $this->file_url;
    
    if (CONFIG::use_pretty_image_urls)
      $path = "/jpeg/{$this->md5}/".$this->pretty_file_name(array('type' => 'jpeg')).'.jpg';
    else
      $path = "/data/jpeg/{$this->md5}.jpg";
    
    return CONFIG::url_base . $path;
  }
  
  # } File 
  
  function file_hierarchy() {
    return substr($this->md5, 0, 2).'/'.substr($this->md5, 2, 2);
  }

  function file_path() {
    return ROOT."public/data/image/".$this->file_hierarchy()."/".$this->file_name();
  }

  function file_url() {
    if (CONFIG::use_pretty_image_urls)
      $path = "/data/image/".$this->md5."/".$this->pretty_file_name().".{$this->file_ext}";
    else
      $path = "/data/image/".$this->file_hierarchy()."/".$this->file_name();
    return CONFIG::url_base . $path;
  }


  function delete_file() {
    unlink($this->file_path());
    if ($this->is_image()) {
      if (file_exists($this->preview_path()))
        unlink($this->preview_path());
      if (file_exists($this->sample_path()))
        unlink($this->sample_path());
      if (file_exists($this->jpeg_path()))
        unlink($this->jpeg_path());
    }
  }

  function move_file() {
    $this->create_dirs($this->file_path());
    
    if ($this->is_upload)
      move_uploaded_file($this->tempfile_path, $this->file_path());
    else
      rename($this->tempfile_path, $this->file_path());
    
    chmod($this->file_path(), 0777);

    if ($this->is_image()) {
      $this->create_dirs($this->preview_path());
      rename($this->tempfile_preview_path(), $this->preview_path());
      chmod($this->preview_path(), 0777);
    }

    if (file_exists($this->tempfile_sample_path())) {
      $this->create_dirs($this->sample_path());
      rename($this->tempfile_sample_path(), $this->sample_path());
      chmod($this->sample_path(), 0777);
    }

    if (file_exists($this->tempfile_jpeg_path())) {
      $this->create_dirs($this->jpeg_path());
      rename($this->tempfile_jpeg_path(), $this->jpeg_path());
      chmod($this->jpeg_path(), 0777);
    }
  }
  
  private function create_dirs($dir) {
    @mkdir(pathinfo($dir, PATHINFO_DIRNAME), 0777, true);
  }
    
  /** }
   * external_post.rb and others? {
   */ 
  
  /** }
   * Rating methods? {
   */
  
  function pretty_rating(){
    if ($this->rating == 'e')
      return 'Explicit';
    elseif ($this->rating == 'q')
      return 'Questionable';
    elseif ($this->rating == 's')
      return 'Safe';
  }
  
  /**
   * Tag methods?
   */
  function has_tag($tag) {
    if(array_key_exists($tag, $this->cached_tags))
      return true;
  }
  
  function parse_cached_tags($tags = null) {
    !$tags && $tags = $this->cached_tags;
    
    $tags = explode(' ', $tags);
    sort($tags);
    
    foreach($tags as $tag) {
      $tag_type = explode(':', $tag);
      if (!isset($tag_type[1]) || $tag_type[1] === '') {
        continue;
      }
      
      $tag = $tag_type[0];
      
      $type = Tag::$_->type_name($tag_type[1]);
      $parsed[$tag] = $type;
    }
    
    if (empty($parsed))
      $parsed['tagme'] = 0;
    
    return $parsed;
  }
  
  function generate_cached_tags() {
    $string = array();
    foreach ($this->parsed_cached_tags as $name => $type_name)
      $string[] = "$name:".Tag::$_->type_code($type_name);
    
    $this->cached_tags = implode(' ', $string);
  }
  
  function tag_names() {
    foreach(array_keys($this->parsed_cached_tags) as $tag)
      $names[] = $tag;
    return implode(' ', $names);
  }
  
  function tag_title() {
    return substr(preg_replace('/\W+/', '-', $this->tags), 0, 50);
  }
  
  function title_tags() {
    return $this->tags;
  }
  
  function commit_tags() {
    if ($this->is_empty_model())
      return;
    
    $this->tags = array_filter(explode(' ', str_replace(array("\r", "\n"), '', $this->tags)));
    $this->current_tags = array_keys($this->parsed_cached_tags);
    
    if (empty($this->old_tags))
      $this->old_tags = $this->tags;
    elseif (!is_array($this->old_tags))
      $this->old_tags = array_filter(explode(' ', $this->old_tags));
    
    $this->commit_metatags();
    
    foreach ($this->tags as $k => $tag) {
      if (!preg_match('~^(-pool|pool|rating|parent|child):|^[qse]$~', $tag))
        continue;
      
      // if (strcompare($tag, array('q', 's', 'e')))
        // $tag = 'rating:'.$m[0];
      if (in_array($tag, array('q', 's', 'e')))
        $tag = "rating:$tag";
      
      $subparam = explode(':', $tag);
      $metatag = array_shift($subparam);
      $param = array_shift($subparam);
      $subparam = empty($subparam) ? null : array_shift($subparam);
      
      switch($metatag) {
        case 'rating':
          # Change rating. This will override rating selected on radio buttons.
          // if (strcompare($param, array('q', 's', 'e')))
          if (in_array($param, array('q', 's', 'e')))
            $this->rating = $param;
          unset($this->tags[$k]);
        break;
          
        case 'pool':
          try {
            $name = $param;
            $seq = $subparam;
            
            # Workaround: I don't understand how can the pool be found when finding_by_name
            # using the id.
            if (ctype_digit($name))
              $pool = Pool::$_->find_by_id($name);
            else
              $pool = Pool::$_->find_by_name($name);

            # Set :ignore_already_exists, so pool:1:2 can be used to change the sequence number
            # of a post that already exists in the pool.
            $options = array('user' => User::$_->find(($this->updater_user_id)), 'ignore_already_exists' => true);
            if ($seq)
              $options['sequence'] = $seq;
            
            if (!$pool and !ctype_digit($name))
              $pool = Pool::$_->create(array('name' => $name, 'is_public' => false, 'user_id' => $this->updater_user_id));
            
            if (!$pool)
              continue;
            
            if (!$pool->can_change(User::$current, null))
              continue;
            
            $pool->add_post($this->id, $options);
            
          } catch(PostAlreadyExistsError $e) {
          } catch (AccessDeniedError $e) {
          }
          unset($this->tags[$k]);
        break;
          
        case '-pool':
          unset($this->tags[$k]);
          
          $name = $param;
          $cmd = $subparam;

          $pool = Pool::$_->find_by_name($name);
          if (!$pool->can_change(User::$current, null))
            break;

          if ($cmd == "parent") {
            # If we have a parent, remove ourself from the pool and add our parent in
            # our place.  If we have no parent, do nothing and leave us in the pool.
            if (!empty($this->parent_id)) {
              $pool->transfer_post_to_parent($this->id, $this->parent_id);
              break;
            }
          }
          $pool && $pool->remove_post($id);
        break;
          
        case 'source':
          $this->source = $param;
          unset($this->tags[$k]);
        break;
          
        case 'parent':
          
          if (is_numeric($param)) {
            $this->parent_id = (int)$param;;
          }
          unset($this->tags[$k]);
        break;
        
        case 'child':
          unset($this->tags[$k]);
        break;
          
        default:
         unset($this->tags[$k]);
        break;
      }
    }
    
    $new_tags = array_diff($this->tags, $this->old_tags);
    $new_tags = array_merge($new_tags, TagAlias::$_->to_aliased($new_tags));
    $new_tags = array_merge($new_tags, TagImplication::$_->with_implied($new_tags));
    $new_tags = array_values(array_unique($new_tags));
    
    $old_tags = array_diff($this->old_tags, $this->tags);
    
    if (empty($new_tags) && $old_tags == $this->current_tags) {
      if (!in_array('tagme', $new_tags))
        $new_tags[] = 'tagme';
      if (in_array('tagme', $old_tags)) {
        unset($old_tags[array_search('tagme', $old_tags)]);
      }
    }
    
    foreach ($old_tags as $tag) {
      if (array_key_exists($tag, $this->parsed_cached_tags))
        unset($this->parsed_cached_tags[$tag]);
      
      $tag = Tag::$_->find_by_name($tag);
      if ($tag)
        DB::delete('posts_tags WHERE post_id = ? AND tag_id = ?', $this->id, $tag->id);
    }
    
    foreach ($new_tags as $tag) {
      $tag = Tag::$_->find_or_create_by_name($tag);
      $this->parsed_cached_tags[$tag->name] = $tag->type_name;
      DB::insert_ignore('posts_tags VALUES (?, ?)', $this->id, $tag->id);
    }
    
    $this->tags = $this->tag_names();
    
    $this->generate_cached_tags();
  }
  
  # Commit metatags; this is done before save, so any changes are stored normally.
  function commit_metatags() {
    // if (!is_array($this->tags))
      // return;
    
    foreach ($this->tags as $k => $tag) {
      switch ($tag) {
        case 'hold':
          $this->hold();
          unset($this->tags[$k]);
        break;
        
        case 'unhold':
          $this->unhold();
          unset($this->tags[$k]);
        break;
        
        case 'show':
          $this->is_shown_in_index = true;
          unset($this->tags[$k]);
        break;
        
        case 'hide':
          $this->is_shown_in_index = false;
          unset($this->tags[$k]);
        break;
        
        // case '+flag':
          // $this->metatag_flagged = "moderator flagged";
        // break;
        
        // case 'e':
        // case 'q':
        // case 's':
          // $this->rating = $tag;
          // unset($this->tags[$k]);
        // break;
      }
    }
  }
  
  /**
   * Parent methods {
   */
  
  # $side_updates_only will save a query and let the parent be updated via save()
  # Option created for commit_tags()
  function set_parent($post_id, $parent_id, $old_parent_id = null, $side_updates_only = false) {
    if (!CONFIG::enable_parent_posts)
      return;
    
    # TODO: accessing DB directly twice. not good?
    if ($old_parent_id === null)
      $old_parent_id = DB::select_value("parent_id FROM posts WHERE id = ?", $post_id);
    
    if ($parent_id == $post_id || $parent_id == 0) {
      $parent_id = null;
    }
    
    if (!$side_updates_only)
      DB::update("posts SET parent_id = ? WHERE id = ?", $parent_id, $post_id);
    
    $this->update_has_children($old_parent_id);
    $this->update_has_children($parent_id);
  }
  
  function update_has_children($post_id) {
    if (!$post_id)
      return;
    
    $has_children = Post::$_->exists('parent_id = ? AND status <> "deleted"', $post_id);
    
    DB::update("posts SET has_children = ? WHERE id = ?", $has_children, $post_id);
  }
  
  function get_parent() {
    if (isset($this->parent))
      return $this->parent;
    
    $this->parent = Post::$_->find_by_id($this->parent_id);
    return $this->parent;
  }
  
  function update_parent() {
    if (!$this->parent_id_changed() && !$this->status_changed())
      return;
    
    $this->set_parent($this->id, $this->parent_id, $this->parent_id_was());
  }
  
  function give_favorites_to_parent() {
    if (!$this->parent_id)
      return;
    
    $parent = new Post('find', $this->parent_id);
    
    foreach (PostVotes::$_->collection('find', array('conditions' => array('post_id = ?', $this->id))) as $vote) {
      $parent->vote($vote->score, $vote->user);
      $this->vote(0, $vote->user);
    }
  }
  
  /** }
   * Status methods {
   */
  function approve($approver_id) {
    $old_status = $this->status;

    if ($this->flag_detail)
      $this->flag_detail->update_attribute('is_resolved', true);
    
    $this->update_attributes(array('status' => "active", 'approver_id' => $approver_id));

    # Don't bump posts if the status wasn't "pending"; it might be "flagged".
    if ($old_status == "pending" and CONFIG::hide_pending_posts) {
      // $this->touch_index_timestamp();
      $this->save();
    }
  }
  
  function on_is_held_change($value) {
    if (is_string($value) && $value == 'false')
      $value = false;
    
    if ($value)
      $this->hold();
    else
      $this->unhold();
  }
  
  function hold() {
    # Only the original poster can hold or unhold a post.
    if (!User::$current->has_permission($this)) {
      return;
    }
    
    # A post can only be held within one minute of posting (except by a moderator);
    # this is intended to be used on initial posting, before it shows up in the index.
    $created_at = new DateTime($this->created_at);
    $limit = new DateTime(gmd());
    $limit->sub(new DateInterval('PT1M'));
    
    if ($limit > $created_at)
      return;
    
    $this->is_held = true;
  }
  
  function unhold() {
    # Only the original poster can hold or unhold a post.
    if (!User::$current->has_permission($this))
      return;
    
    # When a post is unheld, bump it.
    $this->is_held = false;
    $this->touch_index_timestamp();
  }
  
  function batch_activate($user_id, $post_ids) {
    $conds = $cond_params = array();

    $conds[] = "is_held = true";
    $conds[] = "id IN (??)";

    if ($user_id) {
      $conds[] = "user_id = ?";
      $cond_params[] = $user_id;
    }

    # Tricky: we want posts to show up in the index in the same order they were posted.
    # If we just bump the posts, the index_timestamps will all be the same, and they'll
    # show up in an undefined order.  We don't want to do this in the ORDER BY when
    # searching, because that's too expensive.  Instead, tweak the timestamps slightly:
    # for each post updated, set the index_timestamps 1ms newer than the previous.
    #
    # Returns the number of posts actually activated.
    $count = 0;
    
    # Original function is kinda confusing...
    # If anyone knows a better way to do this, it'll be more than welcome.
    sort($post_ids);
    $s = 1;
    $timestamp = new DateTime(self::$_->find_index_timestamp(array('order' => 'id DESC')));
    
    foreach ($post_ids as $id) {
      $timestamp->add(new DateInterval('PT' . $s . 'S'));
      
      if (DB::update('posts SET index_timestamp = ?, is_held = 0 WHERE id = ? AND is_held', $timestamp->format('Y-m-d H:i:s'), $id)) {
        $count++;
        $s++;
      }
    }

    // Cache.expire if count > 0

    return $count;
  }
  
  function reset_index_timestamp() {
    $this->index_timestamp = $this->created_at;
  }

  # Bump the post to the front of the index.
  function touch_index_timestamp() {
    $this->index_timestamp = gmd();
  }
  
  /** }
   * Sql methods? {
   */ 
  
  // # Customizing find() because of the "cached_tags" column.
  // # Problem was that if a tag is updated, the changes wouldn't be reflected on
  // # all posts tagged with such tag, because their cached_tags column wasn't updated
  // # alongside the tag.
  // function find($select, $params = array()) {
    // $find_params = $this->parse_find_params($select, $params);
    
    // $this->default_sql_params($params);
    
    // $sql = $this->create_sql($params);
    
    // $data = $this->execute_find_sql($sql);
    
    // $result = $this->retrieve_find_result($data, $find_params);
    // return $result;
  // }
  
  // private function default_sql_params(&$params) {
    // if ((isset($params['model_name']) && $params['model_name'] != get_class($this)) || (isset($params['from']) && $params['from'] != $this->t()))
      // return;
    
    // $default_joins = 'JOIN users u ON posts.user_id = u.id JOIN posts_tags pt ON posts.id = pt.post_id JOIN tags t ON pt.tag_id = t.id';
    // $default_select = "u.name AS author, GROUP_CONCAT(CONCAT(t.name,':',t.tag_type) SEPARATOR ' ') AS cached_tags";
  
    // if (isset($params['joins']))
      // $params['joins'] .= ' ' . $default_joins;
    // else
      // $params['joins'] = $default_joins;
    
    // if (isset($params['select']))
      // $params['select'] .= ', ' . $default_select;
    // else
      // $params['select'] = 'posts.*, ' . $default_select;
  // }
  
  function generate_sql_range_helper($arr, $field, &$c, &$p) {
    switch ($arr[0]) {
      case 'eq':
        $c[] = $field." = ?";
        $p[] = $arr[1];
        break;

      case 'gt':
        $c[] = $field." > ?";
        $p[] = $arr[1];
        break;
    
      case 'gte':
        $c[] = $field." >= ?";
        $p[] = $arr[1];
        break;

      case 'lt':
        $c[] = $field." < ?";
        $p[] = $arr[1];
        break;

      case 'lte':
        $c[] = $field." <= ?";
        $p[] = $arr[1];
        break;

      case 'between':
        $c[] = $field." BETWEEN ? AND ?";
        $p[] = $arr[1];
        $p[] = $arr[2];
        break;

      case 'in':
        $c[] = $field." IN (".implode(', ', array_fill(0, count($arr[1]), '?')).")";
        $p[] = current($arr[1]);
        break;
    }
  }
  
  function generate_sql_escape_helper($array) {
    return $array;
    // foreach ($array as $token) {
      // if (!$token) continue;
      // $escaped_token = $token.gsub(/\\|'/, '\0\0\0\0').gsub("?", "\\\\77")
      // "''" + escaped_token + "''"
    // }
  }
  
  function generate_sql($q, $options = array()) {
    if (is_array($q)) {
      $original_query = isset($options['original_query']) ? $options['original_query'] : null;
    } else {
      $original_query = $q;
      $q = Tag::$_->parse_query($q);
    }
    
    # Filling default values.
    $q = array_merge(array_fill_keys(array('md5', 'ext', 'source', 'fav', 'user', 'rating', 'rating_negated', 'unlocked_rating', 'show_holds', 'shown_in_index', 'exclude', 'related', 'post_id', 'mpixels', 'width', 'height', 'score', 'date', 'change'), null), $q);
    $options = array_merge(array_fill_keys(array('pending', 'flagged', 'from_api', 'limit', 'offset', 'count', 'select', 'having'), null), $options);
    
    $conds = array('true');
    
    // if (!empty($options['select'])) {
      // $options['select'] .= ', ';
    // }
    
    // $options['select'] .= "";
    
    # Join `users` by default to take author's name.
    # Join posts_tags and tags to take tags.
    $joins = array(
      'posts p',
      'JOIN users u ON p.user_id = u.id',
      'JOIN posts_tags pt ON p.id = pt.post_id',
      'JOIN tags t ON pt.tag_id = t.id'
    );
    $join_params = array();
    $cond_params = array();
    
    if (!empty($q['error']))
      $conds[] = "FALSE";

    $this->generate_sql_range_helper($q['post_id'], "p.id", $conds, $cond_params);
    $this->generate_sql_range_helper($q['mpixels'], "p.width*p.height/1000000.0", $conds, $cond_params);
    $this->generate_sql_range_helper($q['width'],   "p.width", $conds, $cond_params);
    $this->generate_sql_range_helper($q['height'],  "p.height", $conds, $cond_params);
    $this->generate_sql_range_helper($q['score'],   "p.score", $conds, $cond_params);
    $this->generate_sql_range_helper($q['date'],    "DATE(p.created_at)", $conds, $cond_params);
    $this->generate_sql_range_helper($q['change'],  "p.change_seq", $conds, $cond_params);

    if (is_string($q['md5'])) {
      $conds[] = "p.md5 IN (?)";
      $cond_params[] = explode(',', $q['md5']);
    }
  
    if (is_string($q['ext'])) {
      $conds[] = "p.file_ext IN (?)";
      $cond_params[] = explode(',', strtolower($q['ext']));
    }
  
    if (isset($q['show_deleted_only'])) {
      if ($q['show_deleted_only'])
        $conds[] = "p.status = 'deleted'";
    } elseif (empty($q['post_id'])) {
      # If a specific post_id isn't specified, default to filtering deleted posts.
      $conds[] = "p.status <> 'deleted'";
    }

    if (isset($q['parent_id']) && is_numeric($q['parent_id'])) {
      $conds[] = "(p.parent_id = ? or p.id = ?)";
      $cond_params[] = $q['parent_id'];
      $cond_params[] = $q['parent_id'];
    } elseif (isset($q['parent_id']) && $q['parent_id'] == false) {
      $conds[] = "p.parent_id is null";
    }

    if (is_string($q['source'])) {
      $conds[] = "lower(p.source) LIKE lower(?) ESCAPE E'\\\\'";
      $cond_params[] = $q['source'];
    }

    // if (is_string($q['subscriptions'])) {
      // preg_match('/^(.+?):(.+)$/', $q['subscriptions'], $m);
      // $username = $m[1] || $q['subscriptions'];
      // $subscription_name = $m[2];
      // $user = new User('find_by_name', $username);

      // if ($user) {
        // $post_ids = TagSubscription.find_post_ids(user.id, subscription_name)
        // $conds[] = "p.id IN (?)"
        // $cond_params[] = post_ids
      // }
    // }

    if (is_string($q['fav'])) {
      $joins[] = "JOIN favorites f ON f.post_id = p.id JOIN users fu ON f.user_id = fu.id";
      $conds[] = "lower(fu.name) = lower(?)";
      $cond_params[] = $q['fav'];
    }

    if (isset($q['vote_negated'])) {
      $joins[] = "LEFT JOIN post_votes v ON p.id = v.post_id AND v.user_id = ?";
      $join_params[] = $q['vote_negated'];
      $conds[] = "v.score IS NULL";
    }
    
    # TODO: binding parameters (v.user_id = ?)
    if (isset($q['vote'])) {
      $joins[] = "JOIN post_votes v ON p.id = v.post_id";
      // $conds[] = sprintf("v.user_id = %d", $q['vote'][1]);
      $conds[] = 'v.user_id = ?';
      $cond_params[] = $q['vote'][1];

      $this->generate_sql_range_helper($q['vote'][0], "v.score", $conds, $cond_params);
    }

    if (is_string($q['user'])) {
      $conds[] = "lower(u.name) = lower(?)";
      $cond_params[] = $q['user'];
    }

    if (isset($q['exclude_pools'])) {
      foreach (array_keys($q['exclude_pools']) as $i) {
        if (is_int($q['exclude_pools'][$i])) {
          $joins[] = "LEFT JOIN pools_posts ep${i} ON (ep${i}.post_id = p.id AND ep${i}.pool_id = ?)";
          $join_params[] = $q['exclude_pools'][$i];
          $conds[] = "ep${i}.id IS NULL";
        }

        if (is_string($q['exclude_pools'][$i])) {
          $joins[] = "LEFT JOIN pools_posts ep${i} ON ep${i}.post_id = p.id LEFT JOIN pools epp${i} ON (ep${i}.pool_id = epp${i}.id AND LOWER(epp${i}.name) LIKE ?)";
          $join_params[] = "%".strtolower($q['exclude_pools'][$i])."%";
          $conds[] = "ep${i}.id IS NULL";
        }
      }
    }
    
    if (isset($q['pool'])) {
      $conds[] = "p.status = 'active'";

      if (!isset($q['order']))
        $pool_ordering = " ORDER BY pools_posts.pool_id ASC, CAST(pools_posts.sequence AS UNSIGNED), pools_posts.post_id";

      if (is_int($q['pool'])) {
        $joins[] = "JOIN pools_posts ON pools_posts.post_id = p.id JOIN pools ON pools_posts.pool_id = pools.id";
        $conds[] = "pools.id = ".$q['pool'];
      }

      if (is_string($q['pool'])) {
        if ($q['pool'] == "*")
          $joins[] = "JOIN pools_posts ON pools_posts.post_id = p.id JOIN pools ON pools_posts.pool_id = pools.id";
        else {
          $joins[] = "JOIN pools_posts ON pools_posts.post_id = p.id JOIN pools ON pools_posts.pool_id = pools.id";
          $conds[] = "LOWER(pools.name) LIKE ?";
          $cond_params[] = ("%".strtolower($q['pool'])."%");
        }
      }
    }
    
    # http://stackoverflow.com/questions/8106547/how-to-search-on-mysql-using-joins/8107017
    $tags_index_query = array();

    if (!empty($q['include']))
      $tags_index_query[] = implode(' | ', $this->generate_sql_escape_helper($q['include']));
    
    if (!empty($q['related'])) {
      if (count($q['exclude']) > CONFIG::tag_query_limit) {
        "You cannot search for more than ".CONFIG::tag_query_limit." tags at a time";
      }
      
      // # TODO: bound parameters.
      // $tags_index_query[] = '('.implode(', ', array_map(function($v, $k){return 't'.($k+1).'.name';}, $q['related'], array_keys($q['related']))).') = ('.implode(', ', array_map(function($v){return "'".addslashes($v)."'";}, $q['related'])).')';
      $tags_index_query[] = '('.implode(', ', array_map(function($v, $k){return 't'.($k+1).'.name';}, $q['related'], array_keys($q['related']))).') = ('.implode(', ', array_fill(0, count($q['related']), '?')).')';
      
      $cond_params = array_merge($cond_params, $q['related']);
      
      $joins[] = implode(' ', array_map(function($k){return 'INNER JOIN posts_tags pt'.($k+1).' ON p.id = pt'.($k+1).'.post_id INNER JOIN tags t'.($k+1).' ON pt'.($k+1).'.tag_id = t'.($k+1).'.id';}, array_keys($q['related'])));
    }

    if (!empty($q['exclude'])) {
      if (count($q['exclude']) > CONFIG::tag_query_limit) {
        "You cannot search for more than ".CONFIG::tag_query_limit." tags at a time";
      }
      
      $tags_index_query[] = 'NOT EXISTS
      (SELECT *
        FROM posts_tags pt
          INNER JOIN tags t ON pt.tag_id = t.id
        WHERE p.id = pt.post_id 
          AND t.name IN ('.implode(', ', array_map(function($v){return '"'.addslashes($v).'"';}, $q['exclude'])).')
      )';
    }
    
    if (!empty($tags_index_query)) {
      $conds[] = implode(' AND ', $tags_index_query);
    }

    if (is_string($q['rating'])) {
      $r = strtolower(substr($q['rating'], 0, 1));
      if ($r == "s")
        $conds[] = "p.rating = 's'";
      elseif ($r == "q")
        $conds[] = "p.rating = 'q'";
      elseif ($r == "e")
        $conds[] = "p.rating = 'e'";
    }

    if (is_string($q['rating_negated'])) {
      $r = strtolower(substr($q['rating_negated'], 0, 1));
      if ($r == "s")
        $conds[] = "p.rating <> 's'";
      elseif ($r == "q")
        $conds[] = "p.rating <> 'q'";
      elseif ($r == "e")
        $conds[] = "p.rating <> 'e'";
    }

    if ($q['unlocked_rating'] == true)
      $conds[] = "p.is_rating_locked = FALSE";

    if (isset($options['pending']))
      $conds[] = "p.status = 'pending'";
  
    if (isset($options['flagged']))
      $conds[] = "p.status = 'flagged'";

    if (isset($q['show_holds'])) {
      if( $q['show_holds'] == 'only')
        $conds[] = "p.is_held";
      elseif ($q['show_holds'] == 'hide')
        $conds[] = "NOT p.is_held";
      elseif ($q['show_holds'] == 'yes') {/*do nothing?*/}
      
    } else {
      # Hide held posts by default only when not using the API.
      if (!$options['from_api'])
        $conds[] = "NOT p.is_held";
    }

    if (isset($q['show_pending'])) {
      if ($q['show_pending'] == 'only')
        $conds[] = "p.status = 'pending'";
      elseif ($q['show_pending'] == 'hide')
        $conds[] = "p.status <> 'pending'";
      elseif ($q['show_pending'] == 'yes') {/*do nothing?*/}
    } else {
      # Hide pending posts by default only when not using the API.
      if (CONFIG::hide_pending_posts && !isset($options['from_api']))
        $conds[] = "p.status <> 'pending'";
    }

    if (isset($q['shown_in_index'])) {
      if ($q['shown_in_index'])
        $conds[] = "p.is_shown_in_index";
      else
        $conds[] = "NOT p.is_shown_in_index";
    } elseif (!$original_query && !$options['from_api']) {
      # Hide not shown posts by default only when not using the API.
      $conds[] = "p.is_shown_in_index";
    }
    
    // # We'll need the author's name always. Let's take it from the query
    // # instead of creating a new User model just for the name!
    // # We need tags too.
    $sql = "SELECT SQL_CALC_FOUND_ROWS u.name AS author, GROUP_CONCAT(CONCAT(t.name, ':', t.tag_type) SEPARATOR ' ') AS cached_tags ";
    // $sql = "SELECT SQL_CALC_FOUND_ROWS u.name AS author";
    
    
    if ($options['count'])
      $sql .= "COUNT(*)";
    elseif ($options['select'])
      $sql .= ', '.$options['select'];
    else
      $sql .= ", p.*";
      // $sql .= ", p.*, GROUP_CONCAT(CONCAT(t.name, ':', t.tag_type) SEPARATOR ' ') AS cached_tags";

    $sql .= " FROM ".implode(' ', $joins);
    $sql .= " WHERE ".implode(' AND ', $conds);
    
    $sql .= ' GROUP BY p.id ';
    
    if (isset($q['order']) && !$options['count']) {
      if ($q['order'] == "id")
        $sql .= " ORDER BY p.id";
      
      elseif ($q['order'] == "id_desc")
        $sql .= " ORDER BY p.id DESC";
      
      elseif ($q['order'] == "score")
        $sql .= " ORDER BY p.score DESC";
      
      elseif ($q['order'] == "score_asc")
        $sql .= " ORDER BY p.score";
      
      elseif ($q['order'] == "mpixels")
        # Use "w*h/1000000", even though "w*h" would give the same result, so this can use
        # the posts_mpixels index.
        $sql .= " ORDER BY width*height/1000000.0 DESC";

      elseif ($q['order'] == "mpixels_asc")
        $sql .= " ORDER BY width*height/1000000.0";

      elseif ($q['order'] == "portrait")
        $sql .= " ORDER BY 1.0*width/GREATEST(1, height)";

      elseif ($q['order'] == "landscape")
        $sql .= " ORDER BY 1.0*width/GREATEST(1, height) DESC";

      elseif ($q['order'] == "portrait_pool") {
        # We can only do this if we're searching for a pool.
        if (isset($q['pool']))
          $sql .= " ORDER BY 1.0*width / GREATEST(1, height), CAST(pools_posts.sequence AS UNSIGNED), pools_posts.post_id";

      } elseif ($q['order'] == "change" || $q['order'] == "change_asc")
        $sql .= " ORDER BY change_seq";

      elseif ($q['order'] == "change_desc")
        $sql .= " ORDER BY change_seq DESC";

      elseif ($q['order'] == "vote") {
        if (isset($q['vote']))
          $sql .= " ORDER BY v.updated_at DESC";

      } elseif ($q['order'] == "fav") {
        if (is_string($q['fav']))
          $sql .= " ORDER BY f.id DESC";

      } elseif ($q['order'] == "random")
        $sql .= " ORDER BY random";

      else
        $use_default_order = true;
    } else {
      $use_default_order = true;
    }

    if (isset($use_default_order) && !$options['count']) {
      if (isset($pool_ordering))
        $sql .= $pool_ordering;
      else {
        if (!empty($options['from_api']))
          # When using the API, default to sorting by ID.
          $sql .= " ORDER BY p.id DESC";
        else
          $sql .= " ORDER BY p.index_timestamp DESC";
      }
    }
    
    if (!empty($options['limit']))
      $sql .= " LIMIT ".(string)$options['limit'];

    if (!empty($options['offset']))
      $sql .= " OFFSET ".(string)$options['offset'];
    
    $params = array_merge($join_params,$cond_params);
    $sql = $this->sanitize_sql($sql, $params);
    
    return $sql;
  }
  
  /** }
   * Api methods? {
   */
  function batch_api_data($posts, $options = array()) {
    foreach ($posts as $post)
      $result['posts'][] = $post->api_attributes();
    
    if (empty($options['exclude_pools'])) {
      $pool_posts = Pool::$_->get_pool_posts_from_posts($posts);
      // $pools = ;
      $result['pools'] = obj2array(Pool::$_->get_pools_from_pool_posts($pool_posts));
      
      foreach ($pool_posts as $pp) {
        $result['pool_posts'][] = $pp->api_attributes();
      }
    }
    
    if (empty($options['exclude_tags']))
      $result['tags'] = Tag::$_->batch_get_tag_types_for_posts($posts);
    
    if (!empty($options['user']))
      $user = $options['user'];
    else
      $user = User::$current;

    # Allow loading votes along with the posts.
    #
    # The post data is cachable and vote data isn't, so keep this data separate from the
    # main post data to make it easier to cache API output later.
    if (empty($options['exclude_votes'])) {
      $vote_map = array();
      if (!empty($posts)) {
        foreach ($posts as $p) {
          $post_ids[] = $p->id;
        }
        $post_ids = implode(',', $post_ids);
        $sql = sprintf("SELECT v.* FROM post_votes v WHERE v.user_id = %d AND v.post_id IN (%s)", $user->id, $post_ids);
        
        $votes = PostVotes::$_->collection('find_by_sql', $sql);
        // $votes = new Collection('PostVotes', 'find_by_sql', $sql);
        foreach ($votes as $v) {
          $vote_map[$v->post_id] = $v->score;
        }
      }
      $result['votes'] = $vote_map;
    }
    
    return $result;
  }
  
  // function collection_api_attributes($posts) {
    // return array_map(function($p){return $p->api_attributes;}, (array)$posts);
  // }
  
  function api_data() {
    return array(
      'post' => $this,
      'tags' => Tag::$_->batch_get_tag_types_for_posts(array($this))
    );
  }
  
  function filter_api_changes(&$params) {
    // if (is_array($params)) {
    unset($params['frames']);
    unset($params['frames_warehoused']);
    // } elseif (is_object($params)) {
      // unset($params->frames);
      // unset($params->frames_warehoused);
    // }
  }
  
  /** }
   * Vote methods {
   */ 
  
  function recalculate_score() {
    $this->save();
    DB::update($this->t().' SET score = (SELECT COUNT(*) FROM post_votes WHERE post_id = :post_id AND score > 0) WHERE id = :post_id', array('post_id' => $this->id));
   
    $this->reload();
  }
  
  function vote($score, &$user, $options = array()) {
    $score < CONFIG::vote_record_min && $score = CONFIG::vote_record_min;
    $score > CONFIG::vote_record_max && $score = CONFIG::vote_record_max;
    
    if ($user->is_anonymous)
      return false;
    
    $vote = PostVotes::$_->find_by_user_id_and_post_id(array($user->id, $this->id));
    
    if (!$vote) {
      $vote = PostVotes::$_->create(array('post_id' => $this->id, 'user_id' => $user->id, 'score' => $score));
    }
    
    $vote->update_attributes(array('score' => $score));
    
    $this->recalculate_score();
    
    return true;
  }
  
  function voted_by() {
    $votes_tmp = User::$_->find('all', array('joins' => "JOIN post_votes v ON v.user_id = users.id", 'select' => "users.name, users.id, v.score", 'conditions' => array("v.post_id = ? and v.score > 0", $this->id), 'order' => "v.updated_at DESC"));
    
    $votes = array_fill(1, 3, array());
    
    foreach($votes_tmp as $vt) {
      $votes[(int)$vt['score']][] = $vt;
    }
    
    $this->voted_by = $votes ? $votes : false;
    
    return $this->voted_by;
  }
  
  function favorited_by() {
    if(!isset($this->voted_by))
      $this->voted_by();
    
    if(empty($this->voted_by[3]))
      return array();
    
    return $this->voted_by[3];
  }
  
  /** }
   * Count methods?
   */ 
  function get_row_count() {
    return DB::select_value("row_count FROM table_data WHERE name = 'posts'");
  }
  
  function recalculate_row_count() {
    DB::execute_sql("UPDATE table_data SET row_count = (SELECT COUNT(*) FROM posts WHERE parent_id IS NULL AND status <> 'deleted') WHERE name = 'posts'");
  }
  
  function increment_count() {
    DB::execute_sql("UPDATE table_data SET row_count = row_count + 1 WHERE name = 'posts'");
  }

  function decrement_count() {
    DB::execute_sql("UPDATE table_data SET row_count = row_count - 1 WHERE name = 'posts'");
  }
  
  function service() {
    CONFIG::local_image_service;
  }
  
  function service_icon() {
    return "/favicon.ico";
  }
}
?>