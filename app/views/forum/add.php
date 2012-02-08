<?php echo form_tag("#create") ?>
  <?php echo hidden_field_tag(">forum_post[parent_id]", request::$params->parent_id, array('id' => "forum_post_parent_id")) ?>
  <table>
    <tr><td><label for="forum_post_title">Title</label></td><td><?php echo text_field_tag(">forum_post[title]", array('size' => 60)) ?></td></tr>
    <tr><td colspan="2"><?php echo text_area(">forum_post[body]", array('rows' => 20, 'cols' => 80)) ?></td></tr>
    <tr><td colspan="2"><?php echo submit_tag("Post") ?></td></tr>
  </table>
</form>
