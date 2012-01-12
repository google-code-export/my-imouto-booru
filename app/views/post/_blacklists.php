<div id="blacklisted-sidebar" style="display: none;">
  <h5>
    <a class="no-focus-outline" href="" onclick="$('blacklisted-list-box').toggle(); return false;">Hidden Posts</a>
    <span id="blacklist-count" class="post-count"></span>
  </h5>
  <div id="blacklisted-list-box" style="display: none; margin-bottom: 1em;">
    <ul id="blacklisted-list" style="margin-bottom: 0em;">
      <li>
    </ul>

    <form action="" class="need-signup" id="blacklisted-tag-add" method="post">
      <div>
        &raquo; <input id="add-blacklist" name="add-blacklist" size="20" type="text" value="">
        <a class="text-button" href="" onclick="User.run_login(false, function() { Post.blacklist_add_commit(); }); return false;" style="padding: 0px 4px">Add</a>
        <input name="commit" style="display: none;" type="submit" value="Add">
      </div>
      Posts containing all tags will be hidden. Separate tags with spaces.
    </form>
  </div>

</div>

<?php do_content_for("post_cookie_javascripts") ?>
  <script type="text/javascript">
    document.observe("dom:loaded", function() {
      $("blacklisted-tag-add").observe("submit", function(e) {
        if(e.stopped) return;
        e.stop();
        Post.blacklist_add_commit();
      });
    });
  </script>

<?php end_content_for() ?>

