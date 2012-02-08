<?php
ForumPost::unlock(request::$params->id);
notice("Topic unlocked");
redirect_to("#show", array('id' => request::$params->id));
?>