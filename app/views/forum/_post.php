<div class="comment avatar-container">
  <div class="author">
    <h6 class="author"><?php echo link_to(h($post->author), array("user#show", 'id' => $post->creator_id)) ?></h6>
    <span class="date"><?php echo link_to(time_ago_in_words($post->created_at) . " ago", array("#show", 'id' => $post->id)) ?></span>
    <?php if ($post->creator->has_avatar()) : ?>
      <div class="forum-avatar-container"> <?php echo avatar($post->creator, $post->id) ?> </div>
    <?php endif ?>
  </div>
  <div class="content">
    <?php if ($post->is_parent): ?>
      <h6><?php echo h($post->title) ?></h6>
    <?php ;else: ?>
      <h6 class="response-title"><?php echo h($post->title) ?></h6>
    <?php endif ?>
    <div class="body">
      <?php echo format_inlines(format_text($post->body), $post->id) ?>
    </div>
    <?php if (empty($preview)): ?>
    <div class="post-footer" style="clear: left;">
      <ul class="flat-list pipe-list">
      <?php if (User::$current->has_permission($post, 'creator_id')): ?>
        <li> <?php echo link_to("Edit", array("#edit", 'id' => $post->id)) ?></li>
        <li> <a href="/forum/destroy/<?php echo $post->id ?>" onclick="if (confirm('Are you sure you want to delete this post?')) { var f = document.createElement('form'); f.style.display = 'none'; this.parentNode.appendChild(f); f.method = 'POST'; f.action = this.href;f.submit(); };return false;">Delete</a></li>
      <?php endif ?>
      <?php if ($post->is_parent && User::$current->is('>=40')): ?>
        <?php if ($post->is_sticky): ?>
          <li> <?php echo link_to("Unstick", array("#unstick", 'id' => $post->id), array('method' => 'post')) ?></li>
        <?php ;else: ?>
          <li> <?php echo link_to("Stick", array("#stick", 'id' => $post->id), array('method' => 'post')) ?></li>
        <?php endif ?>
        <?php if ($post->is_locked): ?>
          <li> <?php echo link_to("Unlock", array("#unlock", 'id' => $post->id), array('method' => 'post')) ?></li>
        <?php ;else: ?>
          <li> <?php echo link_to("Lock", array("#lock", 'id' => $post->id), array('method' => 'post')) ?></li>
        <?php endif ?>
      <?php endif ?>
      <li> <?php echo link_to_function("Quote", "Forum.quote({$post->id})") ?></li>
      </ul>
    </div>
    <?php endif ?>
  </div>
</div>
