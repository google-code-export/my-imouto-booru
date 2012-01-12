<?php render_partial("comment/comments", array('comments' => array($comment), 'post_id' => $comment->post_id, 'hide' => false)) ?>

<div style="clear: both;">
  <p><?php echo link_to("Return to post", array('post#show', 'id' => $comment->post_id)) ?></p>
</div>
