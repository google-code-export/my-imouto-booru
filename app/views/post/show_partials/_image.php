<?php if($post->is_deleted()) return; ?>
        <div ondblclick="Post.resize_image(); return false;">
<?php if(!$post->can_be_seen_by()): ?>
          <p>You need a privileged account to see this image.</p>
<?php ;elseif($post->is_image()): ?>
          <div id="note-container">
  <?php foreach($post->active_notes() as $note): ?>
          <div class="note-box" style="width: <?php echo $note->width ?>px; height: <?php echo $note->height ?>px; top: <?php echo $note->y ?>px; left: <?php echo $note->x ?>px;" id="note-box-<?php echo $note->id ?>">
            <div class="note-corner" id="note-corner-<?php echo $note->id ?>"></div>
          </div>
          <div class="note-body" id="note-body-<?php echo $note->id ?>" title="Click to edit"><?php echo str_replace('\n', '<br />', $note->body) ?></div>
  <?php endforeach ?>
          </div>
<?php
$file_sample = $post->get_file_sample(User::$current);
$large_width = $file_sample['width'];
$large_height = $file_sample['height'];
?>

          <img id="image" class="image" alt="<?php echo $post->tags ?>" width="<?php echo $file_sample['width'] ?>" height="<?php echo $file_sample['height'] ?>" id="image" large_height="<?php echo $large_height ?>" large_width="<?php echo $large_width ?>" src="<?php echo $file_sample['url'] ?>" />
<?php ;elseif($post->is_flash()): ?>
        <object width="<?php echo $post->width ?>" height="<?php echo $post->height ?>">
          <param name="movie" value="<?php echo $post->file_url ?>">
          <embed src="<?php echo $post->file_url ?>" width="<?php echo $post->width ?>" height="<?php echo $post->height ?>" allowScriptAccess="never"></embed>
        </object>

        <p><?php echo link_to("Save this flash (right click and save)", $post->file_url) ?></p>
<?php ;else: ?>
          <h2><a href="<?php echo $post->file_url ?>">Download</a></h2>
          <p>You must download this file manually.</p>
<?php endif ?>
        </div>
        <div style="margin-bottom: 1em;">
          <p id="note-count"></p>
          <script type="text/javascript">
            Note.post_id = <?php echo $post->id ?>

<?php foreach($post->active_notes() as $note): ?>
            Note.all.push(new Note(<?php echo $note->id ?>, false, '<?php echo h($note->body) ?>'))
<?php endforeach ?>

            Note.updateNoteCount()
            Note.show()

            new WindowDragElement($("image"));

            $("image").observe("click", function(e) { if(!e.stopped) Note.toggle(); }.bindAsEventListener());
          </script>
        </div>

