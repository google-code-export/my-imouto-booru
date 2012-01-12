<?php do_content_for('subnavbar') ?>
  <li><?php echo link_to('List', 'tag#index') ?></li>
  <li><?php echo link_to('Popular', 'tag#popular_by_day') ?></li>
  <li><?php echo link_to('Aliases', 'tag_alias#index') ?></li>
  <li><?php echo link_to('Implications', 'tag_implication#index') ?></li>
  <?php if (User::$current->is('>=40')) : ?>
    <li><?php echo link_to('Mass edit', 'tag#mass_edit') ?></li>
  <?php endif ?>
  <li><?php echo link_to('Edit', 'tag#edit') ?></li>
  <?php echo content_for('footer') ?>
  <li><?php echo link_to('Help', 'help#tags') ?></li>
<?php end_content_for() ?>
