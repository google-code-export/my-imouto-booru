<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
"http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
  <title>System</title>
  <style type="text/css">
    a {
      text-decoration:none;
    }
    a:hover {
      text-decoration:underline;
    }
    #notice {
      border:1px solid #fa0;
      padding:3px;
      width:800px;
      background-color:#fffcd7;
    }
    a:visited {
      color:#00c;
    }
  </style>
</head>
<body>
<?php if (!empty($_GET['n'])) : ?>
  <div id="notice"><?php echo $_GET['n'] ?></div>
<?php endif ?>

<?php echo $body ?>
</body>
</html>