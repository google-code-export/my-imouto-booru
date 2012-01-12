<div id="post-view">
  <div class="sidebar">
    <div>
      <h5>Related Posts</h5>
      <ul>
        <li><?php echo link_to("Previous", "post#show", array('id' => (Request::$params->id - 1))) ?></li>
        <li><?php echo link_to("Next", "post#show", array('id' => (Request::$params->id + 1))) ?></li>
        <li><?php echo link_to("Random", "post#random") ?></li>
      </ul>
    </div>
  </div>
  <div>
    <p>This post does not exist.</p>
  </div>  
</div>
