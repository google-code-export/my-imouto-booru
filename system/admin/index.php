<?php $gen_lnk = file_exists($table_data_file) ? 'Update' : 'Create' ?>
<h2>System menu</h2>
<ul>
  <li>
    <a href="gen_table_data"><?php echo $gen_lnk ?> database table data file</a>
  </li>
  
  <li>
    <a href="runonce">Run-once script</a>
  </li>
  
  <li>
    <h4>App files</h4>
    <ul>
      <li><a href="scan_all">Scan All</a></li>
      <li><a href="scan_controllers">Scan Controllers</a></li>
      <li><a href="scan_models">Scan Models</a></li>
      <li><a href="scan_helpers">Scan Helpers</a></li>
    </ul>
  </li>
</ul>
