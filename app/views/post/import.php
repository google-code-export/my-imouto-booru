<?php if (Request::$post) : ?>
  <?php if (!$errors) : ?>
    <?php echo print_preview($post, array('display' => 'large', 'disable_jpeg_direct_links' => true, 'hide_directlink' => true)) ?>
  <?php ;else: ?>
    <li style="width:160px;text-align:center">
      There was an error with<br />
      "<em><?php echo substr(Request::$params->post['filename'], 0, 50); strlen(Request::$params->post['filename']) > 50 && print '...' ?></em>":
      <h6><?php echo $errors ?></h6>
      <a class="directlink largeimg" href="#">
        <span class="directlink-info"><img class="directlink-icon directlink-icon-large" src="/images/ddl_large.gif" alt=""></span>
        <span class="directlink-res">Error</span>
      </a>
    </li>
  <?php endif ?>
  
  <div id="script<?php echo Request::$params->post['i'] ?>" style="display:none">
    Import.set_status(<?php echo Request::$params->post['i'] ?>, '<?php echo $status ?>');
    <?php if ($status != 'Posted') : ?>Import.error_count();<?php endif ?>
    <?php if (!$errors) : ?>Import.ids.push(<?php echo $post->id ?>);<?php endif ?>
    <?php if ($dupe) : ?>Import.dupes.push(<?php echo Request::$params->post['i'] ?>);<?php endif ?>
  </div>
  
<?php return; endif ?>

<h2>Import</h2>

<p id="description">This will move files from the /public/data/import directory.<br />
Data entered here will be applied to all posts.</p>

<div style="margin-bottom:10px;">
  <span id="file-count">Files found: <?php echo count($files) ?></span> / Errors: <span id="error-count"><?php echo count($invalid_files) ?></span>
  <a href="#" id="details-toggle" onclick="$('details-container').toggle();return false;">(Show/hide details)</a>
  
  <div id="details-container" style="border:1px solid #ccc;padding:3px;width:910px;display:none">
    <table class="form" id="posts-details" style="display:none">
      <tbody>
        <tr>
          <th style="width:75px;"><label for="post_source">Source</label></th>
          <td id="detail-source"></td>
        </tr>
        
        <tr>
          <th><label for="post_pool" style="display:block">Pool</label></th>
          <td id="detail-pool"></td>
        </tr>
        
        <tr>
          <th><label for="post_tags">Tags</label></th>
          <td id="detail-tags"></td>
        </tr>
        
        <tr>
          <th><label for="post_rating_questionable">Rating</label></th>
          <td id="detail-rating"></td>
        </tr>
      </tbody>
    </table>
  
    <table id="errors-container" class="form" style="margin-bottom:0px">
      <tr class="import-thead">
        <th style="text-align:left;">File</th>
        <th style="width:100px;text-align:left;">Status</th>
      </tr>
      
      <?php if ($invalid_files):foreach (range(0, count($invalid_files) - 1) as $i): ?>
      <tr id="e<?php echo $invalid_files[$i] ?>" class="<?php echo cycle('even', 'odd') ?>">
        <td><?php echo substr($invalid_files[$i], 0, 105) ?></td>
        <td>Invalid filename</td>
      </tr>
      <?php endforeach;endif ?>
      
      <?php if ($files):foreach(range(0, count($files) - 1) as $i) : ?>
      <tr id="file<?php echo $i ?>" class="<?php echo cycle('even', 'odd') ?>">
        <td><?php echo substr($files[$i], 0, 100); strlen($files[$i]) > 100 && print '...' ?></td>
        <td>Waiting</td>
      </tr>
      <?php endforeach;endif ?>
    </table>
    
    <div id="delete-dupes" style="display:none">
      <a id="delete-dupes-link" href="#">Delete dupes</a>
    </div>
  </div>
</div>

<form action="" id="data-form">
  <div id="posts">
    <table class="form">
      <tfoot>
        <tr>
          <td></td>
          <td>
            <input accesskey="s" class="submit" name="start" style="margin: 0;" tabindex="7" type="submit" value="Start" />
          </td>
        </tr>
      </tfoot>
      <tbody>
        <tr>
          <th><label for="post_source">Source</label></th>
          <td>
            <input id="post_source" name="post[source]" size="50" tabindex="1" type="text" value="" />
          </td>
        </tr>
        <tr>
          <th><label for="post_pool" style="display:block">Pool</label></th>
          <td>
            <input id="post_pool" type="text" list="pool_list" size="50" tabindex="2" name="post[pool]" value="" />
            <?php echo $pool_list ?>
          </td>
        </tr>
        <tr>
          <th><label for="post_tags">Tags</label></th>
          <td>
            <textarea cols="60" id="post_tags" name="post[tags]" rows="2" tabindex="3"></textarea>
          </td>
        </tr>
        
        
        <tr>
          <th><label for="post_rating_questionable">Rating</label></th>
          <td>
            <input id="post_rating_explicit" name="post[rating]" type="radio" value="e" tabindex="4"<?php echo $chkbox_e ?>>
            <label for="post_rating_explicit">Explicit</label>

            <input id="post_rating_questionable" name="post[rating]" type="radio" value="q" tabindex="5"<?php echo $chkbox_q ?>>
            <label for="post_rating_questionable">Questionable</label>

            <input id="post_rating_safe" name="post[rating]" type="radio" value="s" tabindex="6"<?php echo $chkbox_s ?>>
            <label for="post_rating_safe">Safe</label>
          </td>
        </tr>
      </tbody>
    </table>

    <div id="related"><em>None</em></div>
  </div>
