<?php
ForumPost::lock(request::$params->id);
notice("Topic locked");
redirect_to("#show", array('id' => request::$params->id));
?>