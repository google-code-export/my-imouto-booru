<?php
Tag::recalculate_post_count();
respond_to_success('Count fixed', '#index');
?>