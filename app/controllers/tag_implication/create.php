<?php
required_params('tag_implication');

$ti = TagImplication::blank(array_merge(Request::$params->tag_implication, array('is_pending' => true)));

if ($ti->save())
  notice("Tag implication created");
else
  notice("Error: " . implode(', ', $ti->record_errors->full_messages()));

redirect_to("#index");
?>