<?php
set_title("Notes");
create_page_params();
auto_set_params('post_id');

if (!empty(Request::$params->post_id))
  $posts = Post::find_all(array('order' => "last_noted_at DESC", 'conditions' => array("id = ?", Request::$params->post_id), 'per_page' => 100, 'page' => $page));
  // $posts = Post.paginate :order => "last_noted_at DESC", :conditions => ["id = ?", params[:post_id]], :per_page => 100, :page => params[:page]
else
  $posts = Post::find_all(array('order' => "last_noted_at DESC", 'conditions' => "last_noted_at IS NOT NULL", 'per_page' => 16, 'page' => $page));
  // $posts = Post.paginate :order => "last_noted_at DESC", :conditions => "last_noted_at IS NOT NULL", :per_page => 16, :page => params[:page]

// respond_to do |fmt|
  // fmt.html
  // fmt.xml {render :xml => @posts.map {|x| x.notes}.flatten.to_xml(:root => "notes")}
  // fmt.json {render :json => @posts.map {|x| x.notes}.flatten.to_json}
// end

calc_pages();
?>