<?php
required_params('username');

// $user = new User('find_by_name', Request::$params->username);
$user = User::find_by_name(Request::$params->username);
// vde($user);
$ret['exists'] = false;
$ret['name'] = Request::$params->username;

if (!$user) {
  $ret['response'] = "unknown-user";
  respond_to_success("User does not exist", null, array('api' => $ret));
  return;
}

# Return some basic information about the user even if the password isn't given, for
# UI cosmetics.
$ret['exists'] = true;
$ret['id'] = $user->id;
$ret['name'] = $user->name;
$ret['no_email'] = empty($user->email);

$pass = isset(Request::$params->password) ? Request::$params->password : "";

$user = User::authenticate(Request::$params->username, $pass);

if(!$user) {
  $ret['response'] = "wrong-password";
  respond_to_success("Wrong password", null, array('api' => $ret));
  return;
}

$ret['pass_hash'] = $user->password_hash;
$ret['user_info'] = $user->user_info_cookie();

$ret['response'] = 'success';
respond_to_success("Successful", null, array('api' => $ret));
?>