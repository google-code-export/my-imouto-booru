<h2>New Topic</h2>

<div style="margin: 1em 0;">
  <div id="preview" class="response-list" style="display: none;">
  </div>

  <div id="reply" style="clear: both;">
    <?php echo form_tag("#create") ?>
      <?php $forum_post->parent_id && print hidden_field_tag("forum_post->parent_id") ?>
      <table>
        <tr>
          <td><label for="forum_post_title">Title</label></td>
          <td><?php echo text_field_tag("forum_post->title", array('size' => 60)) ?></td>
        </tr>
        <tr>
          <td colspan="2"><?php echo text_area("forum_post->body", array('rows' => 20, 'cols' => 80)) ?></td>
        </tr>
        <tr>
          <td colspan="2"><?php echo submit_tag("Post") ?><input name="preview" onclick="new Ajax.Updater('preview', '/forum/preview', {asynchronous:true, evalScripts:true, method:'get', onSuccess:function(request){$('preview').show()}, parameters:Form.serialize(this.form)});" type="button" value="Preview"/></td>
        </tr>
      </table>
    </form>
  </div>
  
</div>

<?php do_content_for("subnavbar") ?>
  <li><?php echo link_to("List", "#index") ?></li>
  <li><?php echo link_to("Help", "help#forum") ?></li>
<?php end_content_for() ?>

<script type="text/javascript">
  $("forum_post_title").focus();
</script>
