<?php //body, translated_from_languages = comment.get_translated_formatted_body(@current_user.language, @current_user.secondary_language_array) ?>
<?php //if not translated_from_languages.empty? then @page_uses_translations = true end ?>

<div class="comment avatar-container" id="c<?php echo $comment->id ?>">
  <div class="author">
    <?php if (!empty($comment->user_id)) : ?>
      <h6><a href="/user/show/<?php echo $comment->user_id ?>"><?php echo h($comment->pretty_author()) ?></a></h6>
    <?php ;else: ?>
      <h6><?php echo h($comment->pretty_author()) ?></h6>
    <?php endif ?>
    <span class="date" title="Posted at <?php //echo $comment->created_at.strftime('%c') ?>">
      <?php echo link_to(time_ago_in_words($comment->created_at) . " ago", array('post#show', 'id' => $comment->post_id, 'anchor' => ("c{$comment->id}"))) ?>
    </span>
    <?php if (2 === 3) ://if not translated_from_languages.empty? then ?>
      <span class="translated-notice">translated</span>
    <?php endif ?>
    <?php if ($comment->user and $comment->user->has_avatar()) : ?>
      <?php Comment::avatar_post_reg($comment->user->avatar_post) ?>
      <div class="comment-avatar-container"> <?php echo avatar($comment->user, $comment->id) ?> </div>
    <?php endif ?>
  </div>
  <div class="content">
    <div class="body">
      <?php echo format_inlines(format_text($comment->body, array('mode' => 'comment')), $comment->id) ?>
      <?php //echo $body ?>
    </div>
    <?php if (2===3) ://if not translated_from_languages.empty? then ?>
    <div class="body untranslated-body" style="display: none;">
      <?php //echo comment.get_formatted_body ?>
    </div>
    <?php endif ?>
    <div class="post-footer" style="clear: left;">
      <ul class="flat-list pipe-list">
        <li> <a href="#" onclick="Comment.quote(<?php echo $comment->id ?>); return false;">Quote</a>
        <?php if (User::$current->has_permission($comment)) : ?>
          <li> <?php echo link_to("Edit", array('comment#edit', 'id' => $comment->id)) ?>
          <li> <a href="#" onclick="Comment.destroy(<?php echo $comment->id ?>); return false;">Delete</a>
        <?php ;else: ?>
          <li> <a href="#" onclick="Comment.flag(<?php echo $comment->id ?>); return false;">Flag for deletion</a>
        <?php endif ?>
        <?php if (2===3) : //if not translated_from_languages.empty? then ?>
          <li class="show-translated"> <?php //echo link_to_function "View untranslated post", "Comment.show_translated(#{comment.id}, false)" ?>
          <li class="show-untranslated" style="display: none;"> <?php //echo link_to_function "View translated post", "Comment.show_translated(#{comment.id}, true)" ?>
        <?php endif ?>
      </ul>
    </div>
  </div>
</div>

