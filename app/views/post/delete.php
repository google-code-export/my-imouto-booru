<h4>Delete Post</h4>

<?php if (CONFIG::can_see_post(User::$current, $post)) : ?>
  <?php echo image_tag($post->preview_url) ?>
<?php endif ?>

<?php echo form_tag("#destroy") ?>
  <?php echo hidden_field_tag("id", Request::$params->id) ?>
  <label>Reason</label> <?php echo text_field_tag("reason") ?>
  <?php if ($post->is_deleted()) : ?>
  <?php echo hidden_field_tag("destroy", "1") ?>
  <?php ;else: ?>
  <br />
  <input type="hidden" name="destroy" value="0" />
  <label for="post_destroy">Destroy completely</label> <input id="post_destroy" type="checkbox" name="destroy" value="1" /><br />
  <?php endif ?>

  <?php echo submit_tag($post->is_deleted() ? "Destroy permanently":"Delete") ?> <?php echo submit_tag("Cancel") ?>

</form>

<div class="deleting-post">
<?php if (!$post->is_deleted()) : ?>
  <br />
  <?php if ($post_parent) : ?>
    Votes will be transferred to the following parent post.
    If this is incorrect, reparent this post before deleting it.<br />
  <?php if (CONFIG::can_see_post(User::$current, $post_parent)) : ?>
    <ul id="post-list-posts"> <?php echo print_preview($post_parent, array('hide_directlink' => true)) ?> </ul>
  <?php ;else: ?>
    (parent post hidden due to access restrictions)
  <?php endif ?>

  <?php ;else: ?>
    This post has no parent.  If this post has been replaced, reparent this post before deleting, and votes will be transferred.<br />
  <?php endif ?>
<?php ;else: ?>
  This post is already deleted.  Destroying it will remove it permanently.
<?php endif ?>
</div>

<?php render_partial("footer") ?>

<script type="text/javascript">$("reason").focus();</script>
