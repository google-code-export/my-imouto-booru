<?php
function exit_404() {
  echo file_get_contents(ROOT.'public/404.html');
}

function exit_500() {
  echo file_get_contents(ROOT.'public/500.html');
}
?>