<?php
function update_revision_version($version) {
  $config_path = ROOT . 'config/config.php';
  $notice = "<br />Please update manually the last line in config/config.php to '# Revision: $version'";
  
  if (!file_exists($config_path))
    die("Unable to find config/config.php file!$notice");
  
  $config = file_get_contents($config_path);
  
  if (!preg_match('~# Revision: [ \.\d]+~', $config))
    die("Can't update revision version!$notice");
  
  $config = preg_replace('~# Revision: [ \.\d]+~', '# Revision: ' . $version, $config);
  
  if ($config === null)
    die("An error occured when trying to update revision version.$notice");
  
  if (!file_put_contents($config_path, $config))
    die("An error occured when trying to write config.php file.$notice");
}

$queries = array(
  'DROP TRIGGER IF EXISTS trg_posts_tags__insert',
  'CREATE TRIGGER trg_posts_tags__insert
  AFTER INSERT ON posts_tags
  FOR EACH ROW
  BEGIN
    UPDATE tags SET post_count = post_count + 1 WHERE tags.id = NEW.tag_id;
  END',
  
  'DROP TRIGGER IF EXISTS trg_posts_tags__delete',
  'CREATE TRIGGER trg_posts_tags__delete
  AFTER DELETE ON posts_tags
  FOR EACH ROW
  BEGIN
    UPDATE tags SET post_count = post_count - 1 WHERE tags.id = OLD.tag_id;
  END',
  
  "UPDATE tags SET post_count = (SELECT COUNT(*) FROM posts_tags pt, posts p WHERE pt.tag_id = tags.id AND pt.post_id = p.id AND p.status <> 'deleted')"
);

foreach ($queries as $query) {
  DB::execute_sql($query);
  if (DB::$error)
    die("There was an error executing the following query:<br />$query");
}

update_revision_version('0.0.1 3');
?>