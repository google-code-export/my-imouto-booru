<div class="response-list">
  <?php foreach($comments as $c) : ?>
    <?php render_partial("comment/comment", array('comment' => $c, 'comments_avatar_posts')) ?>
  <?php endforeach ?>
</div>

<div style="clear: both;">
  <?php if (!empty($hide)) : ?>
    <h6 id="respond-link-<?php echo $post_id ?>">
      <a href="#" onclick="Comment.show_reply_form(<?php echo $post_id ?>); return false;">Reply &raquo;</a>
    </h6>
  <?php endif ?>

  
  <div id="reply-<?php echo $post_id ?>" style="<?php !empty($hide) && print 'display: none;' ?>">
    <?php echo form_tag('comment#create', array('class' => 'need-signup')) ?>
      <input id="comment_post_id_<?php echo $post_id ?>" name="comment[post_id]" type="hidden" value="<?php echo $post_id ?>">
      <textarea cols="40" id="reply-text-<?php echo $post_id ?>" name="comment[body]" rows="7" style="width: 98%; margin-bottom: 2px;"></textarea>
      <input name="commit" type="submit" value="Post">
      <!-- <input name="commit" type="submit" value="Post without bumping"> -->
    </form>
    <!--    <p style="margin-top: 1em; font-style: italic;">[spoiler]Hide spoiler text like this[/spoiler] (<a href="/help/comments">more</a>)</p> -->
  </div>

</div>

<?php if (empty($multipost)) : ?>
<script type="text/javascript">
  <?php echo avatar_init() ?>
  
  InlineImage.init();
</script>
<?php endif ?>