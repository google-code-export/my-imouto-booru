<?php if ($user->has_avatar()) : ?>
  <div style="width: 25em; height: <?php echo max($user->avatar_height, 80) ?>px; position: relative;">
    <div style="position: absolute; bottom: 0;">
      <?php echo avatar($user, 1) ?>
    </div>
    <div style="position: absolute; bottom: 0; margin-bottom: 15px; left: <?php echo $user->avatar_width+5 ?>px; ">
      <?php if (User::$current->has_permission($user)) : ?>
        &nbsp;<?php echo link_to("(edit)", array('#set_avatar', 'id' => $user->avatar_post->id, 'user_id' => $user->id)) ?>
      <?php endif ?>
      <h2><?php echo h($user->pretty_name()) ?></h2>
    </div>
  </div>
<?php ;else: ?>
  <h2><?php echo h($user->pretty_name()) ?></h2>
<?php endif ?>

<div style="float: left; width: 25em; clear: left;">
  <table width="100%">
    <tr>
      <td width="40%"><strong>Join Date</strong></td>
      <td width="60%"><?php echo substr($user->created_at, 0, 10) ?>
      </td>

    </tr>
    <?php if ($user->level < 20 or $user->level > 33 or User::is('>=40')) : ?>
    <tr>
      <td><strong>Level</strong></td>
      <td>
        <?php echo $user->pretty_level() ?>
        <?php if ($user->is_blocked() && $user->ban): ?>
          (reason: <?php echo h($user->ban->reason) ?>; expires: <?php echo substr($user->ban->expires_at, 0, 10) ?>)
        <?php endif ?>
      </td>
    </tr>
    <?php endif ?>
<?php /*
    <tr>
      <td><strong>Tag Subscriptions</strong></td>
      <td class="large-text">
<?php render_partial("tag_subscription/user_listing", array('user' => $user)) ?>
      </td>
    </tr>
*/ ?>
    <tr>
      <td><strong>Posts</strong></td>
      <td><a href="/post?tags=user%3A<?php echo $user->name ?>"><?php echo Post::count(array('conditions' => array("user_id = ?", $user->id))) ?></a></td>
    </tr>
    <tr>
      <td><strong>Deleted Posts</strong></td>
      <td><a href="/post/deleted_index?user_id=<?php echo $user->id ?>"><?php echo Post::count(array('conditions' => array("status = 'deleted' AND user_id = ?", $user->id))) ?></a></td>
    </tr>
    <tr>
      <th>Votes</th>
      <td>
        <span class="stars">
          <?php foreach(range(1, 3) as $i) : ?>
            <a class="star star-<?php echo $i ?>" href="<?php echo url_for('post#index', array('tags' => "vote:>=${i}:{$user->name} order:vote")) ?>">
              <?php echo PostVotes::count(array('conditions' => array("user_id = {$user->id} AND score = $i"))) ?>
              <span class="score-on score-voted score-visible">★</span>
            </a>
          <?php endforeach ?>
        </span>
      </td>
    </tr>
    <tr>
      <td><strong>Comments</strong></td>
      <td><?php echo link_to(Comment::count(array('conditions' => "user_id = {$user->id}")), array('comment#search', 'query' => "user:{$user->name}")) ?></td>
    </tr>
    <tr>
      <td><strong>Edits</strong></td>
      <td>0<?php //echo link_to History.count(:all, :conditions => "user_id = #{@user.id}"), :controller => "history", :action => "index", :search => "user:#{@user.name}" ?></td>
    </tr>
    <tr>
      <td><strong>Tag Edits</strong></td>
      <td>0<?php //echo link_to History.count(:all, :conditions => "user_id = #{@user.id} AND group_by_table = 'posts'"), :controller => "history", :action => "post", :search => "user:#{@user.name}" ?></td>
    </tr>
    <tr>
      <td><strong>Note Edits</strong></td>
      <td><?php echo link_to(NoteVersion::count(array('conditions' => "user_id = {$user->id}"), array('note#history', 'user_id' => $user->id))) ?></td>
    </tr>
    <tr>
      <td><strong>Wiki Edits</strong></td>
      <td>0<?php //echo link_to WikiPageVersion.count(:all, :conditions => "user_id = #{@user.id}"), :controller => "wiki", :action => "recent_changes", :user_id => @user.id ?></td>
    </tr>
    <tr>
      <td><strong>Forum Posts</strong></td>
      <td>0<?php //echo ForumPost.count(:all, :conditions => "creator_id = #{@user.id}") ?></td>
    </tr>
    <?php
      if (!empty($user->invited_by)) :
        $u = new User('find', $user->invited_by);
    ?>
      <tr>
        <td><strong>Invited By</strong></td>
        <td><?php echo link_to(h($u->name), array('#show', 'id' => $user->invited_by)) ?></td>
      </tr>
    <?php endif ?>
    <?php if (CONFIG::starting_level < 30) : ?>
    <tr>
      <td><strong>Recent Invites</strong></td>
      <td><?php //echo User.find(:all, :conditions => ["invited_by = ?", @user.id], :order => "id desc", :select => "name, id", :limit => 5).map {|x| link_to(h(x.pretty_name), :action => "show", :id => x.id)}.join(", ") ?></td>
    </tr>
    <?php endif ?>
    <tr>
      <td><strong>Record</strong></td>
      <td>
        <?php if (true) : //!UserRecord.exists?(["user_id = ?", @user.id]) ?>
          None
        <?php ;else: ?>
          <?php //echo UserRecord.count(:all, :conditions => ["user_id = ? AND is_positive = true", @user.id]) - UserRecord.count(:all, :conditions => ["user_id = ? AND is_positive = false", @user.id]) ?>
        <?php endif ?>
        (<a href="/user_record?user_id=<?php echo $user->id ?>">add</a>)
      </td>
    </tr>
    <?php if (User::is('>=40')) : ?>
      <tr>
        <td><strong>IPs</strong></td>
        <td>
          <?php //@user_ips[0,5].each do |ip| ?>
          <?php //echo ip ?>
          <?php //end ?>
          <?php //if @user_ips.length > 5 ?>(more)<?php //end ?>
        </td>
      </tr>
    <?php endif ?>
  </table>
