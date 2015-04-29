<form class="form-horizontal" id="fcontent" name="fcontent" action="/listingcontact" method="post">
	<div class="col-lg-offset-2 col-lg-8 col-md-offset-2 col-md-8">
		<h4><?php echo I18n::get('contact_listing_poster') ?></h4>
		<hr>
		<?php if ($error == 1) echo "<div class=\"alert alert-warning alert-block\">" . I18n::get('email_unsent') ."</div>" ?>
		
		<div class="form-group row">
			<label class="col-lg-3 col-md-3 control-label"><?php echo I18n::get('listing_title') ?></label>
			<div class="col-lg-5 col-md-6"><?php echo $title ?></div>
		</div>
		
		<?php
		if ( ! Auth::instance()->logged_in())
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
			<label class="col-lg-3 col-md-3 control-label" for="email"><?php echo I18n::get('message') ?></label>
			<div class="col-lg-5 col-md-6">
				<textarea class="form-control" id="message" name="message" cols="10" rows="6" placeholder="<?php echo HTML::chars(I18n::get('message')) ?>"><?php echo Arr::get($_POST, "message") ?></textarea>
				<div class="error" id="emessage"><?php echo $errors['message'] ?></div>	
			</div>
		</div>	
		<br>
		<p class="text-center"><input class="btn btn-default" type="submit" value="<?php echo I18n::get('submit') ?>" /></p>
	</div>
	<input type="hidden" id="id" name="id" value="<?php echo $id ?>">
	<input type="hidden" id="uid" name="uid" value="<?php echo $uid ?>">
</form>

<script src="<?php echo $cfg["jquery.js"] ?>"></script>
<script src="<?php echo $cfg["jquery_validate.js"] ?>"></script>
<script>
$(document).ready(function() {
	var validator = $('form#fcontent').validate({
		rules: {
			message: {
				required: true,
				minlength: 10
			},
			<?php
			if ( ! Auth::instance()->logged_in())
			{
			?>
			email: {
				required: true,
				email: true
			},
			name: {
				required: true,
				minlength: 1
			}
			<?php
			}
			?>
		}
		
	});
	
});
</script>