<?php
required_params('tag_alias');
// vde(Request::$params->tag_alias);
$ta = TagAlias::$_->blank(Request::$params->tag_alias);

// vde($ta);

$ta->is_pending = true;

// vde($ta);
// DB::show_query(1);
if ($ta->save())
  notice("Tag alias created");
else
  notice("Error: " . implode(', ', $ta->record_errors->full_messages()));
// exit;
redirect_to("#index");
?>