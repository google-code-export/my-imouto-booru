<?php
User::$current->update_attribute('last_forum_topic_read_at', gmd());
render(false);
?>