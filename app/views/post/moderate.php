<form method="get" action="/post/moderate">
  <?php echo text_field_tag("query", array('size' => 40)) ?>
  <?php echo submit_tag("Search") ?>
</form>

<script type="text/javascript">
  function highlight_row(checkbox) {
    var row = checkbox.parentNode.parentNode
    if (row.original_class == null) {
      row.original_class = row.className
    }
    
    if (checkbox.checked) {
      row.className = "highlight"
    } else {
      row.className = row.original_class
    }
  }
</script>

<div style="margin-bottom: 2em;">
  <h2>Pending</h2>
  <form method="post" action="/post/moderate">
    <?php echo hidden_field_tag("reason", '') ?>

    <table width="100%">
      <tfoot>
        <tr>
          <td colspan="3">
            <a href="#" onclick="$$('.p').each(function (i) {i.checked = true; highlight_row(i)});return false;">Select all</a>
            <a href="#" onclick="$$('.p').each(function (i) {i.checked = !i.checked; highlight_row(i)});return false;">Invert selection</a>
            <?php echo submit_tag("Approve") ?> 
            <?php echo submit_tag("Delete", array('onclick' => "var reason = prompt('Enter a reason'); if (reason != null) {\$('reason').value = reason; return true} else {return false}")) ?>
          </td>
        </tr>
      </tfoot>
      <tbody>
        <?php foreach ($pending_posts as $p) : ?>
          <tr class="<?php if ($p->score > 2): ?>good<?php ;elseif ($p->score < -2): ?>bad<?php endif ?> <?php echo cycle('even', 'odd') ?>">
            <td><input type="checkbox" class="p" name="ids[<?php echo p.id ?>]" onclick="highlight_row(this);return false;"></td>
            <td><?php echo link_to(image_tag($p->preview_url, array('width' => $p->preview_dimensions('w'), 'height' => $p->preview_dimensions('h')), array('post#show', 'id' => $p->id))) ?></td>
            <td class="checkbox-cell">
              <ul>
                <li>Uploaded by <?php echo link_to(h($p->author), array("user#show", 'id' => $p->user_id)) ?> <?php echo time_ago_in_words($p->created_at) ?> ago (<?php echo link_to("mod", array("#moderate", 'query' => "user:{$p->author}")) ?>)</li>
                <li>Rating: <?php echo $p->pretty_rating() ?></li>
                <?php if ($p->parent_id) : ?>
                  <li>Parent: <?php echo link_to($p->parent_id, array("#moderate", 'query' => "parent:{$p->parent_id}")) ?></li>
                <?php endif ?>
                <li>Tags: <?php echo h($p->cached_tags) ?></li>
                <li>Score: <span id="post-score-<?php echo p.id ?>"><?php echo p.score ?></span></li>
                <?php if ($p->flag_detail) : ?>
                <li>
                  Reason: <?php echo h($p->flag_detail->reason) ?> (<?php if (!$p->flag_detail->user_id) : ?>automatic flag<?php ;else: ?><?php echo link_to(h($p->flag_detail->author), array("user#show", 'id' => $p->flag_detail->user_id)) ?><?php endif ?>)
                </li>
                <?php endif ?>
                <li>Size: <?php echo number_to_human_size($p->file_size) ?>, <?php echo $p->width ?>x<?php echo $p->height ?></li>
              </ul>
            </td>
          </tr>
        <?php endforeach ?>
      </tbody>
    </table>
  </form>
</div>

<div>
  <h2>Flagged</h2>
  <form method="post" action="/post/moderate">
    <?php echo hidden_field_tag("reason2", '') ?>

    <table width="100%">
      <tfoot>
        <tr>
          <td colspan="3">
            <a href="#" onclick="$$('.f').each(function (i) {i.checked = true; highlight_row(i)});return false;">Select all</a>
            <a href="#" onclick="$$('.f').each(function (i) {i.checked = !i.checked; highlight_row(i)});return false;">Invert selection</a>
            <?php echo submit_tag("Approve") ?> 
            <?php echo submit_tag("Delete", array('onclick' => "var reason = prompt('Enter a reason'); if (reason != null) {\$('reason2').value = reason; return true} else {return false}")) ?>
          </td>
        </tr>
      </tfoot>
      <tbody>
        <?php foreach ($flagged_posts as $p) : ?>
          <tr class="<?php echo cycle('even', 'odd') ?>">
            <td><input type="checkbox" class="f" name="ids[<?php echo $p->id ?>]" onclick="highlight_row(this);"></td>
            <td><?php echo link_to(image_tag($p->preview_url, array('width' => $p->preview_dimensions('w'), 'height' => $p->preview_dimensions('h'))), array('post#show', 'id' => $p->id)) ?></td>
            <td class="checkbox-cell">
              <ul>
                <li>Uploaded by <?php echo link_to(h($p->author), array("user#show", 'id' => $p->user_id)) ?> <?php echo time_ago_in_words($p->created_at) ?> ago (<?php echo link_to("mod", array("#moderate", 'query' => "user:{$p->author}")) ?>)</li>
                <li>Rating: <?php echo $p->pretty_rating() ?></li>
                <?php if ($p->parent_id) : ?>
                  <li>Parent: <?php echo link_to($p->parent_id, array("#moderate", 'query' => "parent:{$p->parent_id}")) ?></li>
                <?php endif ?>
                <li>Tags: <?php echo h($p->cached_tags) ?></li>
                <li>Score: <span id="post-score-<?php echo $p->id ?>"><?php echo $p->score ?></span></li>
                <?php if ($p->flag_detail) : ?>
                <li>
                  Reason: <?php echo h($p->flag_detail->reason) ?> (<?php if (!$p->flag_detail->user_id) : ?>automatic flag<?php ;else: ?><?php echo link_to(h($p->flag_detail->author), array("user#show", 'id' => $p->flag_detail->user_id)) ?><?php endif ?>)
                </li>
                <?php endif ?>
                <li>Size: <?php echo bytes_to_human($p->file_size) ?>, <?php echo $p->width ?>x<?php echo $p->height ?></li>
              </ul>
            </td>
          </tr>
        <?php endforeach ?>
      </tbody>
    </table>
  </form>
  
  <script type="text/javascript">
    var cells = $$(".checkbox-cell")
    $$(".checkbox-cell").invoke("observe", "click", function(e) {this.up().firstDescendant().down("input").click()})
    <?php foreach(array_unique(array_merge((array)$pending_posts, (array)$flagged_posts)) as $post) : //(@pending_posts + @flagged_posts).uniq.each do |post| ?>
      Post.register(<?php echo $post->to_json() ?>)
    <?php endforeach ?>
  </script>
</div>

<?php render_partial("footer") ?>
