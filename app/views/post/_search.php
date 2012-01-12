        <div style="margin-bottom: 1em;">
          <h5>Search</h5>
          <form action="/post" method="get">
            <div>
              <input id="tags" name="tags" size="20" type="text" value="<?php echo isset(Request::$params->tags)?h(Request::$params->tags) : null ?>">
              <input style="display: none;" type="submit" value="Search">
            </div>
          </form>
        </div>
        <script type="text/javascript">
          new TagCompletionBox($("tags"));
          if(TagCompletion)
            TagCompletion.observe_tag_changes_on_submit($("tags").up("form"), $("tags"), null);
        </script>
