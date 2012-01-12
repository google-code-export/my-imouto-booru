<?php
$mdls = read_files(ROOT.'app/models/');

$contents = '<?php
$models = ' . write_array($mdls) . '
?>';

file_put_contents(SYSROOT . 'data/app_models.php', $contents);

to_index('Models file updated');
?>