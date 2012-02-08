<?php
ForumPost::unstick(request::$params->id);
notice("Topic unstickied");
redirect_to("#show", array('id' => request::$params->id));
?>