<?php do_content_for('post_cookie_javascripts') ?>
  <script type="text/javascript">
    var def = [];
    
    var account_menu = [];
<?php if(User::$current->is_anonymous) : ?>
    account_menu.push({"dest":"/user/login","label":"Login","login":true,"class_names":["login-button"]});
    account_menu.push({"dest":"/user/reset_password","label":"Reset&nbsp;Password","class_names":[]});
<?php ;else: ?>
    account_menu.push({"dest":"/user/logout?from=<?php echo str_replace('/', '%2F', Request::$url) ?>","label":"Logout","class_names":[]});

    if(Cookie.get("user_id"))
    {
      var profile_item = {"dest":"/user/show","label":"My&nbsp;Profile","class_names":[]};
      profile_item.dest += "/" + Cookie.get("user_id")
      account_menu.push(profile_item);
    }

    account_menu.push({"dest":"/dmail/inbox","label":"My&nbsp;Mail","class_names":[]});

    if(Cookie.get("login"))
    {
      var favorites_item = {"dest":"/post?tags=order%3Avote+vote%3A3%3A","label":"My&nbsp;Favorites","class_names":["current-menu"]};
      if(User.get_use_browser())
        favorites_item.dest ="/post/browse#/order:vote vote:3:" + Cookie.get("login");
      else
        favorites_item.dest += Cookie.get("login")
      account_menu.push(favorites_item);
    }

    account_menu.push({"dest":"/user/edit","label":"Settings","class_names":[]});
    account_menu.push({"dest":"/user/change_password","label":"Change&nbsp;Password","class_names":[]});
<?php endif ?>

    def.push({"class_names":[],"label":"My&nbsp;Account","dest":"/user/home","name":"my_account","login":true});
    def[def.length-1].sub = account_menu;

    var posts_menu = [];
    posts_menu.push({"class_names":["current-menu"],"label":"View posts","dest":"/post"});
    posts_menu.push({"class_names":["current-menu"],"label":"Search&nbsp;posts","dest":"/post"});
    posts_menu[posts_menu.length-1].func = ShowPostSearch;
    posts_menu.push({"class_names":["current-menu"],"label":"Upload","dest":"/post/upload"});
    /* {"class_names":["current-menu"],"label":"Subscriptions","dest":"/post"}, */
    <!-- <li id="my-subscriptions-container"><a href="/" id="my-subscriptions">Subscriptions</a> -->
    posts_menu.push({"class_names":["current-menu"],"label":"Random","dest":"/post?tags=order%3Arandom"});
    posts_menu.push({"class_names":["current-menu"],"label":"Popular","dest":"/post/popular_recent"});
    posts_menu.push({"class_names":["current-menu"],"label":"Image&nbsp;Search","dest":"/post/similar"});
    posts_menu.push({"class_names":[],"label":"History","dest":"/history"});
<?php if(User::$_->is('==50')): ?>
    posts_menu.push({"class_names":["current-menu"],"label":"Import","dest":"/post/import"});
<?php endif ?>
<?php if(User::$_->is('>=35')): ?>
    posts_menu.push({"class_names":["current-menu"],"label":"Moderate","dest":"/post/moderate"});

    var posts_flagged = Cookie.get("posts_flagged");
    if (posts_flagged && parseInt(posts_flagged) > "0") {
      posts_menu[posts_menu.length-1].label += " (" + posts_flagged + ")";
      posts_menu[posts_menu.length-1].class_names = ["bolded"];
    }
<?php endif ?>
    def.push({"class_names":["current-menu"],"label":"Posts","dest":"/post","name":"posts"});
    def[def.length-1].sub = posts_menu;

    var comments_menu = [];
    comments_menu.push({"class_names":[],"label":"View comments","dest":"/comment"});
    comments_menu.push({"class_names":[],"label":"Search comments","dest":"/comment/search"});
    comments_menu[comments_menu.length-1].func = ShowCommentSearch;
<?php if(User::$_->is('>=35')): ?>
    comments_menu.push({"class_names":[],"label":"Moderate","dest":"/comment/moderate"});
<?php endif ?>
    def.push({"class_names":[],"label":"Comments","html_id":"comments-link","dest":"/comment","name":"comments"});
    if (Cookie.get("comments_updated") == "1") {
      def[def.length-1].class_names = ["bolded"];
    }
    def[def.length-1].sub = comments_menu;

    var notes_menu = [];
    notes_menu.push({"class_names":[],"label":"View notes","dest":"/note"});
    notes_menu.push({"class_names":[],"label":"Search notes","dest":"/note/search"});
    notes_menu[notes_menu.length-1].func = ShowNoteSearch;
    notes_menu.push({"class_names":[],"label":"Requests","dest":"/posts?tags=translation_request"});
    def.push({"class_names":[],"label":"Notes","dest":"/note","name":"notes"});
    def[def.length-1].sub = notes_menu;

<?php if(CONFIG::enable_artists) : ?>
    var artists_menu = [];
    artists_menu.push({"class_names":[],"label":"View artists","dest":"/artist"});
    artists_menu.push({"class_names":[],"label":"Search artists","dest":"/artist"});
    artists_menu[artists_menu.length-1].func = ShowArtistSearch;
    artists_menu.push({"class_names":[],"label":"Create","dest":"/artist/create"});
    def.push({"class_names":[],"label":"Artists","dest":"/artist","name":"artists"});
    def[def.length-1].sub = artists_menu;
