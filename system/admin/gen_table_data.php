<?php
$tables = DB::execute_sql('SHOW TABLES FROM '.SYSCONFIG::$dbinfo['db']);

$db_tables_path = SYSROOT . 'database/tables/';

delete_files($db_tables_path);

foreach ($tables as $table) {
  $table_data = array();
  
  $table = current($table);
  
  $data = DB::execute_sql("DESCRIBE $table");
  
  foreach ($data as $d) {
    $table_data[$d['Field']] = array(
      'type'  => $d['Type'],
      'key'   => $d['Key']
    );
  }
  
  $contents = '<?php $columns = unserialize(stripslashes(\'' . addslashes(serialize($table_data)) . '\')) ?>';
  
  file_put_contents($db_tables_path . $table . '.php', $contents);
}

// Keys: null, UNI, MUL, PRI

to_index('Database tables file updated');
?>