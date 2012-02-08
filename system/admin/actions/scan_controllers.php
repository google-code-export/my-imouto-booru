<?php
$ctrls = read_folders(ROOT . 'app/controllers/');

write_system_data_file('app_controllers', write_system_data_array($ctrls, 'controllers'));

to_index('Controllers file updated');
?>