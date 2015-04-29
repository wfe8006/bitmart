<form class="form-horizontal" id="fcontent" name="fcontent" action="/account/reset" method="post">
	<div class="col-lg-offset-2 col-lg-8">
		<h4><?php echo I18n::get('reset_password') ?></h4>
		<hr>
		<div class="alert alert-info alert-block"><?php echo I18n::get('reset.msg_new_password') ?></div>
		
		<div class="form-group row">
			<label for="email" class="col-lg-3 col-md-3 control-label"><?php echo I18n::get('email') ?></label>
			<div class="col-lg-5 col-md-6">
				<input class="form-control" id="email" maxlength="50" name="email" type="text" value="<?php echo $email ?>" disabled>
			</div>
		</div>
		
		<div class="form-group row">
			<label for="npassword" class="col-lg-3 col-md-3 control-label"><?php echo I18n::get('npassword') ?></label>
			<div class="col-lg-5 col-md-6">
				<input class="form-control" id="npassword" maxlength="50" name="npassword" type="password" placeholder="<?php echo I18n::get('npassword') ?>">
				<div class="error"><?php echo $errors['npassword'] ?></div>
			</div>
		</div>
		
		<div class="form-group row">
			<label for="cpassword" class="col-lg-3 col-md-3 control-label"><?php echo I18n::get('cpassword') ?></label>
			<div class="col-lg-5 col-md-6">
				<input class="form-control" id="cpassword" maxlength="50" name="cpassword" type="password" placeholder="<?php echo I18n::get('cpassword') ?>">
				<div class="error"><?php echo $errors['cpassword'] ?></div>
			</div>
		</div>
		
		<br>
		<p class="text-center"><input class="btn btn-default" type="submit" value="<?php echo I18n::get('submit') ?>" /></p>
	</div>	

<input type="hidden" name="t" value="2">
<input type="hidden" name="code" value="<?php echo Arr::get($_GET, 'code', Arr::get($_POST, 'code')) ?>">
<input type="hidden" name="email" value="<?php echo Arr::get($_GET, 'email', Arr::get($_POST, 'email')) ?>">
</form>
<script src="<?php echo $cfg["jquery.js"] ?>"></script>
<script src="<?php echo $cfg["jquery_validate.js"] ?>"></script>
<script>
$(document).ready(function() {
	$('#npassword').focus();
	var validator = $('form#fcontent').validate({
		rules: {
			npassword: {
				required: true,
				minlength: 6,
				maxlength: 50
			},
			cpassword: {
				required: true,
				minlength: 6,
				equalTo: "#npassword"
			}
		},
	});
});
</script>