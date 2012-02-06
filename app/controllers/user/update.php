<?php
if (isset(Request::$params->commit) && Request::$params->commit == "Cancel") {
  redirect_to('#home');
}

if (!empty(Request::$params->user) && User::$current->update_attributes(Request::$params->user)) {
  User::save_cookies(User::$current);
  respond_to_success("Account settings saved", "#edit");
} else
  respond_to_error(User::$current, "#edit");
?>