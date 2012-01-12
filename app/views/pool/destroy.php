<h3>Delete Pool</h3>

<form action="" method="post">
  <p>Are you sure you wish to delete "<?php echo h($pool->pretty_name()) ?>"?</p>
  <?php echo submit_tag("Yes") ?> <a href="#" onclick="history.back();return false;">No</a>
</form>

<?php render_partial("footer") ?>