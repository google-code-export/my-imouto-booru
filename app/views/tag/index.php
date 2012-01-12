<div>
  <?php echo form_tag('#index', 'get') ?>
    <table class="form">
      <tbody>
        <tr>
          <th width="15%">
            <label for="name">Name</label>
            <p>Use * as a wildcard.</p>
          </th>
          <td width="85%"><?php echo text_field_tag('>name', Request::$params->name, array('size' => 40)) ?></td>
        </tr>
        <tr>
          <th><label for="type">Type</label></th>
          <td><?php echo select_tag('type', array_merge(array('Any' => 'any'), array_unique(CONFIG::$tag_types)), Request::$params->type) ?></td>
        </tr>
        <tr>
          <th><label for="order">Order</label></th>
           <td><?php echo select_tag('order', array('Name' => 'name', 'Count' => 'count', 'Date' => 'date'), Request::$params->order) ?></td>
        </tr>
      </tbody>
      <tfoot>
        <tr>
          <td><?php echo submit_tag('Search') ?></td>
          <td></td>
        </tr>
      </tfoot>
    </table>
  </form>
</div>

<table width="100%" class="highlightable"> 
  <thead>
    <tr>
      <th width="5%">Posts</th>
      <th width="45%">Name</th>
      <th width="50%">Type</th>
    </tr>
  </thead>
  <tbody>
    <?php foreach ($tags as $k => $tag) : ?>
      <tr class="<?php echo cycle('even', 'odd') ?>">
        <td align="right"><?php echo $tag->post_count ?></td>
	<td class="tag-type-<?php echo $tag->type_name ?>">
	  <a href="/wiki/show?title=<?php echo u($tag->name) ?>">?</a>
	  <a href="/post/index?tags=<?php echo u($tag->name) ?>"><?php echo h($tag->name) ?></a>
	</td>
        <td>
        <?php echo $tag->type_name . ($tag->is_ambiguous && ', ambiguous') ?>
        (<?php echo link_to('edit', array('#edit', 'name' => $tag->name)) ?>)
        <?php if (CONFIG::allow_delete_tags) : ?>
        (<?php echo link_to('delete', array_merge(array('#delete', 'tag_name' => $tag->name), (array)Request::$get_params)) ?>)
        <?php endif ?>
        </td>
      </tr>
    <?php endforeach ?>
  </tbody>
</table>

<div id="paginator">
  <?php paginator() ?>
</div>

<?php render_partial('footer') ?>
