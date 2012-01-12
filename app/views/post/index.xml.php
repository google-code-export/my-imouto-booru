<?php echo '<?xml version="1.0" encoding="UTF-8"?>' ?>

<posts count="<?php echo $found_posts ?>" offset="<?php echo ($offset) ?>">
	<?php foreach ($posts as $post) : ?>
		<?php echo $post->to_xml(array('skip_instruct' => true)) ?>
	<?php endforeach ?>
</posts>
