<?php
!isset($include_tag_hover_highlight) && $include_tag_hover_highlight = false;
!isset($include_tag_reverse_aliases) && $include_tag_reverse_aliases = false;
?>
  <div>
<?php render_partial('post/show_partials/quick_edit') ?>
    <ul id="tag-sidebar">
      <?php !empty($tags['exclude']) && print tag_links($tags['exclude'], array('prefix' => "-", 'with_hover_highlight' => true, 'with_hover_highlight' => $include_tag_hover_highlight)) ?>
      <?php !empty($tags['include']) && print tag_links($tags['include'], array('with_aliases' => $include_tag_reverse_aliases, 'with_hover_highlight' => $include_tag_hover_highlight)) ?>
      <?php !empty($tags['related']) && print tag_links(Tag::$_->find_related($tags['related']), array('with_hover_highlight' => true, 'with_hover_highlight' => $include_tag_hover_highlight)) ?>
    </ul>
<?php content_for("quick_edit_form") ?>
  </div>
