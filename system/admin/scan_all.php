<?php
$mdls = read_files(ROOT.'app/models/');
write_system_data_file('app_models', serialize_system_data($mdls, 'models'));

$mdls = read_files(ROOT.'app/controllers/');
write_system_data_file('app_controllers', serialize_system_data($mdls, 'controllers'));

$mdls = read_files(ROOT.'app/helpers/');
write_system_data_file('app_helpers', serialize_system_data($mdls, 'helpers'));

to_index('All files updated');
?>