<form class="form-horizontal" id="fcontent" name="fcontent" action="/my/message/detail" method="post">
<?php include  __DIR__ . "/../my_menu.php"; ?>
	<div class="col80 content-right">
		<h4><?php echo I18n::get('messages') . " - " . HTML::chars($message_obj[0]['subject']) ?></h4><hr>
		
		<div class="btn-group btn-group-sm">
			<button id="archive" type="button" class="btn btn-default"><?php echo I18n::get('archive') ?></button>
			<button id="delete" type="button" class="btn btn-default"><?php echo I18n::get('delete') ?></button>
		</div>
		<p><br></p>
		<div class="list-group">
		<?php
		foreach ($message_obj as $record)
		{
		?>
		
			<div class="list-group-item">
				<p class="subject"><b><?php echo I18n::get('from') . ": " . $record['from'] ?></b></p>
				<p class="subject"><b><?php echo I18n::get('to') . ": " . $record['to'] ?></b></p>
				<p class="subject"><b><?php echo I18n::get('date') . ": " . date('M d, Y', $record['posted']) ?></b></p>
				<p><?php echo nl2br(HTML::chars($record['message'])) ?></p>
			</div>
		<?php
		}
		?>
		</div>
		<hr>		
		<div class="col-lg-12">
			<h5><b><?php echo I18n::get('reply') ?></b></h5>
		</div>
		<br>
		<div class="col-lg-12">
			<textarea class="form-control" id="message" name="message" cols="10" rows="6" placeholder="<?php echo I18n::get('message') ?>"><?php echo HTML::chars(Arr::get($_POST, "message")) ?></textarea>
		</div>
		
		
		
	
		
		
		<br>
		<p class="text-center"><input class="btn btn-default" type="submit" value="<?php echo I18n::get('submit') ?>" /></p>

	</div>
	<input type="hidden" name="id" value="<?php echo $message_id ?>">
</form>
<script src="<?php echo $cfg["jquery.js"] ?>"></script>
<script src="<?php echo $cfg["jquery_validate.js"] ?>"></script>
<script>
$(document).ready(function() {
	$('#recipient_username').focus();
	var validator = $('form#fcontent').validate({
		rules: {
			message: {
				required: true
			}
		}
	});
});
</script>