<?php
validates('body', array('format' => array('/\S/', 'message' => 'has no content')));
belongs_to('post');
belongs_to('user');
after('save', 'update_last_commented_at');
// after('create', 'update_last_commented_at');
// after_save('update_fragments');
after('destroy', 'update_last_commented_at');

class Comment extends ActiveRecord {
  static $_;

  function generate_sql($params) {
    $params = (array)$params; // because of comment/index
    if (empty($params['post_id']))
      return array();
    
    return array('conditions' => 'post_id = ?', array($params['post_id']));
    // return Nagato::Builder.new do |builder, cond|
      // cond.add_unless_blank "post_id = ?", params[:post_id]
    // end.to_hash
  }
  
  function pretty_author() {
    return str_replace("_", " ", $this->user->name);
  }
  
  function api_attributes() {
    return array(
      'id'          => $this->id, 
      'created_at'  => $this->created_at, 
      'post_id'     => $this->post_id, 
      'creator'     => $this->user->name, 
      'creator_id'  => $this->user_id, 
      'body'        => $this->body
    );
  }
  
  function update_last_commented_at() {
    # return if self.do_not_bump_post
    
    $comment_count = DB::select_value("SELECT COUNT(*) FROM comments WHERE post_id = ?", $this->post_id);
    if ($comment_count <= CONFIG::comment_threshold) {
      DB::update("posts SET last_commented_at = (SELECT created_at FROM comments WHERE post_id = :post_id ORDER BY created_at DESC LIMIT 1) WHERE posts.id = :post_id", array('post_id' => $this->post_id));
    }
  }

  function to_xml($options = array()) {
    return to_xml($this->api_attributes(), array_merge(array('root' => 'comment'), $options));
  }

  function to_json($args = array()) {
    return to_json($this->api_attributes()); //.to_json($args)
  }
  
  /**
   * Avatar post for javascript register
   *
   * Created because in /comments normally we register every avatar post
   * and that's not nice...
   * Pass a true to return the posts.
   */
  function avatar_post_reg($post) {
    static $comments_avatar_posts;
    
    if (empty($comments_avatar_posts))
      $comments_avatar_posts = array();
    
    if ($post === true)
      return $comments_avatar_posts;
    
    foreach ($comments_avatar_posts as $p) {
      if ($p->id == $post->id)
        return;
    }
    $comments_avatar_posts[] = $post;
    // vd($comments_avatar_posts);
  }
}
?>