</div>


<div style="float: left; width: 60em;">
  <table width="100%">
    <?php foreach ($tag_types as $name => $value) : ?>
    <?php //CONFIG["tag_types"].select {|k, v| k =~ /^[A-Z]/ && k != "General" && k != "Faults"}.each do |name, value| ?>
      <tr>
        <th>Favorite <?php echo $name . 's' ?></th>
        <td><?php echo implode(', ', array_map(function($tag) use ($user){ return link_to(h(str_replace('_', ' ', $tag["tag"])), array('post#index', 'tags' => "vote:3:{$user->name} {$tag['tag']} order:vote"));}, $user->voted_tags(array('type' => $value))))?></td>
      </tr>
    <?php endforeach ?>
    <tr>
      <th>Uploaded Tags</th>
      <td><?php echo implode(', ', array_map(function($tag) use ($user){ return link_to(h(str_replace('_', ' ', $tag["tag"])), array('post#index', 'tags' => "user:{$user->name} {$tag['tag']}"));}, $user->uploaded_tags())) ?></td>
    </tr>
    <?php foreach ($tag_types as $name => $value) : ?>
      <tr>
        <th>Uploaded <?php echo $name . 's' ?></th>
        <td><?php echo implode(', ', array_map(function($tag) use ($user){ return link_to(h(str_replace('_', ' ', $tag["tag"])), array('post#index', 'tags' => "user:{$user->name} {$tag['tag']}"));}, $user->uploaded_tags(array('type' => $value))))
        //@user.uploaded_tags(:type => value).map {|tag| link_to h(tag["tag"].tr("_", " ")), :controller => "post", :action => "index", :tags => "user:#{@user.name} #{tag['tag']}"}.join(", ")?></td>
      </tr>
    <?php endforeach ?>
  </table>
</div>

<?php /*
<?php @user.tag_subscriptions.visible.each do |tag_subscription| ?>
  <div style="margin-bottom: 1em; float: left; clear: both;">
    <h4>Tag Subscription: <?php echo h tag_subscription.name ?> <?php echo link_to "&raquo;", :controller => "post", :action => "index", :tags => "sub:#{@user.name}:#{tag_subscription.name}" ?></h4>
    <?php echo render :partial => "post/posts", :locals => {:posts => @user.tag_subscription_posts(5, tag_subscription.name).select {|x| CONFIG["can_see_post"].call(@current_user, x)}} ?>
  </div>
<?php end ?>
*/ ?>

<div style="margin-bottom: 1em; float: left; clear: both;">
  <h4><a href="/post?tags=vote%3A3%3A<?php echo $user->name ?>+order%3Avote">Favorites</a></h4>
  <?php //$posts = array_map(function($x){if (CONFIG::can_see_post(User::$current, $x)) return $x;}, (array)$user->recent_favorite_posts()); ?>
  <?php render_partial("post/posts", array('posts' => array_map(function($x){if (CONFIG::can_see_post(User::$current, $x)) return $x;}, (array)$user->recent_favorite_posts()))) ?>
  <?php //unset($posts) ?>
</div>

<div style="margin-bottom: 1em;  float: left; clear: both;">
  <h4><a href="/post?tags=user%3A<?php echo $user->name ?>">Uploads</a></h4>
  <?php //$posts = array_map(function($x){if (CONFIG::can_see_post(User::$current, $x)) return $x;}, (array)$user->recent_uploaded_posts()); ?>
  <?php render_partial("post/posts", array('posts' => array_map(function($x){if (CONFIG::can_see_post(User::$current, $x)) return $x;}, (array)$user->recent_uploaded_posts()))) ?>    
</div>

<?php do_content_for("footer") ?>
    <li><a href="/user">List</a></li>
<?php if (User::is('>=40')) : ?>
    <li><a href="/user/block?id=<?php echo $user->id ?>">Ban</a></li>
<?php endif ?>
<?php if (User::is('>=35') && $user->level <= 30) : ?>
    <li><a href="/user/invites?name=<?php echo $user->name ?>">Invite</a></li>
<?php endif ?>
    <li><a href="/dmail/compose?to=<?php echo $user->name ?>">Send Message</a></li>
<?php end_content_for() ?>

<?php echo render_partial("footer") ?>
