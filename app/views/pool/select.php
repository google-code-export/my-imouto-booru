<?php echo form_tag("#add_post") ?>
  <?php echo hidden_field_tag('>post_id', Request::$params->post_id) ?>
  <?php echo select_tag('>pool_id', $options, $option_value) ?>
  <input onclick="User.run_login(false, function() { Pool.add_post(<?php echo Request::$params->post_id ?>, $F('pool_id')) });" type="button" value="Add"/>
</form>
