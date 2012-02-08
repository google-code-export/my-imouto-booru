<?php
function error_messages_for($record, $params = array()) {
  if ($record->record_errors->blank())
    return '';
  
  $count = $record->record_errors->count();
  
  $tag = 'h2';
  
  $header = "<$tag>" . $count;
  $header .= $count < 2 ? ' error' : ' errors';
  $header .= " prohibited this record from being saved</$tag>";
  
  $message = 'There were problems with the following fields:';
  $message = "<p>$message</p>";
  
  $error_list = '<ul><li>'.implode('</li><li>', $record->record_errors->full_messages()).'</li></ul>';
  
  return $header . $message . $error_list;
}
?>