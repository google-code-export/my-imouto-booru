<div id="pool-show">
  <h4>Pool: <?php echo h($pool->pretty_name()) ?></h4>
  <?php if ($pool->description) : ?>
    <div style="margin-bottom: 2em;"><?php echo h($pool->description) ?></div>
  <?php endif ?>
  <div style="margin-top: 1em;">
  <ul id="post-list-posts">
    <?php foreach ($posts as &$post) : ?>
      <?php echo print_preview($post, array('onclick' => "return remove_post_confirm(".$post->id.", ".$pool->id.")",
                             'user' => User::$current, 'display' => $browse_mode ? 'large' : 'block', 'hide_directlink' => $browse_mode)) ?>
    <?php endforeach ?>
  </ul>
  </div>
</div>
<script type="text/javascript">
  function remove_post_confirm(post_id, pool_id) {
    if (!$("del-mode") || !$("del-mode").checked) {
      return true
    }

    Pool.remove_post(post_id, pool_id)
    return false
  }

  Post.register_resp(<?php echo to_json(Post::batch_api_data($posts)) ?>);
</script>
<?php echo render_partial('post/hover') ?>

<div id="paginator">
  <?php paginator() //echo will_paginate(@posts, :class => "no-browser-link") ?>

  <div style="display: none;" id="info">When delete mode is enabled, clicking on a thumbnail will remove the post from this pool.</div>
</div>
<?php do_content_for("footer") ?>
  <?php if (CONFIG::pool_zips) : ?>
    <?php $zip_params = array() ?>
    <?php $has_jpeg = (CONFIG::jpeg_enable && $pool->has_jpeg_zip($zip_params)) ?>
    <?php if ($has_jpeg) : ?>
      <li><?php //echo link_to_pool_zip "Download&nbsp;JPGs", @pool, zip_params.merge({:jpeg => true}) ?></li>
    <?php endif ?>
    <?php $li_class = $has_jpeg ? "advanced-editing":"" ?>
    <li class="<?php echo $li_class ?>"><?php //echo link_to_pool_zip "Download", @pool, zip_params, {:has_jpeg => has_jpeg} ?></li>
  <?php endif ?>
  <li><?php echo link_to("Index view", array('post#index', 'tags' => 'pool:'.$pool->id)) ?> </li>
  <?php if (!User::$current->is_anonymous) : ?>
  <li><a href="#" onclick="User.set_pool_browse_mode(<?php echo $browse_mode ? 0 : 1 ?>); return false;">Toggle view</a></li>
  <?php endif ?>
  <?php if (User::$current->has_permission($pool)) : ?>
    <li><?php echo link_to("Edit", array("#update", 'id' => Request::$params->id)) ?></li>
    <li><?php echo link_to("Delete", array("#destroy", 'id' => Request::$params->id)) ?></li>
  <?php endif ?>
<?php end_content_for() ?>

<?php do_content_for("footer_final") ?>
  <br />
  <?php if (User::$current->can_change($pool, 'posts')) : ?>
    <li><?php echo link_to("Order", array("#order", 'id' => Request::$params->id)) ?></li>
    <?php // <li>< echo link_to("Import", array("#import", 'id' => Request::$params->id)) ></li> ?>
    <?php if (User::is('>=33')) : ?>
      <li><?php echo link_to("Copy", array("#copy", 'id' => Request::$params->id)) ?></li>
      <li><?php echo link_to("Transfer&nbsp;post&nbsp;data", array("#transfer_metadata", 'to' => Request::$params->id)) ?></li>
    <?php endif ?>
  <?php endif ?>
  <li><?php echo link_to("History", array('history#index', 'search' => 'pool:'.Request::$params->id)) ?></li>
  <?php if (User::$current->can_change($pool, 'posts')) : ?>
  <li class="advanced-editing del-mode">
    <input type="checkbox" id="del-mode" onclick="Element.toggle('info')">
    <label for="del-mode">Delete&nbsp;mode</label>
  </li>
  <?php endif ?>
<?php end_content_for() ?>

<?php render_partial('footer') ?>
