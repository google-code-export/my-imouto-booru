<?php
ForumPost::stick(request::$params->id);
notice("Topic stickied");
redirect_to("#show", array('id' => request::$params->id));
?>