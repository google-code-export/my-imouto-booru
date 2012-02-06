<?php
function avatar($user, $id, $html_options = array()) {
  // $shown_avatars = array();
  // $avatar = $user->avatar_post($user);
  $avatar = $user->avatar_post;
  
  return '<a href="/post/show/'.$avatar->id.'" class="ca'.$avatar->id.'" onclick="return Post.check_avatar_blacklist('.$avatar->id.', '.$id.');">
  <img alt="'.$user->id.'" class="avatar" width="'.$user->avatar_width.'" height="'.$user->avatar_height.'" src="'.$user->avatar_url().'" />
</a>';
  
  // <img alt="23306" class="avatar" height="64.0" src="http://oreno.imouto.org/data/avatars/23306.jpg?1312590085" width="125.0"/>
  // $posts_to_send = array();

  
  
  #if not @shown_avatars[user] then
  // $shown_avatars['user'] = true;
  // $posts_to_send << $user->avatar_post
  // img = image_tag(user.avatar_url + "?" + user.avatar_timestamp.tv_sec.to_s,
                  // {:class => "avatar", :width => user.avatar_width, :height => user.avatar_height}.merge(html_options))
  // link_to(img,
          // { :controller => "post", :action => "show", :id => user.avatar_post.id.to_s },
          // :class => "ca" + user.avatar_post.id.to_s,
          // :onclick => %{return Post.check_avatar_blacklist(#{user.avatar_post.id.to_s}, #{id});})
  #end
}

function avatar_init() {
  $posts = Comment::avatar_post_reg(true);
  
  if (!$posts)
    return;
  
  $ret = '';
  
  foreach($posts as $post)
    $ret .= "Post.register(".to_json($post).")\n";
  
  $ret .= 'Post.init_blacklisted';
  return $ret;
}
?>