<div id="post-add">
  <div id="static_notice" style="display: none;"></div>

  <?php if ($deleted_posts) : ?>
    <div id="posts-deleted-notice" class="has-deleted-posts" style="margin-bottom: 1em;">
      <?php echo $deleted_posts == 1? "One":"Some" ?> of your posts <?php echo $deleted_posts == 1? "was":"were" ?>
      <a href="<?php echo url_for('#deleted_index', array('user_id' => User::$current->id)) ?>">recently deleted</a>.
      (<a href="#" onclick="Post.acknowledge_new_deleted_posts();">dismiss this message</a>)
    </div>
  <?php endif ?>

  <?php if (!User::$current->is('>=20')) : ?>
    <div style="margin-bottom: 2em;">
      <h4>Upload Guidelines</h4>
      <p>Please keep the following guidelines in mind when uploading something. Consistently violating these rules will result in a ban.</p>
      <ul>
        <li>Do not upload <?php echo link_to('furry', array('wiki#show', 'title' => 'furry')) ?>, <?php echo link_to('yaoi', array('wiki#show', 'title' => 'yaoi')) ?>, <?php echo link_to('guro', array('wiki#show', 'title' => 'guro')) ?>, <?php echo link_to('toon', array('wiki#show', 'title' => 'toon')) ?>, or <?php echo link_to('poorly drawn', array('wiki#show', 'title' => 'poorly_drawn')) ?> art</li>
        <li>Do not upload things with <?php echo link_to('compression artifacts', array('wiki#show', 'title' => 'compression_artifacts')) ?></li>
        <li>Do not upload things with <?php echo link_to('obnoxious watermarks', array('wiki#show', 'title' => 'watermark')) ?></li>
        <li><?php echo link_to('Group doujinshi, manga pages, and similar game CGs together', 'help#post_relationships') ?></li>
        <li>Read the <?php echo link_to('tagging guidelines', 'help#tags') ?></li>
      </ul>
      <p>You can only upload <?php //echo pluralize CONFIG["member_post_limit"] - Post.count(:conditions => ["user_id = ? AND created_at > ?", @current_user.id, 1.day.ago]), "post" ?> today.</p>
    </div>
  <?php endif ?>
  
  <?php echo form_tag('post#create', array('level' => 'member', 'multipart' => true, 'id' => "edit-form")) ?>
    <div id="posts">
      <?php if (Request::$params->url) : ?>
        <img src="<?php echo Request::$params->url ?>" title="Preview" id="image" />
        <p id="scale"></p>
        <script type="text/javascript">
        document.observe("dom:loaded", function() {
          if ($("image").height > 400) {
            var width = $("image").width
            var height = $("image").height
            var ratio = 400.0 / height
            $("image").width = width * ratio
            $("image").height = height * ratio
            $("scale").innerHTML = "Scaled " + parseInt(100 * ratio) + "%"
          }
        })
        </script>
      <?php endif ?>

      <table class="form">
        <tfoot>
          <tr>
            <td></td>
            <td>
              <?php echo submit_tag('Upload', array('tabindex' => 8, 'accesskey' => "s", 'class' => 'submit', 'style' => 'margin: 0;')) ?>
              <?php echo submit_tag('Cancel', array('tabindex' => 8, 'accesskey' => "s", 'class' => 'cancel', 'style' => 'display: none; vertical-align: bottom; margin: 0;')) ?>
              <div id="progress" class="upload-progress-bar" style="display: none;">
                <div class="upload-progress-bar-fill"></div>
              </div>
              <span style="display: none;" id="post-exists">This post already exists: <a href="#" id="post-exists-link"></a></span>
              <span style="display: none;" id="post-upload-error"></span>
            </td>
          </tr>
        </tfoot>
        <tbody>
          <tr>
            <th width="15%"><label for="post_file">File</label></th>
            <td width="85%"><input id="post_file" name="post[file]" size="50" tabindex="1" type="file" /><span class="similar-results" style="display: none;"></span></td>
          </tr>
          <tr>
            <th>
              <label for="post_source">Source</label>
              <?php if (User::$current->is('<20')) : ?>
                <p>You can enter a URL here to download from a website.</p>
              <?php endif ?>
            </th>
            <td>
              <?php echo text_field_tag('post->source', array('value' => Request::$params->url, 'size' => 50, 'tabindex' => 2)) ?>
              <?php if (CONFIG::enable_artists) : ?>
                <a href="#" onclick="RelatedTags.find_artist($F('post_source')); return false;">Find artist</a>
              <?php endif ?>
            </td>
          </tr>
          <tr>
            <th>
              <label for="post_tags">Tags</label>
              <?php if (User::$current->is('<20')) : ?>
                <p>Separate tags with spaces. (<?php echo link_to('help', 'help#tags', array('target' => '_blank')) ?>)</p>
              <?php endif ?>
            </th>
            <td>
              <?php echo text_area('post->tags', Request::$params->tags, array('size' => "60x2", 'tabindex' => 3)) ?>
              <a href="#" onclick="RelatedTags.find('post_tags'); return false;">Related tags</a> | 
              <a href="#" onclick="RelatedTags.find('post_tags', 'artist'); return false;">Related artists</a> |
              <a href="#" onclick="RelatedTags.find('post_tags', 'char'); return false;">Related characters</a> |
              <a href="#" onclick="RelatedTags.find('post_tags', 'copyright'); return false;">Related copyrights</a> |
              <a href="#" onclick="RelatedTags.find('post_tags', 'circle'); return false;">Related circles</a>
            </td>
          </tr>
          <?php if (CONFIG::enable_parent_posts) : ?>
            <tr>
              <th><label for="post_parent_id">Parent</label></th>
              <td><?php echo text_field_tag('post->parent_id', Request::$params->parent, array('size' => 5, 'tabindex' => 4)) ?></td>
            </tr>
          <?php endif ?>
          <tr>
            <th>
              <label for="post_rating_questionable">Rating</label>
              <?php if (User::$current->is('<20')) : ?>
                <p>Explicit tags include sex, pussy, penis, masturbation, blowjob, etc. (<?php echo link_to('help', 'help#ratings', array('target' => "_blank")) ?>)</p>
              <?php endif ?>
            </th>
            <td>
              <input id="post_rating_explicit" name="post[rating]" type="radio" value="Explicit" <?php if ((Request::$params->rating or CONFIG::default_rating_upload) == "e") : ?>checked="checked"<?php endif ?> tabindex="5">
              <label for="post_rating_explicit">Explicit</label>

              <input id="post_rating_questionable" name="post[rating]" type="radio" value="Questionable" <?php if ((Request::$params->rating or CONFIG::default_rating_upload) == "q") : ?>checked="checked"<?php endif ?> tabindex="6">
              <label for="post_rating_questionable">Questionable</label>

              <input id="post_rating_safe" name="post[rating]" type="radio" value="Safe" <?php if ((Request::$params->rating or CONFIG::default_rating_upload) == "s") : ?>checked="checked"<?php endif ?> tabindex="7">
              <label for="post_rating_safe">Safe</label>
            </td>
          </tr>
        </tbody>
      </table>

      <div id="related"><em>None</em></div>
    </div>
  </form>
  
</div>

<script type="text/javascript">
  Post.observe_text_area("post_tags")
  if (Cookie.get("upload-disclaimer") == "1") {
    $("upload-disclaimer").hide()
  }

  /* Set up PostUploadForm in dom:loaded, to make sure the login handler can attach to
   * the form first. */
  document.observe("dom:loaded", function() {
    var form = $("edit-form");
    form.down("#post_file").on("change", function(e) { form.down("#post_tags").focus(); });

    if(form)
    {
      new PostUploadForm(form, $("progress"));
      new UploadSimilarSearch(form.down("#post_file"), form.down(".similar-results"));
    }
  }.bindAsEventListener());
</script>

<?php do_content_for("post_cookie_javascripts") ?>
  <script type="text/javascript">
    RelatedTags.init(Cookie.unescape(Cookie.get('my_tags')), '<?php echo Request::$params->ref || Request::$params->url ?>')
  </script>
<?php end_content_for() ?>

<?php render_partial("footer") ?>
