<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
"http://www.w3.org/TR/html4/loose.dtd">
<html class="action-<?php echo Request::$controller ?> action-<?php echo Request::$controller . '-' . Request::$action ?> hide-advanced-editing">
<head>
  <title><?php echo page_title() ?></title>
  <link rel="shortcut icon" href="/favicon.ico" type="image/x-icon">
  <link rel="top" title="<?php echo CONFIG::app_name ?>" href="/">

  
  <script type="text/javascript">
    var css = ".javascript-hide { display: none !important; }";
    var style = document.createElement("style"); style.type = "text/css";
    if(style.styleSheet) // IE
      style.styleSheet.cssText = css;
    else
      style.appendChild(document.createTextNode(css));
    document.getElementsByTagName("head")[0].appendChild(style);
  </script>

  <link href="/stylesheets/default.css" media="screen" rel="stylesheet" type="text/css">
  <script src="/javascripts/application.js" type="text/javascript"></script>
<?php
if ( !isset($browse_mode) ) {
?>
  <!--[if lt IE 8]>
  <script src="/IE8.js" type="text/javascript"></script>
  <![endif]-->
  
  <!--[if lt IE 7]>
    <style type="text/css">
      body div#post-view > div#right-col > div > div#note-container > div.note-body {
        overflow: visible;
      }
    </style>
    <script src="http://ie7-js.googlecode.com/svn/trunk/lib/IE7.js" type="text/javascript"></script>
  <![endif]-->
<?php
} else {
?>
  <meta name="apple-mobile-web-app-capable" content="yes" >  
  <meta name="apple-mobile-web-app-status-bar-style" content="black">
  <meta name="application-name" content="oreno imouto"> 
  <meta name="application-url" content="/post/browse">

  <link rel="apple-touch-icon" href="/images/iphone-icon.png">
  <style type="text/css">
    /* iPhone tends to highlight things oddly, sometimes even highlighting the
     * entire page on swipe.  Turn tap highlighting off. */
    * { -webkit-tap-highlight-color: rgba(0,0,0,0); }
  </style>
<?php
}
?>
</head>

<body>
<?php
// if ( !isset($browse_mode) ) {
?>
  <div id="header">
    <h2 id="site-title"><?php echo link_to(CONFIG::app_name, '/', array('id' => 'ruu')) ?><span><?php echo isset(Request::$params->tags)?tag_header(h(Request::$params->tags)):null; ?></span></h2>
    <?php render_partial('layouts/menu') ?>
  </div>
  <?php render_partial('layouts/login') ?>
  
  <!--[if lt IE 7]>
  <div style="display: none;" id="old-browser">Your browser is very old, and this site will not display properly.
    Please consider upgrading to a more recent web browser:
    <a href="http://www.mozilla.com/firefox/">Firefox</a>,
    <a href="http://www.opera.com/">Opera</a>,
    <a href="http://www.microsoft.com/windows/internet-explorer/download-ie.aspx">Internet Explorer</a>.
    <div style="text-align: right;" id="old-browser-hide">
      <a href="#" onclick='$("old-browser").hide(); Cookie.put("hide-ie-nag", "1");'>(hide this message)</a>
    </div>
  </div>
  <![endif]-->

<?php
// }
?>
  
<?php render_partial('layouts/notice') ?>
<?php if (!isset($browse_mode)) { ?>
  <div class="blocked" id="block-reason" style="display: none;"></div>
<?php }?>

  <div id="content">
<?php content_for('layout') ?>
    <?php if (check_content_for('subnavbar')) : ?>
      <div class="footer">
<?php content_for('above_footer') ?>
        <ul class="flat-list" id="subnavbar">
<?php content_for('subnavbar') ?>
          
        </ul>
      </div>
    <?php endif ?>
  </div>
  
  <script type="text/javascript">
    Cookie.setup()
    InitTextAreas();
    InitAdvancedEditing();
    Post.InitBrowserLinks();
    if(TagCompletion)
      TagCompletion.init(0);
  </script>

  <!--[if lt IE 7]>
    <script type="text/javascript">
      if(Cookie.get("hide-ie-nag") != "1")
        $("old-browser").show();
    </script>
  <![endif]-->
  
  <?php content_for('post_cookie_javascripts') ?>
  
	</body>
</html>