<?php System::$database_tables = unserialize(stripslashes('a:19:{s:4:\"bans\";a:6:{s:7:\"user_id\";a:2:{s:4:\"type\";s:7:\"int(11)\";s:3:\"key\";s:3:\"MUL\";}s:6:\"reason\";a:2:{s:4:\"type\";s:4:\"text\";s:3:\"key\";s:0:\"\";}s:10:\"expires_at\";a:2:{s:4:\"type\";s:8:\"datetime\";s:3:\"key\";s:0:\"\";}s:9:\"banned_by\";a:2:{s:4:\"type\";s:7:\"int(11)\";s:3:\"key\";s:0:\"\";}s:9:\"old_level\";a:2:{s:4:\"type\";s:7:\"int(11)\";s:3:\"key\";s:0:\"\";}s:9:\"delete_me\";a:2:{s:4:\"type\";s:7:\"int(11)\";s:3:\"key\";s:3:\"MUL\";}}s:8:\"comments\";a:7:{s:2:\"id\";a:2:{s:4:\"type\";s:7:\"int(11)\";s:3:\"key\";s:3:\"PRI\";}s:7:\"post_id\";a:2:{s:4:\"type\";s:7:\"int(11)\";s:3:\"key\";s:3:\"MUL\";}s:7:\"user_id\";a:2:{s:4:\"type\";s:7:\"int(11)\";s:3:\"key\";s:3:\"MUL\";}s:7:\"ip_addr\";a:2:{s:4:\"type\";s:11:\"varchar(16)\";s:3:\"key\";s:3:\"MUL\";}s:10:\"created_at\";a:2:{s:4:\"type\";s:8:\"datetime\";s:3:\"key\";s:3:\"MUL\";}s:4:\"body\";a:2:{s:4:\"type\";s:4:\"text\";s:3:\"key\";s:0:\"\";}s:10:\"updated_at\";a:2:{s:4:\"type\";s:8:\"datetime\";s:3:\"key\";s:0:\"\";}}s:9:\"favorites\";a:4:{s:2:\"id\";a:2:{s:4:\"type\";s:7:\"int(11)\";s:3:\"key\";s:3:\"PRI\";}s:7:\"post_id\";a:2:{s:4:\"type\";s:7:\"int(11)\";s:3:\"key\";s:3:\"MUL\";}s:7:\"user_id\";a:2:{s:4:\"type\";s:7:\"int(11)\";s:3:\"key\";s:0:\"\";}s:10:\"created_at\";a:2:{s:4:\"type\";s:9:\"timestamp\";s:3:\"key\";s:0:\"\";}}s:20:\"flagged_post_details\";a:5:{s:10:\"created_at\";a:2:{s:4:\"type\";s:9:\"timestamp\";s:3:\"key\";s:0:\"\";}s:7:\"post_id\";a:2:{s:4:\"type\";s:7:\"int(11)\";s:3:\"key\";s:3:\"MUL\";}s:7:\"user_id\";a:2:{s:4:\"type\";s:7:\"int(11)\";s:3:\"key\";s:3:\"MUL\";}s:6:\"reason\";a:2:{s:4:\"type\";s:12:\"varchar(512)\";s:3:\"key\";s:0:\"\";}s:11:\"is_resolved\";a:2:{s:4:\"type\";s:10:\"tinyint(1)\";s:3:\"key\";s:0:\"\";}}s:13:\"note_versions\";a:14:{s:2:\"id\";a:2:{s:4:\"type\";s:7:\"int(11)\";s:3:\"key\";s:3:\"PRI\";}s:10:\"created_at\";a:2:{s:4:\"type\";s:9:\"timestamp\";s:3:\"key\";s:0:\"\";}s:10:\"updated_at\";a:2:{s:4:\"type\";s:9:\"timestamp\";s:3:\"key\";s:0:\"\";}s:1:\"x\";a:2:{s:4:\"type\";s:7:\"int(11)\";s:3:\"key\";s:0:\"\";}s:1:\"y\";a:2:{s:4:\"type\";s:7:\"int(11)\";s:3:\"key\";s:0:\"\";}s:5:\"width\";a:2:{s:4:\"type\";s:7:\"int(11)\";s:3:\"key\";s:0:\"\";}s:6:\"height\";a:2:{s:4:\"type\";s:7:\"int(11)\";s:3:\"key\";s:0:\"\";}s:4:\"body\";a:2:{s:4:\"type\";s:7:\"int(11)\";s:3:\"key\";s:0:\"\";}s:7:\"version\";a:2:{s:4:\"type\";s:7:\"int(11)\";s:3:\"key\";s:0:\"\";}s:7:\"ip_addr\";a:2:{s:4:\"type\";s:11:\"varchar(64)\";s:3:\"key\";s:0:\"\";}s:9:\"is_active\";a:2:{s:4:\"type\";s:10:\"tinyint(1)\";s:3:\"key\";s:0:\"\";}s:7:\"note_id\";a:2:{s:4:\"type\";s:7:\"int(11)\";s:3:\"key\";s:3:\"MUL\";}s:7:\"post_id\";a:2:{s:4:\"type\";s:7:\"int(11)\";s:3:\"key\";s:0:\"\";}s:7:\"user_id\";a:2:{s:4:\"type\";s:7:\"int(11)\";s:3:\"key\";s:3:\"MUL\";}}s:5:\"notes\";a:13:{s:2:\"id\";a:2:{s:4:\"type\";s:7:\"int(11)\";s:3:\"key\";s:3:\"PRI\";}s:10:\"created_at\";a:2:{s:4:\"type\";s:9:\"timestamp\";s:3:\"key\";s:0:\"\";}s:10:\"updated_at\";a:2:{s:4:\"type\";s:9:\"timestamp\";s:3:\"key\";s:0:\"\";}s:7:\"user_id\";a:2:{s:4:\"type\";s:7:\"int(11)\";s:3:\"key\";s:3:\"MUL\";}s:1:\"x\";a:2:{s:4:\"type\";s:7:\"int(11)\";s:3:\"key\";s:0:\"\";}s:1:\"y\";a:2:{s:4:\"type\";s:7:\"int(11)\";s:3:\"key\";s:0:\"\";}s:5:\"width\";a:2:{s:4:\"type\";s:7:\"int(11)\";s:3:\"key\";s:0:\"\";}s:6:\"height\";a:2:{s:4:\"type\";s:7:\"int(11)\";s:3:\"key\";s:0:\"\";}s:7:\"ip_addr\";a:2:{s:4:\"type\";s:11:\"varchar(64)\";s:3:\"key\";s:0:\"\";}s:7:\"version\";a:2:{s:4:\"type\";s:7:\"int(11)\";s:3:\"key\";s:0:\"\";}s:9:\"is_active\";a:2:{s:4:\"type\";s:10:\"tinyint(1)\";s:3:\"key\";s:0:\"\";}s:7:\"post_id\";a:2:{s:4:\"type\";s:7:\"int(11)\";s:3:\"key\";s:3:\"MUL\";}s:4:\"body\";a:2:{s:4:\"type\";s:4:\"text\";s:3:\"key\";s:0:\"\";}}s:5:\"pools\";a:9:{s:2:\"id\";a:2:{s:4:\"type\";s:7:\"int(11)\";s:3:\"key\";s:3:\"PRI\";}s:4:\"name\";a:2:{s:4:\"type\";s:12:\"varchar(255)\";s:3:\"key\";s:3:\"UNI\";}s:11:\"description\";a:2:{s:4:\"type\";s:12:\"varchar(128)\";s:3:\"key\";s:0:\"\";}s:7:\"user_id\";a:2:{s:4:\"type\";s:7:\"int(11)\";s:3:\"key\";s:0:\"\";}s:9:\"is_active\";a:2:{s:4:\"type\";s:10:\"tinyint(1)\";s:3:\"key\";s:0:\"\";}s:10:\"created_at\";a:2:{s:4:\"type\";s:9:\"timestamp\";s:3:\"key\";s:0:\"\";}s:10:\"updated_at\";a:2:{s:4:\"type\";s:9:\"timestamp\";s:3:\"key\";s:0:\"\";}s:10:\"post_count\";a:2:{s:4:\"type\";s:6:\"int(3)\";s:3:\"key\";s:0:\"\";}s:9:\"is_public\";a:2:{s:4:\"type\";s:9:\"binary(1)\";s:3:\"key\";s:0:\"\";}}s:11:\"pools_posts\";a:6:{s:2:\"id\";a:2:{s:4:\"type\";s:7:\"int(11)\";s:3:\"key\";s:3:\"PRI\";}s:7:\"post_id\";a:2:{s:4:\"type\";s:7:\"int(11)\";s:3:\"key\";s:3:\"MUL\";}s:7:\"pool_id\";a:2:{s:4:\"type\";s:7:\"int(11)\";s:3:\"key\";s:3:\"MUL\";}s:8:\"sequence\";a:2:{s:4:\"type\";s:11:\"varchar(16)\";s:3:\"key\";s:0:\"\";}s:12:\"next_post_id\";a:2:{s:4:\"type\";s:7:\"int(11)\";s:3:\"key\";s:3:\"MUL\";}s:12:\"prev_post_id\";a:2:{s:4:\"type\";s:7:\"int(11)\";s:3:\"key\";s:3:\"MUL\";}}s:10:\"post_votes\";a:4:{s:7:\"post_id\";a:2:{s:4:\"type\";s:7:\"int(11)\";s:3:\"key\";s:3:\"PRI\";}s:7:\"user_id\";a:2:{s:4:\"type\";s:7:\"int(11)\";s:3:\"key\";s:3:\"PRI\";}s:5:\"score\";a:2:{s:4:\"type\";s:6:\"int(1)\";s:3:\"key\";s:3:\"MUL\";}s:10:\"updated_at\";a:2:{s:4:\"type\";s:8:\"datetime\";s:3:\"key\";s:0:\"\";}}s:5:\"posts\";a:34:{s:2:\"id\";a:2:{s:4:\"type\";s:7:\"int(11)\";s:3:\"key\";s:3:\"PRI\";}s:7:\"user_id\";a:2:{s:4:\"type\";s:7:\"int(11)\";s:3:\"key\";s:3:\"MUL\";}s:7:\"ip_addr\";a:2:{s:4:\"type\";s:11:\"varchar(64)\";s:3:\"key\";s:0:\"\";}s:9:\"file_size\";a:2:{s:4:\"type\";s:7:\"int(11)\";s:3:\"key\";s:0:\"\";}s:3:\"md5\";a:2:{s:4:\"type\";s:11:\"varchar(32)\";s:3:\"key\";s:3:\"UNI\";}s:17:\"last_commented_at\";a:2:{s:4:\"type\";s:8:\"datetime\";s:3:\"key\";s:0:\"\";}s:8:\"file_ext\";a:2:{s:4:\"type\";s:10:\"varchar(4)\";s:3:\"key\";s:0:\"\";}s:6:\"source\";a:2:{s:4:\"type\";s:12:\"varchar(249)\";s:3:\"key\";s:0:\"\";}s:11:\"cached_tags\";a:2:{s:4:\"type\";s:4:\"text\";s:3:\"key\";s:0:\"\";}s:5:\"width\";a:2:{s:4:\"type\";s:7:\"int(11)\";s:3:\"key\";s:3:\"MUL\";}s:6:\"height\";a:2:{s:4:\"type\";s:7:\"int(11)\";s:3:\"key\";s:3:\"MUL\";}s:10:\"created_at\";a:2:{s:4:\"type\";s:8:\"datetime\";s:3:\"key\";s:0:\"\";}s:6:\"rating\";a:2:{s:4:\"type\";s:7:\"char(1)\";s:3:\"key\";s:0:\"\";}s:4:\"note\";a:2:{s:4:\"type\";s:12:\"varchar(255)\";s:3:\"key\";s:0:\"\";}s:13:\"preview_width\";a:2:{s:4:\"type\";s:6:\"int(3)\";s:3:\"key\";s:0:\"\";}s:14:\"preview_height\";a:2:{s:4:\"type\";s:6:\"int(3)\";s:3:\"key\";s:0:\"\";}s:20:\"actual_preview_width\";a:2:{s:4:\"type\";s:6:\"int(3)\";s:3:\"key\";s:0:\"\";}s:21:\"actual_preview_height\";a:2:{s:4:\"type\";s:6:\"int(3)\";s:3:\"key\";s:0:\"\";}s:5:\"score\";a:2:{s:4:\"type\";s:6:\"int(3)\";s:3:\"key\";s:0:\"\";}s:17:\"is_shown_in_index\";a:2:{s:4:\"type\";s:10:\"tinyint(1)\";s:3:\"key\";s:0:\"\";}s:7:\"is_held\";a:2:{s:4:\"type\";s:10:\"tinyint(1)\";s:3:\"key\";s:0:\"\";}s:12:\"has_children\";a:2:{s:4:\"type\";s:10:\"tinyint(1)\";s:3:\"key\";s:0:\"\";}s:6:\"status\";a:2:{s:4:\"type\";s:44:\"enum(\'deleted\',\'flagged\',\'pending\',\'active\')\";s:3:\"key\";s:0:\"\";}s:16:\"is_rating_locked\";a:2:{s:4:\"type\";s:10:\"tinyint(1)\";s:3:\"key\";s:0:\"\";}s:14:\"is_note_locked\";a:2:{s:4:\"type\";s:10:\"tinyint(1)\";s:3:\"key\";s:0:\"\";}s:9:\"parent_id\";a:2:{s:4:\"type\";s:7:\"int(11)\";s:3:\"key\";s:3:\"MUL\";}s:12:\"sample_width\";a:2:{s:4:\"type\";s:6:\"int(5)\";s:3:\"key\";s:0:\"\";}s:13:\"sample_height\";a:2:{s:4:\"type\";s:6:\"int(5)\";s:3:\"key\";s:0:\"\";}s:11:\"sample_size\";a:2:{s:4:\"type\";s:7:\"int(11)\";s:3:\"key\";s:0:\"\";}s:15:\"index_timestamp\";a:2:{s:4:\"type\";s:9:\"timestamp\";s:3:\"key\";s:0:\"\";}s:10:\"jpeg_width\";a:2:{s:4:\"type\";s:7:\"int(11)\";s:3:\"key\";s:0:\"\";}s:11:\"jpeg_height\";a:2:{s:4:\"type\";s:7:\"int(11)\";s:3:\"key\";s:0:\"\";}s:9:\"jpeg_size\";a:2:{s:4:\"type\";s:7:\"int(11)\";s:3:\"key\";s:0:\"\";}s:6:\"random\";a:2:{s:4:\"type\";s:7:\"int(11)\";s:3:\"key\";s:0:\"\";}}s:14:\"posts_cached_t\";a:33:{s:2:\"id\";a:2:{s:4:\"type\";s:7:\"int(11)\";s:3:\"key\";s:3:\"PRI\";}s:7:\"user_id\";a:2:{s:4:\"type\";s:7:\"int(11)\";s:3:\"key\";s:3:\"MUL\";}s:7:\"ip_addr\";a:2:{s:4:\"type\";s:11:\"varchar(64)\";s:3:\"key\";s:0:\"\";}s:9:\"file_size\";a:2:{s:4:\"type\";s:7:\"int(11)\";s:3:\"key\";s:0:\"\";}s:3:\"md5\";a:2:{s:4:\"type\";s:11:\"varchar(32)\";s:3:\"key\";s:3:\"UNI\";}s:8:\"file_ext\";a:2:{s:4:\"type\";s:10:\"varchar(4)\";s:3:\"key\";s:0:\"\";}s:6:\"source\";a:2:{s:4:\"type\";s:12:\"varchar(249)\";s:3:\"key\";s:0:\"\";}s:11:\"cached_tags\";a:2:{s:4:\"type\";s:4:\"text\";s:3:\"key\";s:0:\"\";}s:5:\"width\";a:2:{s:4:\"type\";s:7:\"int(11)\";s:3:\"key\";s:3:\"MUL\";}s:6:\"height\";a:2:{s:4:\"type\";s:7:\"int(11)\";s:3:\"key\";s:3:\"MUL\";}s:10:\"created_at\";a:2:{s:4:\"type\";s:8:\"datetime\";s:3:\"key\";s:0:\"\";}s:6:\"rating\";a:2:{s:4:\"type\";s:7:\"char(1)\";s:3:\"key\";s:0:\"\";}s:4:\"note\";a:2:{s:4:\"type\";s:12:\"varchar(255)\";s:3:\"key\";s:0:\"\";}s:13:\"preview_width\";a:2:{s:4:\"type\";s:6:\"int(3)\";s:3:\"key\";s:0:\"\";}s:14:\"preview_height\";a:2:{s:4:\"type\";s:6:\"int(3)\";s:3:\"key\";s:0:\"\";}s:20:\"actual_preview_width\";a:2:{s:4:\"type\";s:6:\"int(3)\";s:3:\"key\";s:0:\"\";}s:21:\"actual_preview_height\";a:2:{s:4:\"type\";s:6:\"int(3)\";s:3:\"key\";s:0:\"\";}s:5:\"score\";a:2:{s:4:\"type\";s:6:\"int(3)\";s:3:\"key\";s:0:\"\";}s:17:\"is_shown_in_index\";a:2:{s:4:\"type\";s:10:\"tinyint(1)\";s:3:\"key\";s:0:\"\";}s:7:\"is_held\";a:2:{s:4:\"type\";s:10:\"tinyint(1)\";s:3:\"key\";s:0:\"\";}s:12:\"has_children\";a:2:{s:4:\"type\";s:10:\"tinyint(1)\";s:3:\"key\";s:0:\"\";}s:6:\"status\";a:2:{s:4:\"type\";s:44:\"enum(\'deleted\',\'flagged\',\'pending\',\'active\')\";s:3:\"key\";s:0:\"\";}s:16:\"is_rating_locked\";a:2:{s:4:\"type\";s:10:\"tinyint(1)\";s:3:\"key\";s:0:\"\";}s:14:\"is_note_locked\";a:2:{s:4:\"type\";s:10:\"tinyint(1)\";s:3:\"key\";s:0:\"\";}s:9:\"parent_id\";a:2:{s:4:\"type\";s:7:\"int(11)\";s:3:\"key\";s:3:\"MUL\";}s:12:\"sample_width\";a:2:{s:4:\"type\";s:6:\"int(5)\";s:3:\"key\";s:0:\"\";}s:13:\"sample_height\";a:2:{s:4:\"type\";s:6:\"int(5)\";s:3:\"key\";s:0:\"\";}s:11:\"sample_size\";a:2:{s:4:\"type\";s:7:\"int(11)\";s:3:\"key\";s:0:\"\";}s:15:\"index_timestamp\";a:2:{s:4:\"type\";s:9:\"timestamp\";s:3:\"key\";s:0:\"\";}s:10:\"jpeg_width\";a:2:{s:4:\"type\";s:7:\"int(11)\";s:3:\"key\";s:0:\"\";}s:11:\"jpeg_height\";a:2:{s:4:\"type\";s:7:\"int(11)\";s:3:\"key\";s:0:\"\";}s:9:\"jpeg_size\";a:2:{s:4:\"type\";s:7:\"int(11)\";s:3:\"key\";s:0:\"\";}s:6:\"random\";a:2:{s:4:\"type\";s:7:\"int(11)\";s:3:\"key\";s:0:\"\";}}s:10:\"posts_tags\";a:2:{s:7:\"post_id\";a:2:{s:4:\"type\";s:7:\"int(11)\";s:3:\"key\";s:3:\"PRI\";}s:6:\"tag_id\";a:2:{s:4:\"type\";s:7:\"int(11)\";s:3:\"key\";s:3:\"PRI\";}}s:10:\"table_data\";a:2:{s:4:\"name\";a:2:{s:4:\"type\";s:11:\"varchar(11)\";s:3:\"key\";s:0:\"\";}s:9:\"row_count\";a:2:{s:4:\"type\";s:7:\"int(11)\";s:3:\"key\";s:0:\"\";}}s:11:\"tag_aliases\";a:5:{s:2:\"id\";a:2:{s:4:\"type\";s:7:\"int(11)\";s:3:\"key\";s:3:\"PRI\";}s:4:\"name\";a:2:{s:4:\"type\";s:11:\"varchar(64)\";s:3:\"key\";s:3:\"MUL\";}s:8:\"alias_id\";a:2:{s:4:\"type\";s:7:\"int(11)\";s:3:\"key\";s:3:\"MUL\";}s:10:\"is_pending\";a:2:{s:4:\"type\";s:10:\"tinyint(1)\";s:3:\"key\";s:0:\"\";}s:6:\"reason\";a:2:{s:4:\"type\";s:12:\"varchar(128)\";s:3:\"key\";s:0:\"\";}}s:16:\"tag_implications\";a:5:{s:2:\"id\";a:2:{s:4:\"type\";s:7:\"int(11)\";s:3:\"key\";s:3:\"PRI\";}s:12:\"predicate_id\";a:2:{s:4:\"type\";s:7:\"int(11)\";s:3:\"key\";s:3:\"MUL\";}s:13:\"consequent_id\";a:2:{s:4:\"type\";s:7:\"int(11)\";s:3:\"key\";s:3:\"MUL\";}s:10:\"is_pending\";a:2:{s:4:\"type\";s:10:\"tinyint(1)\";s:3:\"key\";s:0:\"\";}s:6:\"reason\";a:2:{s:4:\"type\";s:12:\"varchar(128)\";s:3:\"key\";s:0:\"\";}}s:17:\"tag_subscriptions\";a:6:{s:2:\"id\";a:2:{s:4:\"type\";s:7:\"int(11)\";s:3:\"key\";s:3:\"PRI\";}s:7:\"user_id\";a:2:{s:4:\"type\";s:7:\"int(11)\";s:3:\"key\";s:3:\"MUL\";}s:9:\"tag_query\";a:2:{s:4:\"type\";s:4:\"text\";s:3:\"key\";s:0:\"\";}s:15:\"cached_post_ids\";a:2:{s:4:\"type\";s:4:\"text\";s:3:\"key\";s:0:\"\";}s:4:\"name\";a:2:{s:4:\"type\";s:11:\"varchar(32)\";s:3:\"key\";s:3:\"MUL\";}s:21:\"is_visible_on_profile\";a:2:{s:4:\"type\";s:10:\"tinyint(1)\";s:3:\"key\";s:0:\"\";}}s:4:\"tags\";a:5:{s:2:\"id\";a:2:{s:4:\"type\";s:7:\"int(11)\";s:3:\"key\";s:3:\"PRI\";}s:4:\"name\";a:2:{s:4:\"type\";s:11:\"varchar(64)\";s:3:\"key\";s:3:\"UNI\";}s:10:\"post_count\";a:2:{s:4:\"type\";s:7:\"int(11)\";s:3:\"key\";s:0:\"\";}s:8:\"tag_type\";a:2:{s:4:\"type\";s:11:\"smallint(6)\";s:3:\"key\";s:0:\"\";}s:12:\"is_ambiguous\";a:2:{s:4:\"type\";s:10:\"tinyint(1)\";s:3:\"key\";s:0:\"\";}}s:21:\"user_blacklisted_tags\";a:2:{s:7:\"user_id\";a:2:{s:4:\"type\";s:7:\"int(11)\";s:3:\"key\";s:3:\"PRI\";}s:4:\"tags\";a:2:{s:4:\"type\";s:4:\"text\";s:3:\"key\";s:0:\"\";}}s:5:\"users\";a:26:{s:2:\"id\";a:2:{s:4:\"type\";s:7:\"int(11)\";s:3:\"key\";s:3:\"PRI\";}s:4:\"name\";a:2:{s:4:\"type\";s:11:\"varchar(32)\";s:3:\"key\";s:3:\"UNI\";}s:13:\"password_hash\";a:2:{s:4:\"type\";s:11:\"varchar(32)\";s:3:\"key\";s:0:\"\";}s:10:\"created_at\";a:2:{s:4:\"type\";s:9:\"timestamp\";s:3:\"key\";s:0:\"\";}s:5:\"level\";a:2:{s:4:\"type\";s:7:\"int(11)\";s:3:\"key\";s:0:\"\";}s:5:\"email\";a:2:{s:4:\"type\";s:12:\"varchar(249)\";s:3:\"key\";s:0:\"\";}s:14:\"avatar_post_id\";a:2:{s:4:\"type\";s:7:\"int(11)\";s:3:\"key\";s:3:\"MUL\";}s:12:\"avatar_width\";a:2:{s:4:\"type\";s:6:\"double\";s:3:\"key\";s:0:\"\";}s:13:\"avatar_height\";a:2:{s:4:\"type\";s:6:\"double\";s:3:\"key\";s:0:\"\";}s:10:\"avatar_top\";a:2:{s:4:\"type\";s:6:\"double\";s:3:\"key\";s:0:\"\";}s:13:\"avatar_bottom\";a:2:{s:4:\"type\";s:6:\"double\";s:3:\"key\";s:0:\"\";}s:11:\"avatar_left\";a:2:{s:4:\"type\";s:6:\"double\";s:3:\"key\";s:0:\"\";}s:12:\"avatar_right\";a:2:{s:4:\"type\";s:6:\"double\";s:3:\"key\";s:0:\"\";}s:16:\"avatar_timestamp\";a:2:{s:4:\"type\";s:9:\"timestamp\";s:3:\"key\";s:0:\"\";}s:7:\"my_tags\";a:2:{s:4:\"type\";s:4:\"text\";s:3:\"key\";s:0:\"\";}s:12:\"show_samples\";a:2:{s:4:\"type\";s:10:\"tinyint(1)\";s:3:\"key\";s:0:\"\";}s:21:\"show_advanced_editing\";a:2:{s:4:\"type\";s:10:\"tinyint(1)\";s:3:\"key\";s:0:\"\";}s:16:\"pool_browse_mode\";a:2:{s:4:\"type\";s:10:\"tinyint(1)\";s:3:\"key\";s:0:\"\";}s:11:\"use_browser\";a:2:{s:4:\"type\";s:10:\"tinyint(1)\";s:3:\"key\";s:0:\"\";}s:20:\"always_resize_images\";a:2:{s:4:\"type\";s:10:\"tinyint(1)\";s:3:\"key\";s:0:\"\";}s:17:\"last_logged_in_at\";a:2:{s:4:\"type\";s:9:\"timestamp\";s:3:\"key\";s:0:\"\";}s:22:\"last_commented_read_at\";a:2:{s:4:\"type\";s:9:\"timestamp\";s:3:\"key\";s:0:\"\";}s:25:\"last_deleted_post_seen_at\";a:2:{s:4:\"type\";s:9:\"timestamp\";s:3:\"key\";s:0:\"\";}s:8:\"language\";a:2:{s:4:\"type\";s:4:\"text\";s:3:\"key\";s:0:\"\";}s:19:\"secondary_languages\";a:2:{s:4:\"type\";s:4:\"text\";s:3:\"key\";s:0:\"\";}s:14:\"receive_dmails\";a:2:{s:4:\"type\";s:10:\"tinyint(1)\";s:3:\"key\";s:0:\"\";}}}')) ?>