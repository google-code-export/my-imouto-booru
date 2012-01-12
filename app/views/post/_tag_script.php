    <div id="edit-tag-script" style="display: none;" class="top-corner-float">
      <h5>Tag script</h5>
      <form onsubmit="return false;" action="">
        <input id="tag-script" name="tag-script" size="40" type="text" value="">
      </form>
      <div style="margin-top: 0.25em;">
        <a href="" onclick="PostModeMenu.apply_tag_script_to_all_posts(); return false;">&raquo; Apply tag script to all of these posts</a>
      </div>
    </div>
<?php do_content_for("post_cookie_javascripts") ?>
  <script type="text/javascript">
    TagScript.init($("tag-script"));
  </script>

<?php end_content_for() ?>