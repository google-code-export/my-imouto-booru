<div id="comment-edit">
  <h4>Edit Comment</h4>

  <?php echo form_tag("#update") ?>
    <?php echo hidden_field_tag("id", Request::$params->id) ?>
    <?php echo text_area("comment->body", array('rows' => 10, 'cols' => 60)) ?><br>
    <?php echo submit_tag("Save changes") ?>
  </form>
</div>
