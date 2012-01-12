<div style="width: 40em;">
<h4>Signup</h4>

<?php if (!CONFIG::enable_signups) : ?>
  <p>Signups are currently disabled.</p>
<?php return; endif ?>
  <p>By creating an account, you are agreeing to the <a href="/static/terms_of_service">terms of service</a>. <strong>Remember that this site is open to web crawlers, so people will be able to easily search your name.</strong></p>
  
  <?php echo form_tag("user#create") ?>
    <table class="form">
      <tfoot>
        <tr>
          <td colspan="2">
            <input type="submit" value="Signup">
          </td>
        </tr>
      </tfoot>
      <tbody>
        <tr>
          <th width="15%">
            <label class="block" for="user_name">Name</label>
            <p>Please remember your name will be easy to Google on this site.</p>
          </th>
          <td width="85%">
            <?php echo text_field_tag("user[name]", array('size' => 30)) ?>
          </td>
        </tr>
        <tr>
          <th>
            <label class="block" for="user_email">Email</label>
            <p>Optional, for email notifications and password resets.</p>
          </th>
          <td>
            <?php echo text_field_tag("user[email]", array('size' => 30)) ?>
          </td>
        </tr>
        <tr>
          <th>
            <label class="block" for="user_password">Password</label>
            <p>Minimum of five characters.</p>
          </th>
          <td>
            <input type="password" size="30" name="user[password]" id="user_password" />
          </td>
        </tr>
        <tr>
          <th>
            <label class="block" for="user_password_confirmation">Confirm password</label>
          </th>
          <td>
            <input type="password" size="30" name="user[password_confirmation]" id="user_password_confirmation" />
          </td>
        </tr>
      </tbody>
    </table>
  </form>
</div>
<?php render_partial("footer") ?>
