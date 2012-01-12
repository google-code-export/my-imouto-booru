<h3>Create Pool</h3>

<?php echo form_tag("#create", array('class' => 'need-signup')) ?>
  <table class="form">
    <tbody>
      <tr>
        <th><label for="pool_name">Name</label></th>
        <td><?php echo text_field_tag("pool[name]") ?></td>
      </tr>
      <tr>
        <th><label for="pool_is_public">Public?</label></th>
        <td><?php echo checkbox_tag("pool{is_public]") ?></td>
      </tr>
      <tr>
        <th><label for="pool_description">Description</label></th>
        <td><?php echo text_area("pool[description]", array('size' => "40x10")) ?></td>
      </tr>
      <tr>
        <td colspan="2"><?php echo submit_tag("Save") ?> <input type="button" onclick="history.back()" value="Cancel"></td>
      </tr>
    </tbody>
  </table>
</form>

<?php render_partial("footer") ?>
