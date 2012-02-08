<?php if ($forum_post->is_locked) : ?>
  <div class="status-notice">
    <p>This topic is locked.</p>
  </div>
<?php endif ?>

<div id="forum" class="response-list">
  <?php if (Request::$params->page <= 1) : ?>
    <?php render_partial("post", array('post' => $forum_post)) ?>
  <?php endif ?>

  <?php foreach ($children as $c): ?>
    <?php render_partial("post", array('post' => $c)) ?>
  <?php endforeach ?>
</div>

<?php if (!$forum_post->is_locked): ?>
  <div style="clear: both;">
    
    <div id="preview" class="response-list" style="display: none; margin: 1em 0;">
    </div>

    <div id="reply" style="display: none; clear: both;">
      <form action="/forum/create" class="need-signup" method="post">
        <?php echo hidden_field_tag(">forum_post[title]", "") ?>
        <?php echo hidden_field_tag(">forum_post[parent_id]", $forum_post->root_id) ?>
        <?php echo text_area(">forum_post[body]", array('rows' => 20, 'cols' => 80, 'value' => "")) ?>
        <?php echo submit_tag("Post") ?>
        <input name="preview" onclick="new Ajax.Updater('preview', '/forum/preview', {asynchronous:true, evalScripts:true, method:'get', onSuccess:function(request){$('preview').show()}, parameters:Form.serialize(this.form)});" type="button" value="Preview"/>
      </form>
    </div>    
  </div>
<?php endif ?>

<div id="paginator">
  <?php paginator() ?>
</div>

<script type="text/javascript">
  <?php echo avatar_init() ?>
  InlineImage.init();
</script>

<?php do_content_for("subnavbar") ?>
  <?php if (!$forum_post->is_locked): ?>
    <li><?php echo link_to_function("Reply", "Element.toggle('reply')") ?></li>
  <?php endif ?>
  <li><?php echo link_to("List", "#index") ?></li>
  <li><?php echo link_to("New topic", "#new") ?></li>
  <?php if (!$forum_post->is_parent): ?>
    <li><?php echo link_to("Parent", array("#show", 'id' => $forum_post->parent_id)) ?></li>
  <?php endif ?>
  <li><?php echo link_to("Help", "help#forum") ?></li>
<?php end_content_for() ?>
