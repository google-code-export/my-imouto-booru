<?php echo form_tag("#update") ?>
  <table class="form">
    <tr>
      <th width="15%"><label for="tag_name">Name</label></th>
      
      <td width="85%">
        <?php echo text_field_tag('tag->name', $tag->name, array('size' => 30, 'autocomplete' => 'off')) ?>
        
        <div class="auto_complete" id="tag_name_auto_complete" style="display: none"></div>
        <script type="text/javascript">
        //&lt;![CDATA[
        var tag_name_auto_completer = new Ajax.Autocompleter('tag_name', 'tag_name_auto_complete', '/tag/auto_complete_for_tag_name', {minChars:3})
        //]]>
        </script>
      </td>
    </tr>
    <tr>
      <th><label for="tag_type">Type</label></th>
      <td><?php echo select_tag('tag->tag_type', array_unique(CONFIG::$tag_types), $tag->tag_type) ?></td>
    </tr>
    <tr>
      <th><label for="tag_is_ambiguous">Ambiguous</label></th>
      <td><?php echo checkbox_tag('tag->is_ambiguous') ?></td>
    </tr>
    <tr>
      <td colspan="2"><?php echo submit_tag('Save') ?> <input onclick="history.back();" type="button" value="Cancel"/></td>
    </tr>
  </table>
</form>

<?php render_partial('footer') ?>
