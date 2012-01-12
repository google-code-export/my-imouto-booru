<h3>Edit Pool</h3>

<?php echo form_tag('#update') ?>
  <table class="form">
    <tbody>
      <tr>
        <th width="15%"><label for="pool_name">Name</label></th>
        <td width="85%"><?php echo text_field_tag('pool->name', $pool->pretty_name()) ?></td>
      </tr>
      <tr>
        <th><label for="pool_description">Description</label></th>
        <td><textarea cols="40" id="pool_description" name="pool[description]" rows="10"><?php echo $pool->description ?></textarea>
      </tr>
      <tr>
        <th>
          <label for="pool_is_public">Public</label>
          <p>Public pools allow anyone to add/remove posts.</p>
        </th>
        <td><?php echo checkbox_tag('pool->is_public') ?></td>
      </tr>
      <tr>
        <th>
          <label for="pool_is_active">Active</label>
          <p>Inactive pools will no longer be selectable when adding a post.</p>
        </th>
        <td><?php echo checkbox_tag('pool->is_active', $pool->is_active) ?></td>
      </tr>
      <tr>
        <td colspan="2"><input name="commit" type="submit" value="Save"> <input onclick="history.back();" type="button" value="Cancel"></td>
      </tr>
    </tbody>
  </table>
</form>

<?php render_partial('footer') ?>
