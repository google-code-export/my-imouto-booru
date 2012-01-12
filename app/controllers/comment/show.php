<?php
set_title("Comment");
$comment = Comment::$_->find(Request::$params->id);

respond_to_list($comment);
?>