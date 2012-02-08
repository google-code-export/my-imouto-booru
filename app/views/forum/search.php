<div id="forum">
  <div style="margin-bottom: 1em;">
    <?php echo form_tag("#search", array('method' => 'get')) ?>
      <?php echo text_field_tag("query", request::$params->query, array('size' => 40)) ?>
      <?php echo submit_tag("Search") ?>
    </form>
  </div>
  
  <table class="highlightable">
    <thead>
      <tr>
        <th width="20%">Topic</th>
        <th width="50%">Message</th>
        <th width="10%">Author</th>
        <th width="20%">Last Updated</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($forum_posts as $fp): ?>
        <tr class="<?php echo cycle('even', 'odd') ?>">
          <td><?php echo link_to(h($fp->root->title), array("#show", 'id' => $fp->root_id)) ?></td>
          <td><?php echo link_to(h(substr($fp->body, 0, 70) . "..."), array("#show", 'id' => $fp->id)) ?></td>
          <td><?php echo h($fp->author) ?></td>
          <td><?php echo time_ago_in_words($fp->updated_at) ?> ago by <?php echo $fp->last_updater ?></td>
        </tr>
      <?php endforeach ?>
    </tbody>
  </table>

  <div id="paginator">
    <?php paginator() ?>
  </div>
</div>
