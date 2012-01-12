<div id="note-list">
  <?php render_partial("post/posts", array('posts' => $posts)) ?>

  <div id="paginator">
    <?php paginator() ?>
  </div>

  <?php render_partial("footer") ?>
</div>
