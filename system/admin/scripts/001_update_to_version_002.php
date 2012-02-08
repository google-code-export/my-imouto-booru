<?php
function update_config_version() {
  $config_file = ROOT . 'config/config.php';
  
  if (!file_exists($config_file))
    die("Unable to find config/config.php file!");
  
  $config = file_get_contents($config_file);
  
  $config = str_replace('
# Do not edit this:
# Revision: 0.0.1 3', '', $config);
  
  $config = str_replace("const version = '0.0.1';", "# Do not edit the following line.
  const version = '0.0.2'; # Revision 4", $config);
  
  file_put_contents($config_file, $config);
}

$queries = array(
  "CREATE TABLE IF NOT EXISTS `forum_posts` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `created_at` datetime NOT NULL,
    `updated_at` datetime NOT NULL,
    `title` text NOT NULL,
    `body` text NOT NULL,
    `creator_id` int(11) NOT NULL,
    `parent_id` int(11) NULL,
    `last_updated_by` int(11) NULL,
    `is_sticky` tinyint(1) NOT NULL DEFAULT '0',
    `response_count` int(11) NOT NULL,
    `is_locked` tinyint(1) NOT NULL DEFAULT '0',
    `text_search_index` text NOT NULL,
    PRIMARY KEY (`id`)
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1",
  
  "ALTER TABLE forum_posts ADD CONSTRAINT fk_forum_posts__creator_id FOREIGN KEY (creator_id) REFERENCES users(id) ON DELETE CASCADE,
  ADD CONSTRAINT fk_forum_posts__last_updated_by FOREIGN KEY (last_updated_by) REFERENCES users(id) ON DELETE SET NULL,
  ADD CONSTRAINT fk_forum_posts__parent_id FOREIGN KEY (parent_id) REFERENCES forum_posts(id) ON DELETE CASCADE",
  
  "ALTER TABLE `users` ADD `last_forum_topic_read_at` DATETIME NULL AFTER `last_logged_in_at`"
);

foreach ($queries as $sql)
  db::execute_sql($sql);

update_config_version();
?>