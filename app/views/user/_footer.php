<?php if (check_content_for('footer')) : ?>
  <?php do_content_for("subnavbar") ?>
    <?php content_for('footer') ?>
  <?php end_content_for() ?>
<?php endif ?>
