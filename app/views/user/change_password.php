<div id="user-edit">
  <?php echo form_tag("#update") ?>
    <table>
      <tbody>
        <tr>
          <th><label>New Password</label></th>
          <td><input type="password" name="user[password]" id="user_password" /></td>
        </tr>
        <tr>
          <th><label>Confirm Password</label></th>
          <td><input type="password" name="user[password_confirmation]" id="user_password_confirmation" /></td>
        </tr>
        <tr>
          <td><?php echo submit_tag("Save") ?> <?php echo submit_tag("Cancel") ?></td>
        </tr>
      </tbody>
    </table>
  </form>
</div>

<?php render_partial("footer") ?>
