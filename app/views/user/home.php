<div id="user-index">
  <?php if (User::$current->is_anonymous) : ?>
    <h2>You are not logged in.</h2>

    <ul class="link-page">
      <li><?php echo link_to("&raquo; Login", "#login") ?></li>
      <?php if (CONFIG::enable_signups) : ?>
        <li><?php echo link_to("&raquo; Sign Up", "#signup") ?></li>
      <?php ;else: ?>
        <li>Signups are disabled</li>
      <?php endif ?>
      <li><?php echo link_to("&raquo; Reset Password", "#reset_password") ?></li>
    </ul>
  <?php ;else: ?>
    <h2>Hello <?php echo h(User::$current->name) ?>!</h2>
    <p>From here you can access account-specific options and features.</p>

    <div class="section">
      <ul class="link-page">
        <li><?php echo link_to("&raquo; Logout", "#logout") ?></li>
        <li><?php echo link_to("&raquo; My Profile", array("#show", 'id' => User::$current->id)) ?></li>
        <li><?php echo link_to("&raquo; My Mail", "dmail#inbox") ?></li>
        <li><?php echo link_to("&raquo; My Favorites", array("post#index", 'tags' => "vote:3:".User::$current->name." order:vote")) ?></li>
        <li><?php echo link_to("&raquo; Settings", "#edit") ?></li>
        <li><?php echo link_to("&raquo; Change Password", "#change_password" )?></li>
      </ul>      
    </div>

    <?php if (User::is('>=33')) : ?>
      <div>
        <h4>Moderator Tools</h4>
        <ul class="link-page">
          <li><?php echo link_to("&raquo; Invites", "#invites") ?></li>
          <?php if (User::is('>=40')) : ?>
            <li><?php echo link_to("&raquo; Blocked Users", "#show_blocked_users") ?></li>
          <?php endif ?>
        </ul>
      </div>
    <?php endif ?>
  <?php endif ?>
</div>

<?php render_partial("footer") ?>