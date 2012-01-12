      <div id="stats">
        <h5>Statistics</h5>
        <ul>
          <li>Id: <?php echo $post->id ?></li>
          <li>Posted: <a href="/post?tags=date%3A<?php echo date('Y-m-d', $post->api_attributes['created_at']) ?>"><?php echo time_ago_in_words(date('Y-m-d H:i:s', $post->api_attributes['created_at'])) ?> ago</a> by <a href="/user/profile/<?php echo $post->creator_id ?>"><?php echo $post->author ?></a></li>
          <li>Size: <?php echo $post->width , 'x' , $post->height; ?></li>
<?php if ($post->source) : ?>
          <li>Source: <?php echo propper_source($post) ?></li>
<?php endif ?>
          <li>Rating: <?php echo $post->pretty_rating() ?>
          <span class="vote-desc" id="vote-desc-<?php echo $post->id ?>"></span>
          </li>	
          <li>
            Score: <span id="post-score-<?php echo $post->id ?>"><?php echo $post->score ?></span>
            <?php echo vote_widget() ?>
          </li>
          <li>Favorited by: <span id="favorited-by"><?php echo favorite_list($post) ?></span></li>
        </ul>
      </div>

<?php do_content_for("post_cookie_javascripts") ?>
<script type="text/javascript">
  var widget = new VoteWidget($("stats"));
  widget.set_post_id(<?php echo $post->id ?>);
  widget.init_hotkeys();

  Post.init_add_to_favs(<?php echo $post->id ?>, $("add-to-favs"), $("remove-from-favs"));
</script>
<?php end_content_for() ?>
