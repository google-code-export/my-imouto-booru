<?php
class CONFIG {
  /*
   * Site information and options.
   */
  
  const server_host = 'localhost:3000';
  
  # Base URL with no trailing slash.
  const url_base = 'http://localhost:3000';
  
  # Name for your site and default page title.
  const app_name = 'my.imouto';
  
  const version = '0.0.1';
  
  # Default user level.
  const starting_level = 30;
  
  /**
   * New configs.
   */
  const enable_asynchronous_tasks = false;
  const force_image_samples = false;
  const enable_account_email_activation = false;
  const enable_parent_posts = true;
  const enable_artists = true;
  const hide_pending_posts = true;
  const tag_query_limit = 6;
  const vote_record_min = 0;
  const vote_record_max = 3;
  const local_image_service = "localhost";
  const min_mpixels = null;
  const max_pending_images = null;
  const dupe_check_on_upload = false;
  
  const use_pretty_image_urls = true;
  
  const sample_filename_prefix = '';
  
  const sample_ratio = 1;
  
  const image_samples = true;
  
  # Creates a fake sample_url for posts without a sample, so they can be zoomed-in in browse mode.
  # This is specifically useful if you're not creating image samples.
  const fake_sample_url = true;
  
  const jpeg_enable = true;
  
  const sample_width = null;
  const sample_height = 1000; # Set to null if you never want to scale an image to fit on the screen vertically
  const sample_quality = 92;
  const sample_always_generate_size = 524288; // 512*1024
  
  const sample_max = 1500;
  const sample_min = 1200;
  
  # Scale JPEGs to fit in these dimensions.
  const jpeg_width = 3500;
  const jpeg_height = 3500;
  
  # Resample the image only if the image is larger than jpeg_ratio * jpeg_dimensions.  If
  # not, PNGs can still have a JPEG generated, but no resampling will be done.
  const jpeg_ratio = 1.25;
  static $jpeg_quality = array('min' => 94, 'max' => 97, 'filesize' => 4194304 /*1024*1024*4*/);
  
  const default_index_limit = 16;
  
  const member_comment_limit = 20;
  const enable_signups = true;
  
  const avatar_max_width = 125;
  const avatar_max_height = 125;
  
  static function can_see_post($user, $post) {
    # By default, no posts are hidden.
    return true;
    
    # Some examples:
    #
    # Hide post if user isn't privileged and post is not safe:
    # if($post->rating == 'e' && $user->is('>=20')) return true;
    # 
    # Hide post if user isn't a mod and post has the loli tag:
    # if($post->has_tag('loli') && $user->is('>=40')) return true;
  }
  
  static function can_see_ads(&$user) {
    return $user->is('<=20');
  }
  
  # Show homepage or redirect to /post otherwise.
  const show_homepage = true;
  
  # Show chibi moe imoutos in homepage.
  const show_homepage_imoutos = true;
  
  # Default reason to delete posts. Leave blank to force typing a reason.
  const default_post_delete_reason = 'Default reason';
  
  const allow_delete_tags = true;
  
  # Enables quick edit form in /post/show.
  # Many, many weeks after adding this feature I realized the
    # "Edit" link on the sidebar is a quick access to the edit form.
    # So this may not be too useful, but I like it anyway.
  const enable_quick_edit = true;
  
  static $user_levels = array (
    "Unactivated" => 0,
    "Blocked"     => 10,
    "Member"      => 20,
    "Privileged"  => 30,
    "Contributor" => 33,
    "Janitor"     => 35,
    "Mod"         => 40,
    "Admin"       => 50
  );
  
  static $tag_types = array(
    "General"   => 0,
    "general"   => 0,
    "Artist"    => 1,
    "artist"    => 1,
    "art"       => 1,
    "Copyright" => 3,
    "copyright" => 3,
    "copy"      => 3,
    "Character" => 4,
    "character" => 4,
    "char"      => 4,
    "Circle"    => 5,
    "circle"    => 5,
    "cir"       => 5,
    "Faults"    => 6,
    "faults"    => 6,
    "fault"     => 6,
    "flt"       => 6
  );
  
  static $exclude_from_tag_sidebar = array(0, 6);
  
  static $default_blacklists = array (
    "rating:q",
    "rating:e",
    "rating:e loli",
    "rating:e shota",
    "extreme_content"
  );
  
  # (Next 2 arrays will be filled when including config/languages.php)
  static $language_names = array();
  
  # (This var doesn't seem to be useful)
  static $known_languages = array();
  
  # Languages that we support translating to.  We'll translate each comment into all of these
  # languages.  Set this to array() to disable translation.
  static $translate_languages = array();
  // static $translate_languages = array('en', 'ja', 'zh-CN', 'zh-TW', 'es'):
  
  const pool_zips = false;
  const comment_threshold = 9999;


  
  /*
   * Upload.
   */
  # Allowed mime-types, separatted by comma and space.
    # For now nothing but JPEG and PNG will work, i.e. don't edit this.
  static $allowed_mime_types = array(
    'image/pjpeg' => 'jpg',
    'image/jpeg'  => 'jpg',
    'image/png'   => 'png'
  );
  
  # Custom error output for unsupported mime types.
  const mime_type_error = 'Only JPEG and PNG images are allowed.';
  
  # Default rating for upload (e, q or s).
  const default_rating_upload = 'q';
  
  # Default rating for import (e, q or s).
  const default_rating_import = 'q';
  
  # Note: These next 2 options may need a lot of memory.
  # Create a JPEG version of PNG files.
  # CHANGED for jpeg_enable
  // const create_jpeg = false;

  # Create samples (a smaller JPEG version).
  # CHANGED for image_samples
  // const create_samples = false;

  /**
   * Parse moe imouto filenames on post creation
   *
   * These only work if filename is like "moe 123 tag_1 tag_2".
   */
  # Take tags from filename.
  const tags_from_moe_filename = true;
  # Automatically create source for images.
  const source_from_moe_filename = true;

  # Prefix for images (set to false to disable).
  const download_filename_prefix = "moe";
  
  /*
   * Others/useless.
   */
  # Define which CSS files in /stylesheets you want to use.
  const stylesheets = 'imouto';
  
  # Max tag-subscriptions per user.
  const max_tagsubs = 5;
  
}
# Do not edit this:
# Revision: 0.0.1 1
?>