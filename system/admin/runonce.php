<?php
$ro_dir = SYSROOT . 'admin/runonce/';
$ro_init = $ro_dir . 'initialize.php';

include $ro_init;

delete_files($ro_dir);

file_put_contents($ro_init, "<?php\r\n?>");
to_index('Script ran');
?>