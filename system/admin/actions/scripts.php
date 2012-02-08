<?php
$sc_dir = ADMINROOT . 'scripts/';
$script_log_file = ADMINROOT . 'scripts/log.php';
$msg = '';

if (!file_exists($script_log_file)) {
  file_put_contents($script_log_file, '<?php
$scripts_log = array(
);
?>');
}

$files = read_dir($sc_dir);

if (is_int(array_search('log', $files)))
  unset($files[array_search('log', $files)]);

if (isset($_GET['run'])) {
  include $sc_dir . $_GET['run'] . '.php';
  write_script_log($_GET['run']);
  header('Location: /' . System::$conf->sysadmin_base_url . '/scripts?n=' . urlencode('Script <strong>' . $_GET['run'] . '</strong> ran'));
  exit;
}

include $script_log_file;
?>
<h2>Run scripts</h2>
<?php if ($files) : ?>
<ul>
<?php
foreach ($files as $file) :
  $name = pathinfo($file, PATHINFO_FILENAME);
  if (array_key_exists($file, $scripts_log)) {
    $onclick = ' onclick="if (!confirm(\'This script has already been ran. Are you sure you want to run it again?\')) return false;"';
    $ran_on = ' <span style="color:#a6a6a6">(Ran on '.$scripts_log[$file].')</span>';
  } else {
    $ran_on = $onclick = '';
  }
?>
  <li><a href="/<?php echo System::$conf->sysadmin_base_url ?>/scripts?run=<?php echo $name ?>"<?php echo $onclick ?>><?php echo str_replace('_', ' ', $name) ?></a><?php echo $ran_on ?></li>
<?php endforeach ?>
</ul>
<?php ;else: ?>
<p>No scripts found.</p>
<?php endif ?>

<a href="/<?php echo System::$conf->sysadmin_base_url ?>">Go to index</a>
