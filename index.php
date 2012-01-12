<?php
define('ROOT', str_replace('\\', '/', dirname(__FILE__)) . '/');
define('SYSROOT',   ROOT . 'system/');

include ROOT.'config/config.php';
include SYSROOT.'config.php';
include SYSROOT.'database/initialize.php';
include SYSROOT.'load_functions.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
  set_time_limit(0);
  
  $queries = array(
    "CREATE TABLE IF NOT EXISTS `bans` (
      `user_id` int(11) NOT NULL,
      `reason` text NOT NULL,
      `expires_at` datetime NULL,
      `banned_by` int(11) NOT NULL,
      `old_level` int(11) NOT NULL,
      `delete_me` int(11) NOT NULL,
      KEY `user_id` (`user_id`),
      KEY `delete_me` (`delete_me`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8",

    "CREATE TABLE IF NOT EXISTS `comments` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `post_id` int(11) NOT NULL,
      `user_id` int(11) NOT NULL,
      `ip_addr` varchar(16) NOT NULL,
      `created_at` datetime DEFAULT '0000-00-00 00:00:00',
      `body` text NOT NULL,
      `updated_at` datetime NULL,
      PRIMARY KEY (`id`),
      KEY `image_id` (`post_id`),
      KEY `owner_ip` (`ip_addr`),
      KEY `posted` (`created_at`),
      KEY `fk_comments__user_id` (`user_id`)
    ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ",

    "CREATE TABLE IF NOT EXISTS `favorites` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `post_id` int(11) NOT NULL,
      `user_id` int(11) NOT NULL,
      `created_at` datetime DEFAULT '0000-00-00 00:00:00',
      PRIMARY KEY (`id`),
      UNIQUE KEY `image_id` (`post_id`,`user_id`)
    ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ",

    "CREATE TABLE IF NOT EXISTS `flagged_post_details` (
      `created_at` datetime NULL,
      `post_id` int(11) NOT NULL,
      `user_id` int(11) NOT NULL,
      `reason` varchar(512) NOT NULL,
      `is_resolved` tinyint(1) NOT NULL DEFAULT '0',
      KEY `post_id` (`post_id`),
      KEY `fk_flag_post_details__user_id` (`user_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8",

    "CREATE TABLE IF NOT EXISTS `notes` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `created_at` DATETIME NULL,
      `updated_at` DATETIME NULL,
      `user_id` int(11) NOT NULL,
      `x` int(11) NOT NULL,
      `y` int(11) NOT NULL,
      `width` int(11) NOT NULL,
      `height` int(11) NOT NULL,
      `ip_addr` varchar(64) NOT NULL,
      `version` int(11) NOT NULL DEFAULT '1',
      `is_active` tinyint(1) NOT NULL DEFAULT '1',
      `post_id` int(11) NOT NULL,
      `body` text NOT NULL,
      PRIMARY KEY (`id`),
      KEY `post_id` (`post_id`),
      KEY `user_id` (`user_id`)
    ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ",

    "CREATE TABLE IF NOT EXISTS `note_versions` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `created_at` DATETIME NULL,
      `updated_at` DATETIME NULL,
      `x` int(11) NOT NULL,
      `y` int(11) NOT NULL,
      `width` int(11) NOT NULL,
      `height` int(11) NOT NULL,
      `body` int(11) NOT NULL,
      `version` int(11) NOT NULL,
      `ip_addr` varchar(64) NOT NULL,
      `is_active` tinyint(1) NOT NULL DEFAULT '1',
      `note_id` int(11) NOT NULL,
      `post_id` int(11) NOT NULL,
      `user_id` int(11) NOT NULL,
      PRIMARY KEY (`id`),
      KEY `user_id` (`user_id`),
      KEY `fk_note_versions__note_id` (`note_id`)
    ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ",

    "CREATE TABLE IF NOT EXISTS `pools` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `name` varchar(255) NOT NULL,
      `description` varchar(128) NOT NULL,
      `user_id` int(11) NOT NULL,
      `is_active` tinyint(1) NOT NULL DEFAULT '1',
      `created_at` DATETIME NULL,
      `updated_at` DATETIME NULL,
      `post_count` int(3) NOT NULL DEFAULT '0',
      `is_public` binary(1) NOT NULL DEFAULT '1',
      PRIMARY KEY (`id`),
      UNIQUE KEY `pool_name` (`name`)
    ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ",

    "CREATE TABLE IF NOT EXISTS `pools_posts` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `post_id` int(11) NOT NULL,
      `pool_id` int(11) NOT NULL,
      `sequence` varchar(16) NOT NULL,
      `next_post_id` int(11) DEFAULT NULL,
      `prev_post_id` int(11) DEFAULT NULL,
      PRIMARY KEY (`id`),
      KEY `post_id` (`post_id`),
      KEY `fk_pools_posts__next_post_id` (`next_post_id`),
      KEY `fk_pools_posts__prev_post_id` (`prev_post_id`),
      KEY `fk_pools_posts__pool_id` (`pool_id`)
    ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ",
    
    "DROP TRIGGER IF EXISTS `pools_posts_insert_trg`",
    
    "CREATE TRIGGER `pools_posts_insert_trg` BEFORE INSERT ON `pools_posts`
     FOR EACH ROW UPDATE pools SET post_count = post_count + 1 WHERE id = NEW.pool_id",
   
    
    "DROP TRIGGER IF EXISTS `pools_posts_delete_trg`",
    
    
    "CREATE TRIGGER `pools_posts_delete_trg` BEFORE DELETE ON `pools_posts`
     FOR EACH ROW UPDATE pools SET post_count = post_count - 1 WHERE id = OLD.pool_id",

    "CREATE TABLE IF NOT EXISTS `posts` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `user_id` int(11) NOT NULL,
      `ip_addr` varchar(64) NOT NULL,
      `file_size` int(11) NOT NULL,
      `md5` varchar(32) NOT NULL,
      `last_commented_at` datetime DEFAULT NULL,
      `file_ext` varchar(4) NOT NULL,
      `last_noted_at` datetime DEFAULT NULL,
      `source` varchar(249) DEFAULT NULL,
      `cached_tags` text NOT NULL,
      `width` int(11) NOT NULL,
      `height` int(11) NOT NULL,
      `created_at` datetime NULL,
      `rating` char(1) NOT NULL DEFAULT 'q',
      `note` varchar(255) NOT NULL,
      `preview_width` int(3) NOT NULL,
      `preview_height` int(3) NOT NULL,
      `actual_preview_width` int(3) NOT NULL,
      `actual_preview_height` int(3) NOT NULL,
      `score` int(3) NOT NULL,
      `is_shown_in_index` tinyint(1) NOT NULL DEFAULT '1',
      `is_held` tinyint(1) NOT NULL DEFAULT '0',
      `has_children` tinyint(1) NOT NULL DEFAULT '0',
      `status` enum('deleted','flagged','pending','active') CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT 'active',
      `is_rating_locked` tinyint(1) NOT NULL DEFAULT '0',
      `is_note_locked` tinyint(1) NOT NULL DEFAULT '0',
      `parent_id` int(11) DEFAULT NULL,
      `sample_width` int(5) DEFAULT NULL,
      `sample_height` int(5) DEFAULT NULL,
      `sample_size` int(11) DEFAULT NULL,
      `index_timestamp` datetime NULL,
      `jpeg_width` int(11) DEFAULT NULL,
      `jpeg_height` int(11) DEFAULT NULL,
      `jpeg_size` int(11) DEFAULT NULL,
      `random` int(11) NOT NULL,
      PRIMARY KEY (`id`),
      UNIQUE KEY `images__hash` (`md5`),
      KEY `images__owner_id` (`user_id`),
      KEY `images__width` (`width`),
      KEY `images__height` (`height`),
      KEY `fk_posts__parent_id` (`parent_id`)
    ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ",
    
    "DROP TRIGGER IF EXISTS `trg_posts__insert`",
    
    "CREATE TRIGGER `trg_posts__insert` AFTER INSERT ON `posts`
     FOR EACH ROW UPDATE table_data SET row_count = row_count + 1 WHERE name = 'posts'",
    
    "DROP TRIGGER IF EXISTS `trg_posts__delete`",
    
    "CREATE TRIGGER `trg_posts__delete` BEFORE DELETE ON `posts`
     FOR EACH ROW UPDATE pools SET post_count = post_count - 1 WHERE id IN (SELECT pool_id FROM pools_posts WHERE post_id = OLD.id)",

    "CREATE TABLE IF NOT EXISTS `posts_tags` (
      `post_id` int(11) NOT NULL,
      `tag_id` int(11) NOT NULL,
      UNIQUE KEY `post_id` (`post_id`,`tag_id`),
      KEY `fk_posts_tags__post_id` (`post_id`),
      KEY `fk_posts_tags__tag_id` (`tag_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8",
    
    "DROP TRIGGER IF EXISTS `trg_posts_tags__insert`",
    
    "CREATE TRIGGER `trg_posts_tags__insert` BEFORE INSERT ON `posts_tags`
     FOR EACH ROW UPDATE tags SET post_count = post_count + 1 WHERE tags.id = NEW.tag_id",
    
    "DROP TRIGGER IF EXISTS `trg_posts_tags__delete`",
    
    "CREATE TRIGGER `trg_posts_tags__delete` BEFORE DELETE ON `posts_tags`
     FOR EACH ROW UPDATE tags SET post_count = post_count - 1 WHERE tags.id = OLD.tag_id",
    

    "CREATE TABLE IF NOT EXISTS `post_votes` (
      `post_id` int(11) NOT NULL,
      `user_id` int(11) NOT NULL,
      `score` int(1) DEFAULT '0',
      `updated_at` datetime NULL DEFAULT '0000-00-00 00:00:00',
      UNIQUE KEY `post_id` (`post_id`,`user_id`),
      KEY `score` (`score`),
      KEY `fk_user_id__users_id` (`user_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8",

    "CREATE TABLE IF NOT EXISTS `table_data` (
      `name` varchar(11) CHARACTER SET ucs2 NOT NULL,
      `row_count` int(11) NOT NULL DEFAULT '0',
      KEY `name` (`name`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8",

    "CREATE TABLE IF NOT EXISTS `tags` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `name` varchar(64) NOT NULL,
      `post_count` int(11) NOT NULL DEFAULT '0',
      `cached_related` text,
      `cached_related_expires_on` datetime DEFAULT NULL,
      `tag_type` smallint(6) NOT NULL,
      `is_ambiguous` tinyint(1) NOT NULL DEFAULT '0',
      PRIMARY KEY (`id`),
      UNIQUE KEY `tags__name` (`name`)
    ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ",

    "CREATE TABLE IF NOT EXISTS `tag_aliases` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `name` varchar(64) NOT NULL,
      `alias_id` int(11) NOT NULL,
      `is_pending` tinyint(1) NOT NULL DEFAULT '0',
      `reason` varchar(128) NOT NULL,
      PRIMARY KEY (`id`),
      UNIQUE KEY `alias_unique` (`name`,`alias_id`),
      KEY `name` (`name`),
      KEY `alias_id` (`alias_id`)
    ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ",

    "CREATE TABLE IF NOT EXISTS `tag_implications` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `predicate_id` int(11) NOT NULL,
      `consequent_id` int(11) NOT NULL,
      `is_pending` tinyint(1) NOT NULL DEFAULT '0',
      `reason` varchar(128) NOT NULL,
      PRIMARY KEY (`id`),
      UNIQUE KEY `implication_unique` (`predicate_id`,`consequent_id`),
      KEY `fk_consequent_id` (`consequent_id`)
    ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ",

    "CREATE TABLE IF NOT EXISTS `tag_subscriptions` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `user_id` int(11) NOT NULL,
      `tag_query` text CHARACTER SET latin1 NOT NULL,
      `cached_post_ids` text CHARACTER SET latin1 NOT NULL,
      `name` varchar(32) CHARACTER SET latin1 NOT NULL,
      `is_visible_on_profile` tinyint(1) NOT NULL DEFAULT '1',
      PRIMARY KEY (`id`),
      KEY `user_id` (`user_id`),
      KEY `name` (`name`)
    ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ",

    "CREATE TABLE IF NOT EXISTS `users` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `name` varchar(32) NOT NULL,
      `password_hash` varchar(32) DEFAULT NULL,
      `created_at` datetime NULL,
      `level` int(11) NOT NULL DEFAULT '20',
      `email` varchar(249) DEFAULT NULL,
      `avatar_post_id` int(11) DEFAULT NULL,
      `avatar_width` double DEFAULT NULL,
      `avatar_height` double DEFAULT NULL,
      `avatar_top` double DEFAULT NULL,
      `avatar_bottom` double DEFAULT NULL,
      `avatar_left` double DEFAULT NULL,
      `avatar_right` double DEFAULT NULL,
      `avatar_timestamp` datetime NULL,
      `my_tags` text,
      `show_samples` tinyint(1) NOT NULL DEFAULT '1',
      `show_advanced_editing` tinyint(1) NOT NULL DEFAULT '0',
      `pool_browse_mode` tinyint(1) NOT NULL DEFAULT '0',
      `use_browser` tinyint(1) NOT NULL DEFAULT '0',
      `always_resize_images` tinyint(1) NOT NULL DEFAULT '0',
      `last_logged_in_at` datetime NULL,
      `last_commented_read_at` datetime NULL,
      `last_deleted_post_seen_at` datetime NULL,
      `language` text NOT NULL,
      `secondary_languages` text NOT NULL,
      `receive_dmails` tinyint(1) NOT NULL DEFAULT '1',
      PRIMARY KEY (`id`),
      UNIQUE KEY `users__name` (`name`),
      KEY `fk_users__avatar_post_id` (`avatar_post_id`)
    ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ",

    "CREATE TABLE IF NOT EXISTS `user_blacklisted_tags` (
      `user_id` int(11) NOT NULL,
      `tags` text CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
      UNIQUE KEY `user_id` (`user_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8",


    "ALTER TABLE `comments`
      ADD CONSTRAINT `fk_comments__post_id` FOREIGN KEY (`post_id`) REFERENCES `posts` (`id`) ON DELETE CASCADE,
      ADD CONSTRAINT `fk_comments__user_id` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE",

    "ALTER TABLE `flagged_post_details`
      ADD CONSTRAINT `fk_flag_post_details__user_id` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
      ADD CONSTRAINT `fk_flag_post_det__post_id` FOREIGN KEY (`post_id`) REFERENCES `posts` (`id`) ON DELETE CASCADE",

    "ALTER TABLE `pools_posts`
      ADD CONSTRAINT `fk_pools_posts__next_post_id` FOREIGN KEY (`next_post_id`) REFERENCES `posts` (`id`) ON DELETE SET NULL,
      ADD CONSTRAINT `fk_pools_posts__pool_id` FOREIGN KEY (`pool_id`) REFERENCES `pools` (`id`) ON DELETE CASCADE,
      ADD CONSTRAINT `fk_pools_posts__post_id` FOREIGN KEY (`post_id`) REFERENCES `posts` (`id`) ON DELETE CASCADE,
      ADD CONSTRAINT `fk_pools_posts__prev_post_id` FOREIGN KEY (`prev_post_id`) REFERENCES `posts` (`id`) ON DELETE SET NULL",

    "ALTER TABLE `posts`
      ADD CONSTRAINT `fk_parent_id__posts_id` FOREIGN KEY (`parent_id`) REFERENCES `posts` (`id`) ON DELETE SET NULL",

    "ALTER TABLE `posts_tags`
      ADD CONSTRAINT `fk_posts_tags__post_id` FOREIGN KEY (`post_id`) REFERENCES `posts` (`id`) ON DELETE CASCADE,
      ADD CONSTRAINT `fk_posts_tags__tag_id` FOREIGN KEY (`tag_id`) REFERENCES `tags` (`id`) ON DELETE CASCADE",

    "ALTER TABLE `post_votes`
      ADD CONSTRAINT `fk_post_id__posts_id` FOREIGN KEY (`post_id`) REFERENCES `posts` (`id`) ON DELETE CASCADE,
      ADD CONSTRAINT `fk_user_id__users_id` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE",

    "ALTER TABLE `tag_aliases`
      ADD CONSTRAINT `fk_alias_id` FOREIGN KEY (`alias_id`) REFERENCES `tags` (`id`) ON DELETE CASCADE",

    "ALTER TABLE `tag_implications`
      ADD CONSTRAINT `fk_consequent_id` FOREIGN KEY (`consequent_id`) REFERENCES `tags` (`id`) ON DELETE CASCADE,
      ADD CONSTRAINT `fk_predicate_id` FOREIGN KEY (`predicate_id`) REFERENCES `tags` (`id`) ON DELETE CASCADE",

    "ALTER TABLE `user_blacklisted_tags`
      ADD CONSTRAINT `fk_user_bl_tags__user_id` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE",

    "INSERT INTO `table_data` VALUES ('posts', 0)",
    "INSERT INTO `table_data` VALUES ('users', 0)",
    "INSERT INTO `table_data` VALUES ('non-explicit_posts', 0)"
  );

  foreach ($queries as $query)
    DB::execute_sql($query);

  extract($_POST);
  $password_hash = md5($name, $password);

  $user_id = DB::insert('users (created_at, name, password_hash, level, show_advanced_editing) VALUES (?, ?, ?, ?, ?)', gmd(), $name, $password_hash, 50, 1);
  DB::insert('user_blacklisted_tags VALUES (?, ?)', $user_id, implode("\r\n", CONFIG::$default_blacklists));
  DB::update("table_data set row_count = row_count + 1 where name = 'users'");

  $dp = ROOT . 'public/data/';

  foreach (array($dp, "$dp/avatars", "$dp/export", "$dp/image", "$dp/import", "$dp/jpeg", "$dp/preview", "$dp/sample") as $dir)
    @mkdir($dir);

  unlink('index.php');
  rename('index_.php', 'index.php');
  
  setcookie('login', $name, time()+(60*60*24*365), '/');
  setcookie('pass_hash', $password_hash, time()+(60*60*24*365), '/');
  
  notice('Installation completed');
  header('Location: /');
  exit;
}

if (function_exists('finfo_open')) {
  $finfo =  "Enabled";
  $finfo_class = "good";
  $finfo_notice = '';
} else {
  $finfo =  "Not enabled";
  $finfo_class = "bad";
  $finfo_notice = '<p class="center_box" style="margin-top:-15px;font-weight:700;"><a href="#">Enable php_fileinfo.dll library in php.ini and restart your server.</a></p>';
}
?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
"http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
  <title><?php echo CONFIG::app_name ?></title>
  <meta name="description" content=" ">
  <link rel="top" title="<?php echo CONFIG::app_name ?>" href="/">
  <link rel="shortcut icon" href="/favicon.ico" type="image/x-icon">
  <link href="/stylesheets/default.css" media="screen" rel="stylesheet" type="text/css">
  <script src="/javascripts/application.js" type="text/javascript"></script>
  <style type="text/css">
    .center_box{
      width:550px;
      margin-left:auto;
      margin-right:auto;
      margin-bottom:10px;
    }
    
    .good{ color:#0f0;}
    .okay{ color:orange;}
    .bad{ color:red;}
  </style>
</head>
<body>

  <div class="overlay-notice-container" id="notice-container" style="display: none;">
    <table cellspacing="0" cellpadding="0"> <tbody>
      <tr> <td>
        <div id="notice">
        </div>
      </td> </tr>
    </tbody> </table>
  </div>

  <div id="content">

    <h1 id="static-index-header" style="margin-bottom:50px;"><a href="/"><?php echo CONFIG::app_name ?></a></h1>
    
    <div class="center_box"><h5>PHP.ini directives</h5></div>
    <table class="form" style="margin-left:auto; margin-right:auto; width:550px; text-align:center;">

      <tr>
        <th style="text-align:center; background-color:#555;">Name</th>
        <th style="text-align:center; background-color:#555;">Current value</th>
        <th style="text-align:center; background-color:#555;">Recommended min. value</th>
      </tr>
      
      <tr>
        <th>memory_limit</th>
        <td><?php echo ini_get('memory_limit') ?></td>
        <td>256M</td>
      </tr>
      
      <tr>
        <th>post_max_size</th>
        <td><?php echo ini_get('post_max_size') ?></td>
        <td>64M</td>
      </tr>
      
      <tr>
        <th>upload_max_filesize</th>
        <td><?php echo ini_get('upload_max_filesize') ?></td>
        <td>64M</td>
      </tr>
      
      <tr>
        <th>extension=php_fileinfo.dll</th>
        <td class="<?php echo $finfo_class ?>" id="finfo_info"><?php echo $finfo ?></td>
        <td>Must be enabled</td>
      </tr>
    
    </table>
	<?php echo $finfo_notice ?>
    
    <br />
    <br />
    
    <div class="center_box"><h5>Admin account</h5></div>
    <form action="" method="post" name="install_form">
      <table class="form" style="margin-left:auto; margin-right:auto; width:550px;">
        <tr>
          <th>Name</th>
          <td><input type="text" name="name" id="name" /></td>
        </tr>
        
        <tr>
          <th>Password</th>
          <td><input type="password" name="password" id="pw" /></td>
        </tr>
        
        <tr>
          <th>Confirm password</th>
          <td><input type="password" name="confirm_pw" id="pwc" /></td>
        </tr>
        <tr>
          <td><input type="submit" value="Install" onclick="install(); return false;" /></td>
        </tr>
      </table>
    </form>
  </div>
  
  <script type="text/javascript">
    var finfo = $('finfo_info').innerHTML;
    
    function install(){
      if ( finfo != 'Enabled' ){
        notice("FileInfo library must be enabled");
        return false;
      }
   
      var pw = $('pw').value;
      var pwc = $('pwc').value;
      var name = $('name').value;

      if ( name == '' ) {
        notice("Enter a name");
        $('name').focus();
        return false;
      } else if ( name.length < 2 ) {
        notice("Name must be at least 2 characters long");
        return false;
      } else if ( pw == '' ) {
        notice("Enter a password");
        $('pw').focus();
        return false;
      } else if ( pw.length < 5 ) {
        notice("Password must be at least 5 characters long");
        $('pw').focus();
        return false;
      } else if ( pw != pwc ) {
        notice("Passwords don't match");
        $('pwc').focus();
        return false;
      } else 
        document.install_form.submit()
    }
  
    var text = Cookie.get("notice");
    if (text) {
      notice(text, true);
      Cookie.remove("notice");
    }
  </script>

</body>
</html>