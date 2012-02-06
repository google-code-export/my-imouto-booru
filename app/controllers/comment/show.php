<?php
set_title("Comment");
$comment = Comment::find(Request::$params->id);

respond_to_list($comment);
?>