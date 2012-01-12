<?php
required_params('user');

$user = User::$_->create(Request::$params->user);

if ($user->record_errors->blank()) {
  User::$_->save_cookies($user);

  $ret = array('exists' => false);
  $ret['name'] = $user->name;
  $ret['id'] = $user->id;
  $ret['pass_hash'] = $user->password_hash;
  $ret['user_info'] = $user->user_info_cookie;
  $ret['response'] = 'success';

  respond_to_success("New account created", "#home", array('api' => $ret));
} else {
  $error = implode(', ', $user->record_errors->full_messages());
  respond_to_success("Error: " . $error, "#signup", array('api' => array('response' => "error", 'errors' => $user->record_errors->full_messages())));
}
?>