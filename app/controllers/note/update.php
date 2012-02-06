<?php
required_params('note');

if (!empty(Request::$params->note['post_id']))
  $note = Note::blank(array('post_id' => Request::$params->note['post_id']));
elseif (!empty(Request::$params->id))
  $note = Note::find(Request::$params->id);

if (!$note)
  exit_with_status(400);

if ($note->is_locked())
  respond_to_error("Post is locked", array('post#show', 'id' => $note->post_id), array('status' => 422));

// $note->attributes = Request::$params->note;
$note->add_attributes(Request::$params->note);
$note->user_id = User::$current->id;
$note->ip_addr = Request::$remote_ip;

if ($note->save())
  respond_to_success("Note updated", '#index', array('api' => array('new_id' => $note->id, 'old_id' => (int)Request::$params->id, 'formatted_body' => $note->formatted_body())));
else
  respond_to_error($note, array('post#show', 'id' => $note->post_id));
?>