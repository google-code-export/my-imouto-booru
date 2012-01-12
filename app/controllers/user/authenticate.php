<?php
User::$_->save_cookies(User::$current);

$path = empty(Request::$params->url) ? '/user/home' : Request::$url;

respond_to_success("You are now logged in", $path);
?>