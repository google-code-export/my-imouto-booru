        <div>
          <h5>Related Posts</h5>
          <ul>
            <li><a href="/post/show/<?php echo ($post->id - 1) ?>">Previous</a></li>
            <li><a href="/post/show/<?php echo  ($post->id + 1) ?>">Next</a></li>
<?php if ($post->parent_id): ?>
            <li><a href="/post/show/<?php echo $post->parent_id ?>">Parent</a>
<?php endif ?>
            <li><a href="/post/random">Random</a></li>
            <li><a id="find-dupes">Find dupes</a></li>
            <li><a id="find-similar">Find similar</a></li>
            <script type="text/javascript">
              $("find-dupes").href = '#';
              $("find-similar").href = '#';
            </script>
          </ul>
        </div>
