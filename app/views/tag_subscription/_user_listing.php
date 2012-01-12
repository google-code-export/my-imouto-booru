<?php if (empty($user->tag_subscriptions)) : ?>
  None
<?php ;else: ?>
  <?php echo tag_subscription_listing($user) ?>
<?php endif ?>
  
<?php if (User::$current->id == $user->id) : ?>
  (<a href="/tag_subscription">edit</a>)
<?php endif ?>
