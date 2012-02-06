<div id="comment-list">
  <?php if (!$posts) : ?>
    <h4>No comments.</h4>
  <?php endif ?>

  <?php foreach ($posts as $post) : ?>
    <div class="post">
      <div class="col1">
        <?php echo link_to('<img src="'.$post->preview_url().'" title="'.$post->tags.'" class="preview javascript-hide" id="p'.$post->id.'" style="width:'.$post->preview_dimensions('w').'px;height:'.$post->preview_dimensions('h').'px" />', array('post#show', 'id' => $post->id)) ?>&nbsp;
      </div>
      <div class="col2" id="comments-for-p<?php echo $post->id ?>">
        <div class="header">
          <div>
            <span class="info"><strong>Date</strong> <?php echo compact_time($post->created_at) ?></span>
            <span class="info"><strong>User</strong> <?php echo link_to(h($post->author), array('user#show', 'id' => $post->user_id)) ?></span>
            <span class="info"><strong>Rating</strong> <?php echo $post->pretty_rating() ?></span>
            <span class="info vote-container"><strong>Score</strong>
              <span id="post-score-<?php echo $post->id ?>"><?php echo $post->score ?></span>
              <?php echo vote_widget(User::$current) ?>
              <?php echo vote_tooltip_widget() ?>
            </span>

            <?php if (count($post->comments) > 6) : ?>
              <span class="info"><strong>Hidden</strong> <?php echo link_to((count($post->comments) - 6), array('post#show', 'id' => $post->id)) ?></span>
            <?php endif ?>
          </div>
          <div class="tags">
            <strong>Tags</strong>
            <?php foreach($post->parsed_cached_tags as $name => $type) : ?>
              <span class="tag-type-<?php echo $type ?>">
                <a href="/post/index?tags=<?php echo u($name) ?>"><?php echo h(str_replace('_', ' ', $name)) ?></a>
              </span>
            <?php endforeach ?>
          </div>
        </div>
        <?php render_partial("comments", array('comments' => $post->recent_comments(), 'post_id' => $post->id, 'hide' => true, 'multipost' => true)) ?>          
      </div>
    </div>
  <?php endforeach ?>

  <div id="paginator">
    <?php paginator() ?>
  </div>

  <?php if (2 === 3) : //if @page_uses_translations then ?>
    <?php //content_for("above_footer") do ?>
      Comment translation provided by <a href="http://translate.google.com">Google</a>.
      <br>
    <?php //end ?>
  <?php endif ?>

  <script type="text/javascript">
    <?php echo avatar_init() ?>
    
    InlineImage.init();
    
    Post.register_resp(<?php echo to_json(Post::batch_api_data($posts)) ?>);
    <?php foreach ($posts as $post) : ?>
      var container = $("comments-for-p<?php echo $post->id ?>").down(".vote-container");
      var widget = new VoteWidget(container);
      widget.set_post_id(<?php echo $post->id ?>);
    <?php endforeach ?>
    Post.init_blacklisted({replace: true})
  </script>

  <?php render_partial("footer") ?>
</div>
