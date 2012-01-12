<div id="post-list"> 
  <?php if ($tag_suggestions) : ?>
    <div class="status-notice">
      Maybe you meant: <?php //echo @tag_suggestions.map {|x| tag_link(x)}.to_sentence(:connector => "or") ?>
    </div>
  <?php endif ?>

  <div class="sidebar">
    <?php render_partial("search") ?>
    <?php if (User::$current->is('>=20')) : ?>
    <div style="margin-bottom: 1em;" id="mode-box" class="advanced-editing">
      <h5>Mode</h5>
      <form onsubmit="return false;" action="">
        <div>
          <select name="mode" id="mode" onchange="PostModeMenu.change()" onkeyup="PostModeMenu.change()" style="width: 13em;">
            <option value="view">View posts</option>
            <option value="edit">Edit posts</option>
<!--        <option value="rating-s">Rate safe</option>
            <option value="rating-q">Rate questionable</option>
            <option value="rating-e">Rate explicit</option>

            <option value="lock-rating">Lock rating</option>
            <option value="lock-note">Lock notes</option>
             -->
            <?php if (User::$current->is('>=40')) : ?>
              <option value="approve">Approve post</option>
            <?php endif ?>
            <option value="flag">Flag post</option>
            <option value="apply-tag-script">Apply tag script</option>
            <option value="reparent-quick">Reparent posts</option>
            <?php if ($searching_pool) : ?>
              <option value="remove-from-pool">Remove from pool</option>
            <?php endif ?>
          </select>          
        </div>
      </form>
    </div>

    <?php render_partial("tag_script") ?>
    <?php endif ?>

    <?php if ($searching_pool) : ?>
      Viewing pool <?php echo link_to(h($searching_pool->pretty_name()), array('pool#show', 'id' => $searching_pool->id)) ?>.
    <?php endif ?>

    <?php if ($showing_holds_only) : ?>
      <?php if ($posts) : ?>
        <div style="margin-bottom: .5em;">
          <div><a href="#" onclick="Post.activate_all_posts(); return false;">&raquo; Activate all held posts</a></div>
        </div>
      <?php endif ?>
    <?php ;else: ?>
      <div id="held-posts" style="display: none; margin-bottom: .5em;">You have held <span id="held-posts-count"></span> (<a href="#">view</a>).</div>
    <?php endif ?>

    <?php render_partial('blacklists') ?>
    <?php render_partial('tags', array('include_tag_hover_highlight' => true)) ?>
    <?php if (CONFIG::can_see_ads(User::$current)) : ?>
    <?php //render_partial('vertical') ?>
    <?php endif ?>

  </div>
  <div class="content">    
    <?php if ($ambiguous_tags) : ?>
      <div class="status-notice">
        The following tags are ambiguous: <?php array_map(function($x){echo link_to(h($x), 'wiki#show', array('title' => $x));}, $ambiguous_tags) ?>
      </div>
    <?php endif ?>

    <div id="quick-edit" style="display: none;" class="top-corner-float">
      <form action="/post/update" method="post">
        <textarea cols="60" id="post_tags" name="post[tags]" rows="2"></textarea>
        <input name="commit" type="submit" value="Update">
        <input class="cancel" type="button" value="Cancel">
        <h4 style="float: right;">Edit Tags</h4>
      </form>
    </div>

    <?php render_partial('hover') ?>
    <?php render_partial('posts', array('posts' => $posts)) ?>
    <div id="paginator">
      <?php paginator() ?>
    </div>
  </div>
</div>

<?php do_content_for('post_cookie_javascripts') ?>
<script type="text/javascript">
  post_quick_edit = new PostQuickEdit($("quick-edit"));

  PostModeMenu.init(<?php $searching_pool && print $searching_pool->id ?>)
<?php /*
  <php @preload.each do |post| >
  Preload.preload('<?php echo escape_javascript(post.preview_url) >');
  <php end >
*/ ?>
  var held_posts = Cookie.get("held_post_count");
  if(held_posts && held_posts > 0)
  {
    var e = $("held-posts");
    if(e)
    {
      var a = e.down("A");
      var cnt = e.down("#held-posts-count");
      cnt.update("" + held_posts + " " + (held_posts == 1? "post":"posts"));
      a.href = "/post/index?tags=holds%3Aonly%20user%3A" + Cookie.get("login") + "%20limit%3A100"
      e.show();
    }
  }
  Post.cache_sample_urls();
  new TagCompletionBox($("post_tags"));
  if($("tag-script"))
    new TagCompletionBox($("tag-script"));
</script>
<?php end_content_for() ?>

<?php do_content_for('html_header') ?>
  <?php //echo auto_discovery_link_tag_with_id :rss, {:controller => "post", :action => "piclens", :tags => params[:tags], :page => params[:page]}, {:id => 'pl'} ?>
  <?php //echo navigation_links(@posts) ?>
<?php end_content_for() ?>

<?php render_partial('footer') ?>

<?php if (check_content_for('subnavbar')) : ?>
  <!-- Align the links to the content, not the window. -->
  <div style="clear: both;">
    <div class="sidebar">&nbsp;</div>
    <div class="footer" style="clear: none;">
      <ul class="flat-list" id="subnavbar">
        <?php content_for('subnavbar') ?>
      </ul>
    </div>
  </div>
  <?php empty_content_for('subnavbar') ?>
<?php endif ?>