<?php endif ?>

    var tags_menu = [];
    tags_menu.push({"class_names":[],"label":"View tags","dest":"/tag"});
    tags_menu.push({"class_names":[],"label":"Search tags","dest":"/tag"});
    tags_menu[tags_menu.length-1].func = ShowTagSearch;
    tags_menu.push({"class_names":[],"label":"Popular","dest":"/tag/popular_by_day"});
    tags_menu.push({"class_names":[],"label":"Aliases","dest":"/tag_alias"});
    tags_menu.push({"class_names":[],"label":"Implications","dest":"/tag_implication"});
<?php if(User::$_->is('>=40')) : ?>
    tags_menu.push({"class_names":[],"label":"Mass edit","dest":"/tag/mass_edit"});
<?php endif ?>
    tags_menu.push({"class_names":[],"label":"Edit","dest":"/tag/edit"});
    def.push({"class_names":[],"label":"Tags","dest":"/tag?order=date","name":"tags"});
    def[def.length-1].sub = tags_menu;

    var pools_menu = [];
    pools_menu.push({"class_names":[],"label":"View pools","dest":"/pool"});
    pools_menu.push({"class_names":[],"label":"Search pools","dest":"/pool"});
    pools_menu[pools_menu.length-1].func = ShowPoolSearch;
    pools_menu.push({"class_names":[],"label":"Create&nbsp;new&nbsp;pool","dest":"/pool/create"});
    def.push({"class_names":[],"label":"Pools","dest":"/pool","name":"pools"});
    def[def.length-1].sub = pools_menu;

    var wiki_menu = [];
    wiki_menu.push({"class_names":[],"label":"View wiki index","dest":"/wiki"});
    wiki_menu.push({"class_names":[],"label":"Search wiki","dest":"/wiki"});
    wiki_menu[wiki_menu.length-1].func = ShowWikiSearch;
    wiki_menu.push({"class_names":[],"label":"Create&nbsp;new&nbsp;page","dest":"/wiki/add"});
    def.push({"class_names":[],"label":"Wiki","dest":"/wiki/show?title=help%3Ahome","name":"wiki"});
    def[def.length-1].sub = wiki_menu;

    var forum_menu = [];
    forum_menu.push({"class_names":[],"label":"View topics","dest":"/forum"});
    forum_menu.push({"class_names":[],"label":"Search forums","dest":"/forum"});
    forum_menu[forum_menu.length-1].func = ShowForumSearch;
    forum_menu.push({"class_names":[],"label":"New&nbsp;topic","dest":"/forum/new"});
    def.push({"class_names":[],"label":"Forum","html_id":"forum-link","dest":"/forum","name":"forum"});
    if (Cookie.get("forum_updated") == "1") {
      def[def.length-1].class_names = ["bolded"];
    }
    def[def.length-1].sub = forum_menu;

    var help_menu = [];
<?php if(in_array(Request::$controller, array('post', 'comment', 'note', 'artist', 'tag', 'wiki', 'pool', 'forum'))) : ?>
    help_menu.push({"class_names":[],"label":"<?php echo ucfirst(Request::$controller) ?> Help","dest":"/help/<?php echo Request::$controller ?>"});
<?php endif ?>
    help_menu.push({"class_names":[],"label":"Site Help","dest":"/help"});
    def.push({"class_names":[],"label":"Help","dest":"/help","name":"help"});
    def[def.length-1].sub = help_menu;

    def.push({"class_names":[],"label":"More&nbsp;&raquo;","dest":"/static/more","name":"more"});
  
<?php
/*
    account_menu.push({"dest":"/admin","label":"Admin tools","class_names":[]});
    posts_menu.push({"class_names":["current-menu"],"label":"Batch upload","dest":"/post/upload/batch"});
*/
?>
  </script>

<?php end_content_for() ?>

<?php
$cm = array('user' => '', 'post' => '', 'comment' => '', 'note' => '', 'artist' => '', 'tag' => '', 'pool' => '', 'wiki' => '', 'forum' => '', 'help' => '', 'static' => '');
$cm[Request::$controller] = " current-menu";
?>
  <div id="main-menu">
    <div class='menu top-item-my_account<?php echo $cm['user'] ?>'><a href="/user/home" onclick='if(!User.run_login_onclick(event)) return false;'>My Account</a></div>
    <div class='menu top-item-posts<?php echo $cm['post'] ?>'><a href="/post" >Posts</a></div>
    <div id='comments-link' class='menu top-item-comments<?php echo $cm['comment'] ?>'><a href="/comment" >Comments</a></div>
    <div class='menu top-item-notes<?php echo $cm['note'] ?>'><a href="/note" >Notes</a></div>
    <div class='menu top-item-artists<?php echo $cm['artist'] ?>'><a href="#" >Artists</a></div>
    <div class='menu top-item-tags<?php echo $cm['tag'] ?>'><a href="/tag" >Tags</a></div>
    <div class='menu top-item-pools<?php echo $cm['pool'] ?>'><a href="/pool" >Pools</a></div>
    <div class='menu top-item-wiki<?php echo $cm['wiki'] ?>'><a href="#" >Wiki</a></div>
    <div id='forum-link' class='menu top-item-forum<?php echo $cm['forum'] ?>'><a href="#" >Forum</a></div>
    <div class='menu top-item-help<?php echo $cm['help'] ?>'><a href="#" >Help</a></div>
    <div class='menu top-item-more<?php echo $cm['static'] ?>'><a href="#" >More &raquo;</a></div>
  </div>
  
<?php do_content_for("post_cookie_javascripts") ?>
  <script type="text/javascript">
    var main_menu = new MainMenu($("main-menu"), def);
    main_menu.add_forum_posts_to_submenu();
    main_menu.init();
    $('cn').show();
  </script>
  
<?php end_content_for() ?>