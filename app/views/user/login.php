<div id="user-login" class="page">
  <h4>Login</h4>
  <?php if (User::$current->is_unactivated) : ?>
    <p>You have not yet activated your account. Click <?php echo link_to("here", "#resend_confirmation") ?> to resend your confirmation email to <?php echo h(User::$current->email) ?>.</p>
  <?php ;else: ?>
    <p>
      You need an account to access some parts of <?php echo h(CONFIG::app_name) ?>. 
      <?php if (!User::$current->is_anonymous) : ?>
        Click <?php echo link_to("here", "#reset_password") ?> to reset your password.
      <?php endif ?>
      <?php if (User::$current->is_anonymous) : ?>
        <?php if (CONFIG::enable_signups) : ?>
          You can register for an account <?php echo link_to("here", "#signup") ?>.
        <?php ;else: ?>
          Registration is currently disabled.
        <?php endif ?>
      <?php endif ?>
    </p>
  <?php endif ?>
  
  <?php echo form_tag("#authenticate") ?>
    <?php echo hidden_field_tag("url", Request::$params->url) ?>
    <table class="form">
      <tr>
        <th width="15%"><label class="block" for="user_name">Name</label></th>
        <td width="85%"><?php echo text_field_tag("user->name", array('tabindex' => 1)) ?></td>
      </tr>
      <tr>
        <th><label class="block" for="user_password">Password</label></th>
        <td><input type="password" id="user_password" name="user[password]" tabindex="1" /></td>
      </tr>
      <tr>
        <td colspan="2"><?php echo submit_tag("Login", array('tabindex' => 1)) ?></td>
      </tr>
    </table>
  </form>    
</div>

<?php render_partial("footer") ?>