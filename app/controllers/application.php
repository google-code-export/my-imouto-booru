<?php
require ROOT.'lib/danbooru_image_resizer.php';

if (!isset_layout())
  layout('default');

set_current_user();

init_cookies();

set_title();
?>