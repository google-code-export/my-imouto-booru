<?php
set_title("Comments");
auto_set_params(array('page' => 1));
create_page_params(array('limit' => 10));

if (Request::$format == "json" || Request::$format == "xml") {
  // required_params('post_id');
  // $comments = new Collection('Comment', 'find', array_merge(Comment::$_->generate_sql(Request::$params), array('per_page' => 25, 'page' => Request::$params->page, 'order' => 'id DESC')));
  $comments = Comment::$_->collection('find', array_merge(Comment::$_->generate_sql(Request::$params), array('per_page' => 25, 'page' => Request::$params->page, 'order' => 'id DESC')));
  // vde($comments);
  // Comment.paginate(Comment.generate_sql(params).merge(:per_page => 25, :page => params[:page], :order => "id DESC"))
  respond_to_list($comments);
} else {
  
  // $posts = new Collection('Paginate->found_rows', 'Post', 'find', array('order' => 'last_commented_at DESC', 'conditions' => 'last_commented_at IS NOT NULL', 'per_page' => 10, 'page' => Request::$params->page));
  $posts = Post::$_->collection('Paginate->found_rows', 'find', array('order' => 'last_commented_at DESC', 'conditions' => 'last_commented_at IS NOT NULL', 'per_page' => 10, 'page' => Request::$params->page));
  // Post.paginate :order => "last_commented_at DESC", :conditions => "last_commented_at IS NOT NULL", :per_page => 10, :page => params[:page]
  // vde($posts);
  $comments = array();
  foreach ($posts as $post)
    $comments[] = $post->recent_comments();
  // @posts.each { |post| comments.push(*post.recent_comments) }
  // exit;
  // newest_comment = comments.max {|a,b| a.created_at <=> b.created_at }
  // if !@current_user.is_anonymous? && newest_comment && @current_user.last_comment_read_at < newest_comment.created_at
    // @current_user.update_attribute(:last_comment_read_at, newest_comment.created_at)
  // end

  // $posts = $posts.select {|x| x.can_be_seen_by?(@current_user, {:show_deleted => true})}
  
  $i = 0;
  foreach ($posts as &$x) {
    if (!$x->can_be_seen_by(User::$current, array('show_deleted' => true)))
      unset($posts->$i);
    unset($x);
    $i++;
  }
  // exit;
  calc_pages(); 
}
?>