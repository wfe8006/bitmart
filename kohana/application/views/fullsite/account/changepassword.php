<form class="form-horizontal" id="fcontent" name="fcontent" action="/account/changepassword" method="post">
	<?php include  __DIR__ . "/../../" . TEMPLATE . "/my/my_menu.php"; ?>
	<div class="col2 col-lg-10 col-md-10">
		<h4><?php echo $header ?></h4>
		<hr>
		<?php
		$secret = $cfg['key'];
		$hashed_secret = $auth->hash_password($auth->get_user()->username . $secret);
		//registered via social account, user didn't change the password, so no password available and we hide the current password field
		if ($auth->get_user()->password == $hashed_secret)
		{
		}
		else
		{
		?>
		<div class="form-group row">
			<label for="opassword" class="col-lg-3 col-md-3 control-label"><?php echo I18n::get('opassword') ?></label>
			<div class="col-lg-5 col-md-6">
				<input class="form-control" id="opassword" maxlength="50" name="opassword" type="password" placeholder="<?php echo I18n::get('opassword') ?>">
				<div class="error" id="opassword"><?php echo $errors['opassword'] ?></div>
			</div>
		</div>
		

		<?php
		}
		?>
		
		<div class="form-group row">
			<label for="npassword" class="col-lg-3 col-md-3 control-label"><?php echo I18n::get('npassword') ?></label>
			<div class="col-lg-5 col-md-6">
				<input class="form-control" id="npassword" maxlength="50" name="npassword" type="password" placeholder="<?php echo I18n::get('npassword') ?>">
				<div class="error" id="npassword"><?php echo $errors['npassword'] ?></div>
			</div>
		</div>
		
		<div class="form-group row">
			<label for="cpassword" class="col-lg-3 col-md-3 control-label"><?php echo I18n::get('rpassword') ?></label>
			<div class="col-lg-5 col-md-6">
				<input class="form-control" id="cpassword" maxlength="50" name="cpassword" type="password" placeholder="<?php echo I18n::get('rpassword') ?>">
				<div class="error" id="cpassword"><?php echo $errors['cpassword'] ?></div>
			</div>
		</div>
		<p class="text-center"><input class="btn btn-default" type="submit" value="<?php echo I18n::get('submit') ?>" /></p>
	</div>
</form>
<script src="<?php echo $cfg["jquery.js"] ?>"></script>
<script src="<?php echo $cfg["jquery_validate.js"] ?>"></script>
<script>
$(document).ready(function() {
	var validator = $('form#fcontent').validate({
		rules: {
			opassword: {
				required: true,
				minlength: 6
			},
			npassword: {
				required: true,
				minlength: 6
			},
			cpassword: {
				required: true,
				minlength: 6,
				equalTo: "#npassword"
			}	
		}
	});
});
</script>