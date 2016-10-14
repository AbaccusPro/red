<?php if(Setting::get('timeline_right_ad') != NULL): ?>
	<?php echo htmlspecialchars_decode(Setting::get('timeline_right_ad')); ?>

<?php endif; ?>
<?php if(Config::get('app.env') == 'demo' || Config::get('app.env') == 'local'): ?>
	<img src="http://placehold.it/190x200?text=Ad+Block"><br><br>
	<img src="http://placehold.it/190x200?text=Ad+Block">
<?php endif; ?>