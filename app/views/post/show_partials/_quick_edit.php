<?php if(Request::$action != 'show' || !CONFIG::enable_quick_edit) { ?>
    <h5>Tags</h5>
<?php return; } ?>
    <h5 style="display:inline;">Tags |</h5>
    <a id="qedit" onclick="$('tag-sidebar').hide();$('quick-edit').show();$('qedit').hide();$('qcancel').show();$('post_tags_quick').focus();" style="cursor:pointer;">Quick edit</a>
    <a id="qcancel" onclick="$('tag-sidebar').show();$('quick-edit').hide();$('qedit').show();$('qcancel').hide();" style="display:none; cursor:pointer;">Cancel</a>
<?php do_content_for("quick_edit_form") ?>

    <div id="quick-edit" style="display:none;margin:15px 0px;border:1px solid #ffaaae;padding:3px;width:234px;">
      <form action="/post/update/<?php echo $post->id ?>" class="need-signup" id="edit-form" method="post" style="width:234px;">
        <input type="hidden" name="post[old_tags]" value="<?php echo $post->tags ?>" />
        <input type="hidden" name="post[is_shown_in_index]" value="<?php echo $post->is_shown_in_index ?>" />
        <input type="hidden" name="post[is_note_locked]" value="<?php echo $post->is_note_locked ?>"  />
        <input type="hidden" name="post[is_rating_locked]" value="<?php echo $post->is_rating_locked ?>" />
        <table class="form" style="width:235px;margin-bottom:0;">
          <tr><th style="text-align:left;"><label class="block" for="post_rating_questionable">Rating</label></th></tr>
          
          <tr><td>
              <input <?php echo tag_attribute('checked', $post->rating == 'e') ?> id="post_rating_explicit" name="post[rating]" tabindex="20" type="radio" value="e" /> 
              <label for="post_rating_explicit">Explicit</label>
          </td></tr>
              
          <tr><td>
              <input <?php echo tag_attribute('checked', $post->rating == 'q') ?> id="post_rating_questionable" name="post[rating]" tabindex="21" type="radio" value="q" />  
              <label for="post_rating_questionable">Questionable</label>
          </td></tr>
          
          <tr><td>
              <input <?php echo tag_attribute('checked', $post->rating == 's') ?> id="post_rating_safe" name="post[rating]" tabindex="22" type="radio" value="s" /> 
              <label for="post_rating_safe">Safe</label>
          </td></tr>

          <tr><th style="text-align:left;"><label class="block" for="post_tags_quick">Tags</label></th></tr>
          
          <tr><td>
            <textarea cols="50"<?php echo tag_attribute('disabled', $post->is('deleted')) ?> id="post_quick_tags" style="width:208px;" name="post[tags]" rows="4" tabindex="10" autocomplete="off"><?php echo $post->tags . ' ' ?></textarea>
          </td></tr>
          
          <tr><td><input name="commit" tabindex="24" type="submit" value="Save changes" /></td></tr>
        </table>
      </form>
    </div>
<?php end_content_for() ?>

<?php do_content_for("post_cookie_javascripts") ?>
<script type="text/javascript">
  new TagCompletionBox($("post_quick_tags"));
</script>
<?php end_content_for() ?>

