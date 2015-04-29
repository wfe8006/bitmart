<form class="form-horizontal" id="fcontent" name="fcontent" action="/account/reset" method="post">
	<div class="col-lg-offset-2 col-lg-8 col-md-offset-2 col-md-8">
		<h4><?php echo $header ?></h4>
		<hr>
		<div class="alert alert-info alert-block"><?php echo I18n::get('reset.msg_intro') ?></div>
		
		<div class="form-group row">
			<label for="email" class="col-lg-3 col-md-3 control-label"><?php echo I18n::get('email') ?></label>
			<div class="col-lg-5 col-md-6">
				<input class="form-control" id="email" maxlength="100" name="email" type="text" value="<?php echo HTML::chars(Arr::get($_POST, "email")) ?>" placeholder="<?php echo I18n::get('email') ?>">
				<div class="error" id="eemail"><?php echo $errors['email'] ?></div>
			</div>
		</div>

		<br>
		<p class="text-center"><input class="btn btn-default" type="submit" value="<?php echo I18n::get('submit') ?>" /></p>
		
	</div>

<input type="hidden" name="t" value="1">
</form>
<script src="<?php echo $cfg["jquery.js"] ?>"></script>
<script src="<?php echo $cfg["jquery_validate.js"] ?>"></script>
<script>
$(document).ready(function() {
	$('#email').focus();
	var validator = $('form#fcontent').validate({
		rules: {
			email: {
				required: true,
				email: true
			}		
		},
	});
});
</script>