<h3>Pool Ordering: <?php echo link_to($pool->pretty_name(), array('#show', 'id' => $pool->id))  ?></h3>
<p>Lower numbers will appear first.</p>

<script type="text/javascript">
  function orderAutoFill() {
    var i = 0
    var step = parseInt(prompt("Enter an interval"))

    $$(".pp").each(function(x) {
      x.value = i
      i += step
    })
  }

  function orderReverse() {
    var orders = []
    $$(".pp").each(function(x) {
      orders.push(x.value)
    })
    var i = orders.size() - 1
    $$(".pp").each(function(x) {
      x.value = orders[i]
      i -= 1
    })
  }

  function orderShift(start, offset) {
    var found = false;
    $$(".pp").each(function(x) {
      if(x.id == "pool_post_sequence_" + start)
        found = true;
      if(!found)
        return;
      x.value = Number(x.value) + offset;
    });
  }
</script>

<?php echo form_tag("#order") ?>
  <?php echo hidden_field_tag("pool->id", $pool->id) ?>
  <table>
    <thead>
      <tr>
        <th></th>
        <th>Order</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach($pool_posts as $pp) : ?>
        <tr>
          <td>
            <?php if ($pp->post->can_be_seen_by(User::$current)) : ?>
              <?php echo link_to(image_tag($pp->post->preview_url, array('width' => $pp->post->preview_dimensions('w'), 'height' => $pp->post->preview_dimensions('h'))), array('post#show', 'id' => $pp->post_id), array('title' => $pp->post->tags)) ?>
            <?php endif ?>
          </td>
          <td>
            <?php echo text_field_tag("pool_post_sequence[{$pp->id}]", $pp->sequence, array('class' => "pp", 'size' => 5, 'tabindex' => 1)) ?>
            <a href="#" onclick="orderShift(<?php echo $pp->id ?>, +1); return false;" class="text-button">+1</a>
            <a href="#" onclick="orderShift(<?php echo $pp->id ?>, -1); return false;" class="text-button">-1</a>
          </td>
        </tr>
      <?php endforeach ?>
    </tbody>
    <tfoot>
      <tr>
        <td colspan="2"><input type="submit" value="Save" tabindex="1" /> <input type="button" onclick="orderAutoFill();" value="Auto Order" tabindex="2" /> <input type="button" value="Reverse" onclick="orderReverse();" tabindex="3" /> <input type="button" value="Cancel" onclick="history.back();" tabindex="4" /></td>
      </tr>
    </tfoot>
  </table>
</form>
