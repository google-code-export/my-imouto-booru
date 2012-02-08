<?php
$tables = DB::execute_sql('SHOW TABLES FROM `'.System::$conf->dbinfo['db'].'`');

if (!$tables) {
  to_index('Warning: Couldn\'t retrieve table information.');
}

$db_tables_path = SYSROOT . 'database/tables/';

delete_files($db_tables_path);

foreach ($tables as $table) {
  $table_data = array();
  
  $table = current($table);
  
  $data = DB::execute_sql("DESCRIBE $table");
  
  foreach ($data as $d) {
    $table_data[$d['Field']] = array(
      'type'  => $d['Type']
    );
  }
  
  $idxs = db::execute_sql("SHOW INDEX FROM $table");
  $table_indexes = $pri = $uni = array();
  
  if ($idxs) {
    foreach ($idxs as $idx) {
      if ($idx['Key_name'] == 'PRIMARY') {
        $pri[] = $idx['Column_name'];
      } elseif ($idx['Non_unique'] === '0') {
        $uni[] = $idx['Column_name'];
      }
    }
  }
  
  if ($pri)
    $table_indexes['PRI'] = $pri;
  elseif ($uni)
    $table_indexes['UNI'] = $uni;
  
  $contents = '<?php $columns = unserialize(stripslashes(\'' . addslashes(serialize($table_data)) . '\'));
$indexes = unserialize(stripslashes(\'' . addslashes(serialize($table_indexes)) . '\'));
?>';
  
  file_put_contents($db_tables_path . $table . '.php', $contents);
}

// Keys: null, UNI, MUL, PRI

to_index('Database tables file updated');
?>