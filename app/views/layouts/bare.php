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

  <?php content_for('html_header') ?>
</head>
<body>
  <?php render_partial("layouts/notice") ?>
  <div id="content">
    <?php content_for("layout") ?>
  </div>
  <?php content_for('post_cookie_javascripts') ?>
</body>
</html>
