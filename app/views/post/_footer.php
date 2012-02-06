<?php do_content_for("subnavbar") ?>
  <li><?php echo link_to("List", "post#index") ?></li>
  <li><?php echo link_to("Upload", "post#upload") ?></li>
  <!-- <li id="my-subscriptions-container"><?php //echo link_to "Subscriptions", "/", :id => "my-subscriptions" ?></li> -->
  <li><?php echo link_to("Random", array("post", 'tags' => "order:random")) ?></li>
  <li><?php echo link_to("Popular", "post#popular_recent") ?></li>
  <li><?php echo link_to("Image Search", "post#similar") ?></li>
  <li><?php echo link_to("History", "history#index") ?></li>
  <?php if (User::is('>=30')) : ?>
    <li><?php echo link_to("Batch", "batch") ?></li>
  <?php endif ?>
  <?php if (User::is('>=33')) : ?>
    <li><?php echo link_to("Moderate", "post#moderate", array('id' => "moderate")) ?></li>
  <?php endif ?>
  <?php content_for('footer') ?>
  <li><?php echo link_to("Help", "help#posts") ?></li>
<?php end_content_for() ?>
