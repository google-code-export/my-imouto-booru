<?php if(!CONFIG::show_homepage) redirect_to('/post') ?>
<?php $post_count = Post::get_row_count() ?>
<div id="static-index">
  <h1 id="static-index-header"><a href=""><?php echo CONFIG::app_name ?></a></h1>
  <div style="margin-bottom: 1em;" id="links">
    <a href="/post" title="A paginated list of every post">Posts</a>
    <a href="/comment" title="A paginated list of every comment">Comments</a>
    <a href="/tag" title="A paginated list of every tag">Tags</a>
    <a href="/wiki" title="Wiki">Wiki</a>
    <a href="/static/more" title="A site map">&raquo;</a>
  </div>
  <div style="margin-bottom: 2em;">
    <form action="/post" method="get">
      <div>
        <input id="tags" name="tags" size="30" type="text" value=""><br>
        <input name="searchDefault" type="submit" value="Search">        
      </div>
    </form>
  </div>
  <?php echo numbers_to_imoutos($post_count) ?>
  <div style="font-size: 80%; margin-bottom: 2em;">
    <p>
      <script type="text/javascript">eval(decodeURIComponent('%64%6f%63%75%6d%65%6e%74%2e%77%72%69%74%65%28%27%3c%61%20%68%72%65%66%3d%22%68%74%74%70%3a%2f%2f%70%6f%70%2d%77%6f%72%6b%73%2e%62%6c%6f%67%73%70%6f%74%2e%63%6f%6d%22%3e%43%6f%6e%74%61%63%74%3c%2f%61%3e%27%29%3b'))</script> &ndash; 
    
      Serving <?php echo $post_count ?> posts &ndash; Running MyImouto <?php echo CONFIG::version ?>
    </p>
  </div>
</div>