</form>

<div id="post-list">
  <ul id="post-list-posts"></ul>
</div>
<?php //render_partial('hover') ?>

<script>RelatedTags.init(Cookie.get('my_tags'), '')</script>

<script>
files = [<?php if ($files) echo "'" . implode("', '", $files) . "'" ?>]
url = '/post/import'

Import = {
  importing   : 0,
  busy        : false,
  file_status : null,
  dupes       : [],
  ids         : [],
  pool        : null,
  e_count     : <?php echo count($invalid_files) ?>,
  
  start: function() {
    this.busy = true
    $('description').hide()
    $('posts-details').show()
    
    rating = this.get_rating()
    
    this.post_data = {
      'post[tags]'  : $('post_tags').value,
      'post[source]': $('post_source').value,
      'post[rating]': rating
    }
    
    $('detail-source').innerHTML  = $('post_source').value || '<em>None</em>'
    $('detail-pool').innerHTML    = $('post_pool').value || '<em>None</em>'
    $('detail-tags').innerHTML    = $('post_tags').value || '<em>None</em>'
    $('detail-rating').innerHTML  = this.get_rating(true)
    
    if ($('post_pool').value)
      this.pool = $('post_pool').value.toLowerCase().replace(/ /g, '_')
    
    // alert(this.pool)
    
    $('data-form').remove()
    this.send_request()
  },
  
  send_request: function(){
    i = this.importing
    
    post = this.post_data
    
    if (i == (files.length))
      return;
    
    $('file-count').innerHTML = 'Converting ' + (i+1) + '/' + files.length
    this.set_status(i, 'Importing')
    
    file = files[i]
    
    post['post[filename]'] = file
    post['post[i]'] = i
    
    new Ajax.Updater('post-list-posts', url, {
      parameters: post,
      method:'post',
      insertion: Insertion.Bottom,
      onSuccess: function(){
        Import.delayed(i)
        Import.send_request()
      }
    })
    
    Import.importing++
  },
  
  delayed: function(i){
    setTimeout(function(){
      scriptid = 'script'
      scriptid = scriptid + i
      eval($(scriptid).innerHTML)
      
      if (files.length - 1 == i) {
        Import.add_to_pool()
      }
      
    }, 100)
  },
  
  get_rating: function(proper) {
    if ($('post_rating_explicit').checked)
      return proper ? 'Explicit' : 'e';
    else if ($('post_rating_questionable').checked)
      return proper ? 'Questionable' : 'q';
    else
      return proper ? 'Safe' : 's';
  },
  
  set_status: function(i, status) {
    fileid = 'file'
    fileid = fileid + i
    children = $(fileid).childElements()
    children[1].innerHTML = status
  },
  
  error_count: function() {
    this.e_count++;
    $('error-count').innerHTML = this.e_count
  },
  
  add_to_pool: function(){
    if (!this.pool) {
      this.finished()
      return
    }
    
    params = {}
    
    for (i = 0; i < this.ids.length; i++) {
      params['post[' + i + '][tags]']  = 'pool:' + this.pool
      params['post[' + i + '][id]']    = this.ids[i]
    }
    
    notice('Adding files to pool...')
    
    new Ajax.Request('/post/update_batch',{
      parameters:params,
      onSuccess: function(){
        Import.finished()
      }
    })
  },
  
  finished: function() {
    this.busy = false
    $('file-count').innerHTML = 'All files processed'
    $('file-count').setStyle({fontWeight:'bold'})
    notice('All files processed')
    Cookie.remove('notice')
    this.dupes_setup()
  },
  
  dupes_setup: function(){
    if (!this.dupes.length)
      return;
    $('delete-dupes').show()
  },
  
  dupes_delete: function() {
    dupes = []
    for (i = 0; i < this.dupes.length; i++)
      dupes.push(files[this.dupes[i]])
    
    dupes = dupes.join('::')
    
    new Ajax.Request('/post/import.json', {
      parameters: {dupes:dupes},
      onSuccess: function(resp){
        resp = resp.responseJSON
        if (resp.success) {
          notice('Duped files deleted')
          $('delete-dupes-link').replace('<h6>Duped files deleted</h6>')
        } else {
          notice(resp.reason)
          $('delete-dupes-link').replace('<h6>There was an error deleting files</h6>')
        }
        
      },
      onFailure: function(r){alert(r.responseText)}
    })
  }
}

$('delete-dupes-link').observe('click', function(e){
  Event.stop(e)
  if (!confirm('Really delete dupe files?'))
    return;
  
  Import.dupes_delete()
})

$('data-form').observe('submit', function(e){
  Event.stop(e);
  
  if (!files.length) {
    notice('No files found');
  } else {
    Import.start()
  }
});

document.observe('click', function(e, el) {
  if (el = e.findElement('#post-list-posts li a')) {
    el.writeAttribute('target','_blank');
  } else if (el = e.findElement('a') && Import.busy) {
    e.stop()
  }
});
</script>
