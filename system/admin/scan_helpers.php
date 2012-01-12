<?php
foreach (read_files(ROOT.'app/helpers/') as $helper)
  $helpers[] = str_replace('_helper', '', $helper);

$contents = '<?php
$helpers = ' . write_array($helpers) . '
?>';

file_put_contents(SYSROOT . 'data/app_helpers.php', $contents);

to_index('Helpers file updated');
?>