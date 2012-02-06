      <div>
<?php if (!$posts) { ?>
        <p>Nobody here but us chickens!</p>
<?php } else { ?>
        <ul id="post-list-posts">
<?php foreach($posts as $post) : ?>
      <?php echo print_preview($post, array('blacklisting' => true)) ?>
<?php endforeach ?>
        </ul>

<?php # Make sure this is done early, as lots of other scripts depend on this registration. ?>
<?php do_content_for("post_cookie_javascripts")?>

        <script type="text/javascript">
        
          Post.register_tags(<?php echo to_json(Tag::batch_get_tag_types_for_posts($posts)) ?>)

<?php foreach($posts as $post) : ?>
          Post.register(<?php echo $post->to_json() ?>)
      
<?php endforeach ?>
        </script>
<?php end_content_for(true) ?>

<?php } ?>

<?php do_content_for("post_cookie_javascripts") ?>
  <script type="text/javascript">
    var blacklist_options = {};
    <?php if (!empty($search_id)) : ?>
      blacklist_options.exclude = <?php to_json($search_id) ?>;
    <?php endif ?>
    Post.init_blacklisted(blacklist_options)

    Post.init_post_list();
  </script>

<?php end_content_for() ?>
      </div>