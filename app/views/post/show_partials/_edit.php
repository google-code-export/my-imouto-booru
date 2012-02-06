    <div id="edit" style="display: none;">
      <form action="/post/update/<?php echo $post->id ?>" class="need-signup" id="edit-form" method="post">
        <input id="post_old_tags" name="post[old_tags]" type="hidden" value="<?php echo $post->tags ?>">
        <table class="form">
          <tfoot><tr><td colspan="2"><input accesskey="s" name="commit" tabindex="11" type="submit" value="Save changes"></td></tr></tfoot>
          <tbody>
            <tr>
              <th width="15%"><label class="block" for="post_rating_questionable">Rating</label></th>
              <td width="85%">
<?php if($post->is_rating_locked): ?>
                This post is rating locked.
<?php ;else: ?>
                <input <?php echo tag_attribute('checked', $post->rating == 'e') ?> id="post_rating_explicit" name="post[rating]" tabindex="1" type="radio" value="e" /> 
                <label for="post_rating_explicit">Explicit</label>
                <input <?php echo tag_attribute('checked', $post->rating == 'q') ?> id="post_rating_questionable" name="post[rating]" tabindex="2" type="radio" value="q" />  
                <label for="post_rating_questionable">Questionable</label>
                <input <?php echo tag_attribute('checked', $post->rating == 's') ?> id="post_rating_safe" name="post[rating]" tabindex="3" type="radio" value="s" /> 
                <label for="post_rating_safe">Safe</label>
<?php endif ?>
              </td>
            </tr>
<?php if(CONFIG::enable_parent_posts): ?>
            <tr>
              <th><label>Parent Post</label></th>
              <td><input id="parent_id" name="post[parent_id]" size="10" tabindex="4" type="text" <?php echo tag_has_value($post->parent_id) ?>/></td>
            </tr>
<?php endif ?>
            <tr>
              <th><label class="block" for="is_shown_in_index">Shown in index</label></th>
              <input name="post[is_shown_in_index]" type="hidden" value="0" />
              <td><input <?php echo tag_attribute('checked', $post->is_shown_in_index) ?>id="is_shown_in_index" name="post[is_shown_in_index]" tabindex="7" type="checkbox" value="1" />
              </td>
            </tr>
<?php if(User::is('>=20')): ?>
            <tr>
              <th><label class="block" for="is_note_locked">Note locked</label></th>
              <input name="post[is_note_locked]" type="hidden" value="0" />
              <td><input <?php echo tag_attribute('checked', $post->is_note_locked) ?> id="is_note_locked" name="post[is_note_locked]" tabindex="7" type="checkbox" value="1" />
              </td>
            </tr>
            
            <tr>
              <th><label class="block" for="is_rating_locked">Rating locked</label></th>
              <input name="post[is_rating_locked]" type="hidden" value="0" />
              <td><input <?php echo tag_attribute('checked', $post->is_rating_locked) ?>id="is_rating_locked" name="post[is_rating_locked]" tabindex="8" type="checkbox" value="1" />
              </td>
            </tr>
<?php endif ?>
            <tr>
              <th><label class="block" for="source">Source</label></th>
              <td><input id="source" name="post[source]" size="40" tabindex="9" type="text" value="<?php echo h($post->source) ?>" /></td>
            </tr>
            
            <tr>
              <th>
                <label class="block" for="post_tags">Tags</label>
<?php if(User::is('<20')): ?>
                  <p>Separate tags with spaces (<a href="/help/tags" target="_blank">help</a>)</p>
<?php endif ?>
              </th>
              <td>
                <textarea cols="50"<?php echo tag_attribute('disabled', $post->is_deleted()) ?> id="post_tags" name="post[tags]" rows="4" tabindex="10" autocomplete="off"><?php echo $post->tags . ' ' ?></textarea>
<?php if($post->can_be_seen_by()): ?>
                <a href="" onclick="RelatedTags.find('tags'); return false;">Related tags</a> | 
                <a href="" onclick="RelatedTags.find('tags', 'artist'); return false;">Related artists</a> | 
                <a href="" onclick="RelatedTags.find('tags', 'char'); return false;">Related characters</a> | 
                <a href="" onclick="RelatedTags.find('tags', 'copyright'); return false;">Related copyrights</a> | 
                <a href="" onclick="RelatedTags.find_artist($F('source')); return false;">Find artist</a>
<?php endif ?>
              </td>
            </tr>
          </tbody>
        </table>
        <div>
        <h5>Related Tags</h5>
        <div style="margin-bottom: 1em;" id="related"><em>None</em></div>
        </div>
      </form>
    </div>

