<form class="form-horizontal" id="fcontent" name="fcontent" action="/my/message/new" method="post">
<?php include  __DIR__ . "/../my_menu.php";  ?>


	<div class="col80 content-right">
		<h4><?php echo I18n::get('messages') ?></h4><hr>
	
		<div class="form-group row">
			<label for="recipient_username" class="col-lg-3 col-md-3 control-label"><?php echo I18n::get('recipient_username') ?></label>
			<div class="col-lg-5 col-md-6">
				<input class="form-control" id="recipient_username" maxlength="40" name="recipient_username" type="text" value="<?php echo HTML::chars(Arr::get($_POST, "recipient_username")) ?>">
				<div class="error"><?php echo $errors['recipient_username'] ?></div>
			</div>
		</div>
			
		<div class="form-group row">
			<label class="col-lg-3 col-md-3 control-label" for="subject"><?php echo I18n::get('subject') ?></label>
			<div class="col-lg-5 col-md-6">
				<input class="form-control" id="subject" maxlength="100" name="subject" type="text" value="<?php echo HTML::chars(Arr::get($_POST, "subject")) ?>">
				<div class="error"><?php echo $errors['subject'] ?></div>
			</div>
		</div>
	
		<div class="form-group row">
			<label class="col-lg-3 col-md-3 control-label" for="message"><?php echo I18n::get('message') ?></label>
			<div class="col-lg-5 col-md-6">
				<textarea class="form-control" class="form-control" id="message" name="message" cols="10" rows="6"><?php echo HTML::chars(Arr::get($_POST, "message")) ?></textarea>
				<div class="error" id="emessage"><?php echo $errors['message'] ?></div>
			</div>
		</div>


		<br>
		<p class="text-center"><input class="btn btn-default" type="submit" value="<?php echo I18n::get('submit') ?>" /></p>

	</div>
</form>
<script src="<?php echo $cfg["jquery.js"] ?>"></script>
<script src="<?php echo $cfg["jquery_validate.js"] ?>"></script>
<script>
$(document).ready(function() {
	$('#recipient_username').focus();
	var validator = $('form#fcontent').validate({
		rules: {
			recipient_username: {
				required: true,
				minlength: 3,
				remote: "/json/message_username",
			},
			subject: {
				required: true,
			},
			message: {
				required: true
			}
		},
		messages: {
			recipient_username: {
				remote: jQuery.format("{0} doesn't exist")
			},			
		}
	});
});
</script>