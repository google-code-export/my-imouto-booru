<?php do_content_for("subnavbar") ?>
  <li><?php echo link_to("List", "#index") ?></li>
  <li><?php echo link_to("Search", "#search") ?></li>
  <li><?php echo link_to("History", "#history") ?></li>
  <li><?php echo link_to("Requests", array("post#index", 'tags' => "translation_request")) ?></li>
  <li><?php echo link_to("Help", "help#notes") ?></li>
<?php end_content_for() ?>
