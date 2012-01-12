    <div style="display: none;" class="post-hover-overlay" id="index-hover-overlay">
      <a href=""><img src="about:blank" alt=""></a>
    </div>

    <div style="display: none;" class="post-hover" id="index-hover-info">
      <div id="hover-top-line">
        <div style="float: right; margin-left: 0em;">
          <span id="hover-dimensions"></span>,
          <span id="hover-file-size"></span>
        </div>
        <div style="padding-right: 1em">
          Post #<span id="hover-post-id"></span>
        </div>
      </div>

      <div style="padding-bottom: 0.5em">
        <div style="float: right; margin-left: 0em;">
          <span id="hover-author"></span>
        </div>
        <div style="padding-right: 1em">
          Score: <span id="hover-score"></span>
          Rating: <span id="hover-rating"></span>
          <span id="hover-is-parent">Parent</span>
          <span id="hover-is-child">Child</span>
          <span id="hover-is-pending">Pending</span>
          <span id="hover-not-shown">Hidden</span>
        </div>
        <div>
          <span id="hover-is-flagged"><span class="flagged-text">Flagged</span> by <span id="hover-flagged-by"></span>: <span id="hover-flagged-reason">gar</span></span>
        </div>
      </div>
      <div>
        <span id="hover-tags">
          <span class="tag-type-artist"><a id="hover-tag-artist"></a></span>
          <span class="tag-type-circle"><a id="hover-tag-circle"></a></span>
          <span class="tag-type-copyright"><a id="hover-tag-copyright"></a></span>
          <span class="tag-type-character"><a id="hover-tag-character"></a></span>
          <span class="tag-type-general"><a id="hover-tag-general"></a></span>
          <span class="tag-type-faults"><a id="hover-tag-faults"></a></span>
          <span class="tag-type-custom"><a id="hover-tag-custom"></a></span>
        </span>
      </div>
    </div>

<?php do_content_for("post_cookie_javascripts") ?>
  <script type="text/javascript">Post.hover_info_init();</script>
<?php end_content_for(true) ?>
