<div style="margin-bottom: 1em;">
  <?php echo form_tag('#index', array('method' => 'get')) ?>
    <?php echo text_field_tag("query", Request::$params->query) ?> 
    <?php echo submit_tag("Search Aliases") ?>
    <?php echo submit_tag("Search Implications") ?>  
  </form>
</div>

<div id="aliases">
  <?php echo form_tag("#update") ?>
    <table width="100%" class="highlightable">
      <thead>
        <tr>
          <th width="1%"></th>
          <th width="19%">Alias</th>
          <th width="20%">To</th>
          <th width="60%">Reason</th>
        </tr>        
      </thead>
      <tfoot>
        <tr>
          <td colspan="4">
            <?php if (User::$current->is('>=40')) : ?>
              <a href="#" onclick="$$('.pending').each(function(x) {x.checked = true}); return false;">Select pending</a>
              <?php echo submit_tag("Approve") ?> 
            <?php endif ?>
            <a href="#" onclick="$('reason-box').show(); $('reason').focus(); return false;">Delete</a>
            <a href="#" onclick="$('add-box').show().scrollTo(); $('tag_alias_name').focus(); return false;">Add</a>

            <div id="reason-box" style="display: none; margin-top: 1em;">
              <strong>Reason:</strong>
              <?php echo text_field_tag("reason", "", array('size' => 40)) ?>
              <?php echo submit_tag("Delete") ?>
            </div>
          </td>
        </tr>
      </tfoot>
      <tbody>
        <?php foreach ($aliases as $a) : ?>
          <tr class="<?php echo cycle('even', 'odd'); echo $a->is_pending ? ' pending-tag' : null ?>">
            <td><input type="checkbox" name="aliases[<?php echo $a->id ?>]" value="1"<?php echo $a->is_pending ? ' class="pending"' : null ?>></td>
            <td><?php echo link_to(h($a->name), array('post#index', 'tags' => $a->name)) ?> (<?php $count = Tag::$_->find_post_count_by_name($a->name); echo $count ? $count : 0 ?>)</td>
            <td><?php echo link_to(h($a->alias_name()), array('post#index', 'tags' => $a->alias_name())) ?> (<?php $post_count = Tag::$_->find_post_count_by_id($a->alias_id); echo $post_count ? $post_count : 0 ?>)</td>
            <td><?php echo h($a->reason) ?></td>
          </tr>
        <?php endforeach ?>        
      </tbody>
    </table>
  </form>
</div>

<div id="add-box" style="display: none;">
  <?php echo form_tag("#create") ?>
    <h4>Add Alias</h4>
    <p>You can suggest a new alias, but it must be approved by an administrator before it is activated.</p>  

    <?php if (!User::$current->is_anonymous) : ?>
      <?php echo hidden_field_tag("tag_alias[creator_id]", User::$current->id) ?>
    <?php endif ?>
    
    <table>
      <tr>
        <th><label for="tag_alias_name">Name</label></th>
        <td><?php echo text_field_tag("tag_alias->name", array('size' => 40)) ?></td>
      </tr>
      <tr>
        <th><label for="tag_alias_alias">Alias to</label></th>
        <td><?php echo text_field_tag("tag_alias->alias", array('size' => 40)) ?></td>
      </tr>
      <tr>
        <th><label for="tag_alias_reason">Reason</label></th>
        <td><?php echo text_area("tag_alias->reason", array('size' => "40x2")) ?></td>
      </tr>
      <tr>
        <td colspan="2"><?php echo submit_tag("Submit") ?></td>
      </tr>
    </table>
  </form>
</div>

<div id="paginator">
  <?php paginator() ?>
</div>

<?php render_partial("/tag/footer") ?>
