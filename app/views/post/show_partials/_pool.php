<div class="status-notice" id="pool<?php echo $pool->id ?>">
  <div style="display: inline;">
    <p>
      <?php if ($pool_post->prev_post_id) : ?>
        <?php echo link_to("&laquo; Previous", array("#show", 'id' => $pool_post->prev_post_id, 'pool_id' => $pool_post->pool_id)) ?>
      <?php endif ?>
      <?php if ($pool_post->next_post_id) : ?>
        <?php echo link_to("Next &raquo;", array("#show", 'id' => $pool_post->next_post_id, 'pool_id' => $pool_post->pool_id)) ?>
      <?php endif ?>
      This post is <span id="pool-seq-<?php echo $pool_post->pool_id ?>"><?php echo h($pool_post->pretty_sequence()) ?></span>
      in the <?php echo link_to(h($pool->pretty_name()), array("pool#show", 'id' => $pool->id)) ?> pool
      <?php $pooled_post_id = $post->id ?>

    <?php if (User::$current->can_change($pool_post, 'active')) : ?>
    <span class="advanced-editing">
    (<a href="" onclick="if(confirm('Are you sure you want to remove this post from <?php echo addslashes($pool->pretty_name()) ?>?')) Pool.remove_post(<?php echo $post->id ?>, <?php echo $pool->id ?>); return false;">remove</a>,
    <a href="" onclick="Pool.change_sequence(<?php echo $post->id ?>, <?php echo $pool_post->pool_id ?>, &quot;<?php echo $pool_post->sequence ?>&quot;); return false;">change page</a>
    <?php if ($post->parent_id) : ?>,
    <a href="" onclick="if(confirm('Are you sure you want to remove this post from <?php echo addslashes($pool->pretty_name()) ?> and add the parent post instead?')) Pool.transfer_post(<?php echo $post->id ?>, <?php echo $post->parent_id ?>, <?php echo $pool->id ?>, &quot;<?php echo $pool_post->sequence ?>&quot;">transfer to parent</a>
    <?php endif ?>)
    </span>
    <?php endif ?>
    </p>
  </div>
</div>


    
