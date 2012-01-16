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

$query = 'ALTER TABLE posts DROP COLUMN cached_tags';
db::execute_sql($query);
if (db::$error)
  die("Couldn't execute query: $query");

update_revision_version('0.0.1 2');
?>