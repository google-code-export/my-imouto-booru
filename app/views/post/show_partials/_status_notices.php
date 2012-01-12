<?php if ($post->is_flagged()): ?>
  <div class="status-notice">
    This post was flagged for deletion by <?php echo h($post->flag_detail->flagged_by()) ?>. Reason: <?php echo h($post->flag_detail->reason) ?>
    <?php if(User::$current->is('>=40') || ($post->flag_detail && $post->flag_detail->user_id == User::$current->id)) : ?>
    (<a href="#" onclick="Post.unflag(<?php echo $post->id ?>, function() { window.location.reload(); });">unflag this post</a>)
    <?php endif ?>
  </div>
<?php ;elseif ($post->is_pending()): ?>
  <div class="status-notice" id="pending-notice">
    This post is pending moderator approval.
  </div>
<?php ;elseif ($post->is_deleted()): ?>
  <div class="status-notice">
    This post was deleted. 
    <?php if($post->flag_detail) : ?>
      <?php if(User::$current->is('>=40')) : ?>
        By: <a href="/user/show?id=<?php echo $post->flag_detail->user_id ?>"><?php echo $post->flag_detail->flagged_by() ?></a>
      <?php endif ?>

      Reason: <?php echo h($post->flag_detail->reason) ?>. MD5: <?php echo $post->md5 ?>
    <?php endif ?>
  </div>
<?php endif ?>

<?php if($post->is_held): ?>
  <div class="status-notice" id="held-notice">
    This post has been temporarily held from the index by the poster.
    <?php if(User::$current->has_permission($post)): ?>
      (<a href="#" onclick="Post.activate_post(<?php echo $post->id ?>);return false;">activate this post</a>)
    <?php endif ?>
  </div>
<?php endif ?>

<?php if(!$post->is_deleted() && $post->use_sample() && $post->can_be_seen_by(User::$current)): ?>
  <div class="status-notice" style="display: none;" id="resized_notice">
    This image has been resized. Click on the <a href="#" onclick="Post.highres(); return false;">View larger version</a> link in the sidebar for a high-quality version.
    <!--
    <?php if(!User::$current->is_anonymous || CONFIG::force_image_samples): ?>
      <a href="#" onclick="User.disable_samples(); return false;">Always view original</a>.
    <?php endif ?>
    -->
    <a href="#" onclick="$('resized_notice').hide(); Cookie.put('hide_resized_notice', '1'); return false;">Hide this message</a>.
    <script type="text/javascript">
      if (Cookie.get("hide_resized_notice") != "1") {
        $("resized_notice").show()
      }
    </script>
  </div>
<?php /*
  <div class="status-notice" style="display: none;" id="samples_disabled">
    Image samples have been disabled. If you find this to be too slow, you can turn samples back on in your profile settings.
  </div>
*/ ?>
<?php endif ?>

<?php if(CONFIG::enable_parent_posts): ?>
  <?php if($post->parent_id): ?>
    <div class="status-notice">
      This post belongs to a <a href="/post/show/<?php echo $post->parent_id ?>">parent post</a>
      <span class="advanced-editing"> (<a href="#" onclick="Post.reparent_post(<?php echo $post->id ?>, <?php echo $post->parent_id ?>, <?php echo $post->get_parent()->parent_id ? 'true' : 'false' ?>); return false;">make this post the parent</a>)</span>.
    </div>
  <?php endif ?>

  <?php if($post->has_children): ?>
    <?php $children = &$post->children ?>
    <div class="status-notice">
      This post has <?php echo link_to((count($children) == 1? "a child post":"child posts"), array('#index', 'tags' => 'parent:'.$post->id)) ?> (post <?php echo implode(', ', array_map(function(&$child){return link_to($child->id, '#show', array('id' => $child->id));}, (array)$children)) ?>).
    </div>
  <?php endif ?>
<?php endif ?>

<?php

foreach($pools as $k => &$pool):
  global $pool;
  $pool = $pools->$k;
  $pp = PoolPost::$_->find('first', array('conditions' => array("pool_id = ? AND post_id = ?", $pool->id, $post->id)));
?>
  <?php render_partial("post/show_partials/pool", array('pool', 'pool_post' => $pp)) ?>
<?php endforeach;// endif ?>
