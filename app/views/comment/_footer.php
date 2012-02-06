 <?php do_content_for("subnavbar") ?>
  <li><?php echo link_to("List", "#index") ?></li>
  <li><?php echo link_to("Search", "#search") ?></li>
  <?php if (User::is('>=33')) : ?>
    <li><?php echo link_to("Moderate", "#moderate") ?></li>
  <?php endif ?>
  <li><?php echo link_to("Help", "help#comments") ?></li>
<?php end_content_for() ?>
