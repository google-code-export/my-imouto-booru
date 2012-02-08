<div id="forum">
  <div id="search" style="margin-bottom: 1em;">
    <?php echo form_tag("#search", array('method' => 'get')) ?>
      <?php echo text_field_tag("query", request::$params->query, array('size' => 40)) ?>
      <?php echo submit_tag("Search") ?>
    </form>
  </div>
  
  <table class="highlightable" width="100%">
    <thead>
      <tr>
        <th width="65%">Title</th>
        <th width="10%">Created by</th>
        <th width="10%">Updated by</th>
        <th width="10%">Updated</th>
        <th width="5%">Responses</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($forum_posts as $fp) : ?>
      <tr class="<?php echo cycle('even', 'odd') ?>">
        <td>
          <?php if (!user::$current->is_anonymous && $fp->updated_at > user::$current->last_forum_topic_read_at) : ?>
            <span class="forum-topic unread-topic"><?php if ($fp->is_sticky) : ?>Sticky: <?php endif ?><?php echo link_to(h($fp->title), array("#show", 'id' => $fp->id)) ?></span>
          <?php ;else: ?>
            <span class="forum-topic"><?php if ($fp->is_sticky) : ?>Sticky: <?php endif ?><?php echo link_to(h($fp->title), array("#show", 'id' => $fp->id)) ?></span>
          <?php endif ?>

          <?php if ($fp->response_count > 30) : ?>
            <?php echo link_to("last", array("#show", 'id' => $fp->id, 'page' => ceil($fp->response_count / 30)), array('class' => "last-page")) ?>
          <?php endif ?>

          <?php if ($fp->is_locked) : ?>
            <span class="locked-topic">(locked)</span>
          <?php endif ?>
        </td>
        <td><?php echo h($fp->author) ?></td>
        <td><?php echo h($fp->last_updater) ?></td>
        <td><?php echo time_ago_in_words($fp->updated_at) ?> ago</td>
        <td><?php echo $fp->response_count ?></td>
      </tr>
      <?php endforeach ?>
    </tbody>
  </table>

  <div id="paginator">
    <?php paginator() ?>
  </div>

  <?php do_content_for("subnavbar") ?>
    <li><?php echo link_to("New topic", "#new") ?></li>
    <?php if (!user::$current->is_anonymous) : ?>
      <li><a href="#" onclick="Forum.mark_all_read(); return false;">Mark all read</a></li>
    <?php endif ?>
    <li><?php echo link_to("Help", "help#forum") ?></li>
  <?php end_content_for() ?>

  <div id="preview" style="display: none; margin: 1em 0;">
  </div>

  <div id="reply" style="display: none;">
    <form action="/forum/create" class="need-signup" method="post">
      <?php echo hidden_field_tag("forum_post[parent_id]", request::$params->parent_id) ?>
      <table>
        <tr>
          <td><label for="forum_post_title">Title</label></td>
          <td><?php echo text_field_tag("forum_post[title]", array('size' => 60)) ?></td>
        </tr>
        <tr>
          <td colspan="2"><?php echo text_area("forum_post[body]", array('rows' => 20, 'cols' => 80)) ?></td>
        </tr>
        <tr>
          <td colspan="2"><?php echo submit_tag("Post") ?><input name="preview" onclick="new Ajax.Updater('preview', '/forum/preview', {asynchronous:true, evalScripts:true, method:'get', onSuccess:function(request){$('preview').show()}, parameters:Form.serialize(this.form)});" type="button" value="Preview"></td>
        </tr>
      </table>
    </form>
  </div>
</div>
