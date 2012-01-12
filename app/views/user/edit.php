<div id="user-edit">
  <form action="/user/update" method="post">
    <table class="form">
      <tfoot>
        <tr>
          <td colspan="2">
            <input name="commit" type="submit" value="Save"> <input name="commit" type="submit" value="Cancel">
          </td>
        </tr>
      </tfoot>
      <tbody>
        <tr>
          <th width="15%">
            <label class="block" for="user_blacklisted_tags">Tag Blacklist</label>
            <p>Any post containing all blacklisted tags on a line will be hidden. Separate tags with spaces.</p>
          </th>
          <td width="85%">
            <textarea cols="80" id="user_blacklisted_tags" name="user[blacklisted_tags]" rows="6"><?php echo $user->blacklisted_tags() ?></textarea>
          </td>
        </tr>
        <tr>
          <th>
            <label class="block" for="user_email">Email</label>
            <?php if (CONFIG::enable_account_email_activation) : ?>
              <p>An email address is required to activate your account.</p>
            <?php ;else: ?>
              <p>This field is optional. It is useful if you ever forget your password and want to reset it.</p>
            <?php endif ?>
          </th>
          <td>
            <input id="user_email" name="user[email]" size="40" type="text" value="<?php echo $user->email ?>" />
          </td>
        </tr>
        <tr>
          <th>
            <label class="block" for="user_tag_subscriptions_text">Tag Subscriptions</label>
          </th>
          <td class="large-text">
            <?php render_partial("tag_subscription/user_listing", array('user')) ?>
          </td>          
        </tr>
        <tr>
          <th>
            <label class="block" for="user_my_tags">Edit Tags</label>
            <p>These will be accessible when you <a href="/post/upload">upload</a> or edit a post.</p>
          </th>
          <td>
            <textarea cols="40" id="user_my_tags" name="user[my_tags]" rows="5"><?php echo $user->my_tags ?></textarea>
          </td>
        </tr>
        <tr>
          <th>
            <label class="block" for="user_always_resize_images">Resize Images</label>
            <p>If enabled, large images will always be resized to fit the screen.</p>
          </th>
          <td>
            <input name="user[always_resize_images]" type="hidden" value="0" />
            <input id="user_always_resize_images" name="user[always_resize_images]" type="checkbox" value="1"<?php echo tag_attr_checked($user->always_resize_images) ?> />
          </td>
        </tr>
        <tr>
          <th>
            <label class="block" for="user_receive_dmails">Receive Mails</label>
            <p>Receive emails when someone sends you a message.</p>
          </th>
          <td>
            <input name="user[receive_dmails]" type="hidden" value="0" />
            <input id="user_receive_dmails" name="user[receive_dmails]" type="checkbox" value="1"<?php echo tag_attr_checked($user->receive_dmails) ?> />
          </td>
        </tr>
        <?php if (CONFIG::image_samples && !CONFIG::force_image_samples) : ?>
        <tr>
          <th>
            <label class="block" for="user_show_samples">Show Image Samples</label>
            <p>Show reduced large-resolution images.</p>
          </th>
          <td>
            <input name="user[show_samples]" type="hidden" value="0" />
            <input id="user_show_samples" name="user[show_samples]" type="checkbox" value="1"<?php echo tag_attr_checked($user->show_samples) ?> />
          </td>
        </tr>
        <?php endif ?>
        <tr>
          <th>
            <label class="block" for="user_use_browser">Use post browser</label>
            <p>Use the post browser when viewing posts and pools.</p>
          </th>
          <td>
            <input name="user[use_browser]" type="hidden" value="0" />
            <input id="user_use_browser" name="user[use_browser]" type="checkbox" value="1"<?php echo tag_attr_checked($user->use_browser) ?> />
          </td>
        </tr>
        <tr>
          <th>
            <label class="block" for="user_show_advanced_editing">Advanced Editing</label>
            <p>Show advanced editing controls.</p>
          </th>
          <td>
            <input name="user[show_advanced_editing]" type="hidden" value="0" />
            <input id="user_show_advanced_editing" name="user[show_advanced_editing]" type="checkbox" value="1"<?php echo tag_attr_checked($user->show_advanced_editing) ?> />
          </td>
        </tr>
        <tr>
          <th>
            <label class="block" for="user_language">Language</label>
            <p>Language to show comments in.</p>
          </th>
          <td>
            <select id="user_language" name="user[language]">
              <option value="" <?php empty($user->language) && print 'selected="selected"' ?>>Original (no translation)</option>
              <?php foreach (CONFIG::$translate_languages as $lang) : ?>
                <option value="<?php echo h($lang) ?>" <?php $user->language == $lang && print 'selected="selected"' ?>><?php echo h(CONFIG::$language_names[$lang]) ?></option>
              <?php endforeach ?>
            </select>
          </td>
        </tr>
        <tr>
          <th>
            <label class="block" for="user_secondary_languages">Secondary languages</label>
            <p>Languages to not translate from.</p>
          </th>
          <td>
            <select id="user_secondary_languages" name="user[secondary_language_array][]" multiple size="10">
              <option value="none" <?php empty($user->secondary_language_array) && print 'selected="selected"' ?>>(none)</option>
              <?php foreach (CONFIG::$language_names as $short => $lang) : ?>
                <option value="<?php echo h($lang) ?>" <?php in_array($lang, $user->secondary_language_array()) && print 'selected="selected"' ?>><?php echo h($short) ?></option>
              <?php endforeach ?>
            </select>
          </td>
        </tr>
      </tbody>
    </table>
  </form>
</div>

<?php render_partial("footer") ?>
