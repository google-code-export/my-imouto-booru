<div style="margin-bottom: 1em;">
  <?php echo form_tag("#index", array('method' => 'get')) ?>
    <?php echo text_field_tag("query", Request::$params->query) ?> 
    <?php echo submit_tag("Search Implications") ?>  
    <?php echo submit_tag("Search Aliases") ?>
  </form>  
</div>

<?php echo form_tag("#update") ?>
  <table class="highlightable" width="100%">
    <thead>
      <tr>
        <th width="1%"></th>
        <th width="19%">Predicate</th>
        <th width="20%">Consequent</th>
        <th width="60%">Reason</th>
      </tr>      
    </thead>
    <tfoot>
      <tr>
        <td colspan="4">
          <?php if (User::$current->is('>=40')) : ?>
            <input type="button" onclick="$$('.pending').each(function(x) {x.checked = true});" value="Select pending" />
            <?php echo submit_tag("Approve") ?> 
          <?php endif ?>
          <input type="button" onclick="$('reason-box').show(); $('reason').focus();" value="Delete" />
          <input type="button" onclick="$('add-box').show().scrollTo(); $('tag_implication_predicate').focus();" value="Add" />
          
          <div id="reason-box" style="display: none; margin-top: 1em;">
            <strong>Reason:</strong>
            <?php echo text_field_tag("reason", null, array('size' => 40)) ?>
            <?php echo submit_tag("Delete") ?>
          </div>
        </td>
      </tr>      
    </tfoot>
    <tbody>
      <?php foreach($implications as $i): ?>
        <tr class="<?php echo cycle('even', 'odd') ?> <?php echo $i->is_pending ? 'pending-tag' : null ?>">
          <td><input type="checkbox" value="1" name="implications[<?php echo $i->id ?>]" <?php echo $i->is_pending ? 'class="pending"' : null ?>></td>
          <td><?php echo link_to(h($i->predicate->name), array("post#index", 'tags' => $i->predicate->name)) ?> (<?php echo $i->predicate->post_count ?>)</td>
          <td><?php echo link_to(h($i->consequent->name), array("post#index", 'tags' => $i->consequent->name)) ?> (<?php echo $i->consequent->post_count ?>)</td>
          <td><?php echo h($i->reason) ?></td>
        </tr>
      <?php endforeach ?>      
    </tbody>
  </table>
</form>

<div id="add-box" style="display: none;">
  <?php echo form_tag("#create") ?>
    <h4>Add Implication</h4>
    <p>You can suggest a new implication, but it must be approved by a moderator before it is activated.</p>  
    <p>The predicate tag is the tag that is matched against, and the consequent tag is the tag that is added. For example, a tag implication with predicate=square consequent=rectangle would mean any post tagged with square would also be tagged with rectangle.</p>
    <?php if (!User::$current->is_anonymous): ?>
      <?php echo hidden_field_tag("tag_implication[creator_id]", User::$current->id) ?>
    <?php endif ?>
    
    <table>
      <tr>
        <th><label for="tag_implication_predicate">Predicate</label></th>
        <td><?php echo text_field_tag("tag_implication[predicate]", array('size' => 40)) ?></td>
      </tr>
      <tr>
        <th><label for="tag_implication_consequent">Consequent</label></th>
        <td><?php echo text_field_tag("tag_implication[consequent]", array('size' => 40)) ?></td>
      </tr>
      <tr>
        <th><label for="tag_implication_reason">Reason</label></th>
        <td><?php echo text_area("tag_implication[reason]", array('size' => "40x2")) ?></td>
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
