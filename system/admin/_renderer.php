<?php
ob_start();
require SYSROOT.'/admin/'.$action.'.php';
$body = ob_get_clean();

require SYSROOT.'/admin/_layout.php';
?>