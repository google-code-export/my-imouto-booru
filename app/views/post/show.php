    <div id="post-view">
<?php if(empty($post->id)) : ?>
      <h2>Nobody here but us chickens!</h2>
<?php ;else: ?>
<?php if($post->can_be_seen_by()) : ?>
      <script type="text/javascript"> Post.register_resp(<?php echo to_json(Post::batch_api_data(array($post))) ?>); </script>
<?php endif ?>

<?php render_partial("post/show_partials/status_notices", 'pools') ?>
      <div class="sidebar">
<?php render_partial("search") ?>
<?php render_partial("tags") ?>
<?php render_partial("post/show_partials/statistics_panel") ?>
<?php render_partial("post/show_partials/options_panel") ?>
<?php render_partial("post/show_partials/related_posts_panel") ?>

      </div>
      <div class="content" id="right-col">
      
<?php render_partial("post/show_partials/image") ?>
<?php render_partial("post/show_partials/image_footer") ?>
<?php render_partial("post/show_partials/edit") ?>
<?php render_partial("post/show_partials/comments") ?>

<?php endif ?>
      </div>
      


<?php do_content_for("post_cookie_javascripts") ?>
      <script type="text/javascript">
        Post.observe_text_area("post_tags")

        RelatedTags.init(Cookie.get('my_tags'), '<?php !empty(Request::$params->url) && print Request::$params->url ?>')

        if (Cookie.get('resize_image') == '1') {
          Post.resize_image()
        }

        var anchored_to_comment = window.location.hash == "#comments";
        if(window.location.hash.match(/^#c[0-9]+$/))
          anchored_to_comment = true;

        if (Cookie.get('show_defaults_to_edit') == '1' && !anchored_to_comment) {
          $('comments').hide();
          $('edit').show();
        }

<?php //browser_url = "/post/browse##{@post.id}" ?>
<?php //browser_url += "/pool:#{@following_pool_post.pool_id}" if not @following_pool_post.nil? ?>
        OnKey(66, { AlwaysAllowOpera: true }, function(e) { window.location.href = '<?php //browser_url.to_json ?>'; });
      </script>
<?php end_content_for() ?>
    </div>

<?php /* if CONFIG["app_name"] == "oreno.imouto" >
< render :partial => "referral" >
< end */ ?>

<script type="text/javascript">
  new TagCompletionBox($("post_tags"));
  if(TagCompletion)
    TagCompletion.observe_tag_changes_on_submit($("edit-form"), $("post_tags"), $("post_old_tags"));

  <?php if (CONFIG::app_name == "oreno.imouto") : ?>
    referral_banner = new ReferralBanner($("hosting-referral"));
    referral_banner.increment_views_and_check_referral();
  <?php endif ?>
</script>

<?php render_partial("footer") ?>