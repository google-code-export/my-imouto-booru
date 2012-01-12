<div id="pool-index">
  <div style="margin-bottom: 2em;">
    <form action="/pool" method="get">
      <?php if (!empty(Request::$params->order)): ?><input type="hidden" name="order" value="<?php echo Request::$params->order ?>" /><?php endif ?>
      <input id="query" name="query" size="40" type="text"<?php if(!empty(Request::$params->query)): ?>value="<?php echo Request::$params->query ?>"<?php endif ?> />
      <input type="submit" value="Search" />
    </form>
  </div>

  <img style="position: absolute; display: none; border: 2px solid #000; right: 42%;" id="hover-thumb" src="about:blank" alt="" />
  
  <table width="100%" class="highlightable">
    <thead>
      <tr>
        <th width="60%">Name</th>
        <th width="*">Creator</th>
        <th width="*">Posts</th>
        <th width="*">Created</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach($pools as $k => &$p) : ?>
        <tr class="<?php echo $k &1 ? 'odd' : 'even' ?>" id="p<?php echo $p->id ?>">
          <td><a href="/pool/show/<?php echo $p->id ?>"><?php echo $p->pretty_name() ?></a></td>
          <td><?php echo h($p->user->pretty_name()) ?></td>
          <td><?php echo $p->post_count ?></td>
          <td><?php echo time_ago_in_words($p->created_at) ?> ago</td>
        </tr>
      <?php endforeach ?>
    </tbody>
  </table>
</div>

<div id="paginator">
  <?php paginator() ?>
</div>

<?php do_content_for("post_cookie_javascripts") ?>
<script type="text/javascript">
  var thumb = $("hover-thumb");
  <?php foreach($samples as $k => $post) : ?>
    Post.register(<?php echo to_json($post) ?>);
    var hover_row = $("p<?php echo $pools->$k->id ?>");
    var container = hover_row.up("TABLE");
    Post.init_hover_thumb(hover_row, <?php echo $post->id ?>, thumb, container);
  <?php endforeach ?>
  Post.init_blacklisted({replace: true});

  <?php foreach($samples as $post) : ?>
    if(!Post.is_blacklisted(<?php echo $post->id ?>))
      Preload.preload('<?php echo addslashes($post->preview_url) ?>');
  <?php endforeach ?>
</script>
<?php end_content_for() ?>

<?php render_partial("footer") ?>