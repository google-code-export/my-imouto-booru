<?php
function write_system_data_file($file, $content) {
  $f = fopen(SYSROOT.'data/'.$file.'.php', 'w+');
  fwrite($f, $content);
  fclose($f);
}

function serialize_system_data(&$arr, $var_name) {
  return '<?php System::$'.$var_name." = unserialize(stripslashes('".addslashes(serialize($arr))."')) ?>";
}

function write_system_data_array($arr, $var_name) {
  $data = '<?php
System::$' . $var_name . ' = ' . write_array($arr) . '
?>';
  
  return $data;
}

function write_array($arr) {
  $data = array();
  
  foreach ($arr as $v)
    $data[] = "'$v'";
  
  $data = 'array(
  ' . implode (",\r\n  ", $data) . '
);';
  
  return $data;
}

function to_index($notice = null) {
  if ($notice)
    $notice = "?n=$notice";
  header("Location: /sysadmin/$notice");
  exit;
}

function read_files($dir) {
  $d = dir($dir);

  while (false !== ($f = $d->read())) {
    if ($f == '.' || $f == '..' || !is_file($dir.$f))
      continue;
    $files[] = str_replace('.php', '', $f);
  }
  
  return $files;
}

function read_folders($dir) {
  $d = dir($dir);

  while (false !== ($f = $d->read())) {
    if ($f == '.' || $f == '..' || !is_dir($dir.$f))
      continue;
    $folders[] = $f;
  }
  
  return $folders;
}

function delete_files($dir) {
  $d = dir($dir);

  while (false !== ($f = $d->read())) {
    if ($f == '.' || $f == '..' || !is_file($dir.$f))
      continue;
    unlink($dir.$f);
  }
}
?>