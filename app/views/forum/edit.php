<div id="preview" style="display: none; margin: 1em 0; width: 60em;">
</div>

<?php echo form_tag("#update") ?>
  <?php echo hidden_field_tag("id", request::$params->id) ?>
  <table>
    <tr><td><label for="forum_post_title">Title</label></td><td><?php echo text_field_tag("forum_post->title", array('size' => 60)) ?></td></tr>
    <tr><td colspan="2"><?php echo text_area("forum_post->body", array('rows' => 20, 'cols' => 80)) ?></td></tr>
    <tr><td colspan="2">
      <?php echo submit_tag("Post") ?>
      <input name="preview" onclick="new Ajax.Updater('preview', '/forum/preview', {asynchronous:true, evalScripts:true, method:'get', onSuccess:function(request){$('preview').show()}, parameters:Form.serialize(this.form)});" type="button" value="Preview"/>
    </td></tr>
  </table>
</form>
