<form class="form-horizontal" id="fcontent" name="fcontent" action="/account/auth/set_username" method="post">
	<div class="col-lg-offset-2 col-lg-8 col-md-offset-2 col-md-8">
		<h4><?php echo I18n::get('set_a_username') ?></h4><hr>
		<div class="alert alert-success"><?php echo I18n::get('set_a_username_msg') ?></div>	
		<div class="form-group row">
			<label for="username" class="col-lg-3 col-md-3 control-label"><?php echo I18n::get('username') ?></label>
			<div class="col-lg-5 col-md-6">
				<input class="form-control" id="username" maxlength="40" name="username" type="text" value="<?php echo HTML::chars(Arr::get($_POST, "username")) ?>" placeholder="<?php echo I18n::get('username') ?>">
				<div class="error"><?php echo $errors['username'] ?></div>
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
	$('#username').focus();
	var validator = $('form#fcontent').validate({
		rules: {
			username: {
				required: true,
				minlength: 3,
				remote: "/account/signup/process_username"
			}
		},
		messages: {
			username: {
				remote: jQuery.format("{0} is already in use")
			}
		}
	});
});
</script>