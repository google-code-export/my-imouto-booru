<?php
$ctrls = read_folders(ROOT . 'app/controllers/');
write_system_data_file('app_controllers', write_system_data_array($ctrls, 'controllers'));

foreach (read_dir(ROOT.'app/helpers/') as $helper)
  $helpers[] = str_replace('_helper', '', $helper);
$contents = '<?php
$helpers = ' . write_array($helpers) . '
?>';
file_put_contents(SYSROOT . 'data/app_helpers.php', $contents);

$mdls = read_dir(ROOT.'app/models/');
$contents = '<?php
$models = ' . write_array($mdls) . '
?>';
file_put_contents(SYSROOT . 'data/app_models.php', $contents);

to_index('All files updated');
?>