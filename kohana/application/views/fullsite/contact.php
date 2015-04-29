<form class="form-horizontal" id="fcontent" name="fcontent" action="/contact" method="post">
	<div class="col-lg-offset-2 col-lg-8 col-md-offset-2 col-md-8">
		<h4><?php echo I18n::get('contact_us') ?></h4>
		<hr>
		<div class="alert alert-info alert-block">Thank you for contacting <?php echo $cfg["site_name"] ?>. Your comments and suggestions are very important to us. Please use the form below to provide your feedback or report service-related problems. Your feedback will allow us improve our services it is completely confidential.</div>

	<?php
	if ( ! $logged_in)
	{
	?>
		<div class="form-group row">
			<label class="col-lg-3 col-md-3 control-label" for="name"><?php echo I18n::get('your_name') ?></label>
			<div class="col-lg-5 col-md-6">
				<input class="form-control" id="name" maxlength="100" name="name" type="text" value="<?php echo HTML::chars(Arr::get($_POST, "name")) ?>" placeholder="<?php echo I18n::get('your_name') ?>">
				<div class="error"><?php echo $errors['name'] ?></div>
			</div>
		</div>
			
		<div class="form-group row">
			<label class="col-lg-3 col-md-3 control-label" for="email"><?php echo I18n::get('your_email') ?></label>
			<div class="col-lg-5 col-md-6">
				<input class="form-control" id="email" maxlength="100" name="email" type="text" value="<?php echo HTML::chars(Arr::get($_POST, "email")) ?>" placeholder="<?php echo I18n::get('your_email') ?>">
				<div class="error"><?php echo $errors['email'] ?></div>
			</div>
		</div>
	<?php
	}
	?>
		<div class="form-group row">
			<label class="col-lg-3 col-md-3 control-label" for="message"><?php echo I18n::get('message') ?></label>
			<div class="col-lg-5 col-md-6">
				<textarea class="form-control" class="form-control" id="message" name="message" cols="10" rows="6" placeholder="<?php echo I18n::get('message') ?>"><?php echo HTML::chars(Arr::get($_POST, "message")) ?></textarea>
				<div class="error" id="emessage"><?php echo $errors['message'] ?></div>
			</div>
		</div>

	<!--
	<label><?php //echo I18n::get('validation_code') ?></label>
	<span class="row"><?php //echo $recaptcha ?></span>
	<div class="error"><?php //echo empty($errors['recaptcha_response_field']) ? '' : $errors['recaptcha_response_field'] ?></div>
	<br>
	//-->
		<br>
		<p class="text-center"><input class="btn btn-default" type="submit" value="<?php echo I18n::get('submit') ?>" /></p>

	</div>
</form>
<script src="<?php echo $cfg["jquery.js"] ?>"></script>
<script src="<?php echo $cfg["jquery_validate.js"] ?>"></script>
<script>
$(document).ready(function() {
	$('#name').focus();
	var validator = $('form#fcontent').validate({
		rules: {
			name: {
				required: true
			},
			email: {
				required: true,
				email: true
			},
			message: {
				required: true
			}
		}
	});
});
</script>