    <div id="comments" style="margin-top: 1em; max-width: 800px; width: 100%;">
<?php
$comments = Comment::$_->collection('find', array('conditions' => array('post_id = ?', $post->id), 'order' => 'id'));
render_partial("comment/comments", array('comments' => $comments, 'post_id' => $post->id, 'hide' => false));
?>
    </div>

    <?php if(isset($page_uses_translations)) : ?>
      <?php do_content_for("above_footer") ?>
        Comment translation provided by <a href="http://translate.google.com">Google</a>.
        <br />
      <?php end_content_for() ?>
    <?php endif ?>

