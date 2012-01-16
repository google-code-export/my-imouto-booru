      <div>
        <h5>Options</h5>
        <ul>
          <li><a href="" onclick="$('comments').hide(); $('edit').show().scrollTo(); $('post_tags').focus(); Cookie.put('show_defaults_to_edit', 1);; return false;">Edit</a></li>
          <!-- <?php if(!$post->is_deleted() && $post->is_image() && $post->width > 700): ?>
            <li><?php //link_to_function "Resize image", "Post.resize_image()" ?></li>
          <?php endif ?> -->
<?php
if($post->is_image() && $post->can_be_seen_by()):
  if ($post->has_jpeg()) {
    $url = $post->jpeg_url;
    $size = $post->jpeg_size;
    $ext = "JPG";
  } else {
    $url = $post->file_url;
    $size = $post->file_size;
    $ext = strtoupper($post->file_ext);
  }
  $class_file = $post->has_sample() ? "original-file-changed" : "original-file-unchanged";
?>
  <?php if ($post->use_sample()): ?>
        <li><a href="<?php echo $url ?>" class="<?php echo $class_file ?>" id="highres-show" link_width="<?php echo $post->width ?>" link_height="<?php echo $post->height ?>" onclick="Post.highres(); return false">View larger version</a></li>
  <?php endif ?>
        <li><a href="<?php echo $post->jpeg_url ?>" class="<?php echo $class_file ?>" id="highres"><?php echo $post->has_sample()?"Download larger version":"Image" ?> (<?php echo number_to_human_size($size).' '.$ext ?>)</a></li>
  <?php if ($post->has_jpeg()): ?>
        <?php # If we have a JPEG, the above link was the JPEG.  Link to the PNG here. ?>
        <li><a href="<?php echo $post->file_url ?>" class="original-file-unchanged" id="png">Download PNG (<?php echo number_to_human_size($post->file_size) ?>)</a></li>
  <?php endif ?>
<?php endif ?>
<?php if($post->can_user_delete()): ?>
          <li><a href="/post/delete/<?php echo $post->id ?>">Delete</a></li>
<?php endif ?>
<?php if($post->is_deleted() && User::$_->is('>=35')): ?>
          <li><a href="/post/undelete/<?php echo $post->id ?>">Undelete</a></li>
<?php endif ?>
<?php if((!$post->is_flagged() || !$post->is_deleted())): ?>
          <li><a href="" onclick="User.run_login(false, function() { Post.flag(<?php echo $post->id ?>, function() { window.location.reload(); }) }); return false;">Flag for deletion</a></li>
<?php endif ?>
<?php if(!$post->is_deleted() && $post->is_image() && !$post->is_note_locked): ?>
          <li><a href="" onclick="User.run_login(false, function() { Note.create(<?php echo $post->id ?>) }); return false;">Add translation</a></li>
<?php endif ?>
          <li id="add-to-favs"><a href="" onclick="Post.vote(<?php echo $post->id ?>, 3); return false;">Add to favorites</a></li>
          <li id="remove-from-favs"><a href="" onclick="Post.vote(<?php echo $post->id ?>, 0); return false;">Remove from Favorites</a></li>
<?php if($post->is_pending() && User::$_->is('>=35')): ?>
          <li><a href="" onclick="if (confirm('Do you really want to approve this post?')) {Post.approve(<?php echo $post->id ?>)} return false;"></a></li>
<?php endif ?>
<?php if(!$post->is_deleted()): ?>
          <li id="add-to-pool" class="advanced-editing"><a href="#" onclick="new Ajax.Updater('add-to-pool', '/pool/select?post_id=<?php echo $post->id ?>', {asynchronous:true, evalScripts:true, method:'get'}); return false;">Add to pool</a></li>
<?php endif ?>
          <li id="set-avatar"><a href="/user/set_avatar/<?php echo $post->id ?>">Set avatar</a></li>
          <li><a href="">Post history</a></li>
        </ul>
      </div>
