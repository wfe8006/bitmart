<?php
$email_domain = explode("@", Auth::instance()->get_user()->email);
$email = Auth::instance()->get_user()->email;
?>
<form class="form-horizontal" id="fcontent" name="fcontent" action="/account/profile" method="post">
	<?php include  __DIR__ . "/../../" . TEMPLATE . "/my/my_menu.php"; ?>
	
	<div class="col80 content-right">
		<h4><?php echo I18n::get('profile') ?></h4><hr>
		<div class="alert alert-success<?php echo empty($msg) ? " hidden" : "" ?>"><?php echo $msg?></div>	
		
		<div class="spacer row">
			<label class="col-lg-3 col-md-3 control-label" for="username"><?php echo I18n::get('username') ?></label>
			<div class="col-lg-5 col-md-6">
				<input class="form-control" id="username" maxlength="40" name="username" type="text" value="<?php echo HTML::chars(Arr::get($_POST, 'username', Auth::instance()->get_user()->username)) ?>" placeholder="<?php echo I18n::get('username') ?>" disabled>
				<div class="error"><?php echo $errors['username'] ?></div>
			</div>
		</div>
		
		<div class="spacer row">
			<label class="col-lg-3 col-md-3 control-label" for="password"><?php echo I18n::get('password') ?></label>
			<div class="col-lg-5 col-md-6">
				<input class="form-control" id="password" class="disabled" name="password" type="text" value="************" maxlength="50" disabled>
				 <a class="link" href="/account/changepassword"><?php echo I18n::get('change_password') ?></a>
			</div>
		</div>

		<div class="spacer row">
			<label class="col-lg-3 col-md-3 control-label" for="email"><?php echo I18n::get('email') ?></label>
			<div class="col-lg-5 col-md-6">
				<input class="form-control" id="email" maxlength="50" name="email" type="text" value="<?php echo HTML::chars($email) ?>" placeholder="<?php echo I18n::get('email') ?>">
				<div class="error"><?php echo $errors['email'] ?></div>
			</div>
		</div>
		
		<div class="spacer row">
			<label class="col-lg-3 col-md-3 control-label" for="firstname"><?php echo I18n::get('firstname') ?></label>
			<div class="col-lg-5 col-md-6">
				<input class="form-control" id="firstname" maxlength="50" name="firstname" type="text" value="<?php echo HTML::chars(Arr::get($_POST, 'firstname', Auth::instance()->get_user()->firstname)) ?>" placeholder="<?php echo I18n::get('firstname') ?>">
				<div class="error"><?php echo $errors['firstname'] ?></div>
			</div>
		</div>
		
		<div class="spacer row">
			<label class="col-lg-3 col-md-3 control-label" for="lastname"><?php echo I18n::get('lastname') ?></label>
			<div class="col-lg-5 col-md-6">
				<input class="form-control" id="lastname" maxlength="50" name="lastname" type="text" value="<?php echo HTML::chars(Arr::get($_POST, 'lastname', Auth::instance()->get_user()->lastname)) ?>" placeholder="<?php echo I18n::get('lastname') ?>">
				<div class="error"><?php echo $errors['lastname'] ?></div>
			</div>
		</div>
		
		<div class="spacer row">
			<label class="col-lg-3 col-md-3 control-label" for="gender"><?php echo I18n::get('gender') ?></label>
			<div class="col-lg-5 col-md-6">
				<input type="radio" id="gender1" name="gender" value="1"<?php if (Arr::get($_POST, 'gender', Auth::instance()->get_user()->gender) == 1) echo " checked" ?>> <?php echo I18n::get('male') ?> &nbsp;
				<input type="radio" id="gender2" name="gender" value="0"<?php if (Arr::get($_POST, 'gender', Auth::instance()->get_user()->gender) == 0) echo " checked" ?>> <?php echo I18n::get('female') ?>
			</div>
		</div>

		<div class="spacer row">
			<label class="col-lg-3 col-md-3 control-label" for="dob"><?php echo I18n::get('dob') ?></label>
			<div class="col-lg-5 col-md-6">
				<select class="form-control" name="month" id="month">
					<option value="-"><?php echo I18n::get('month') ?></option>
					<?php
					for ($i = 1; $i < 13; $i++)
					{ 
						$selected = $i == Arr::get($_POST, 'month', substr(Auth::instance()->get_user()->dob, 5, 2)) ? ' selected' : '';
						echo "<option value=\"$i\"$selected>" . date("m", mktime(0, 0, 0, $i)) . "</option>";
					}
					?>
				</select> 
				<select class="form-control" name="day" id="day">
					<option value="-"><?php echo I18n::get('day') ?></option>
					<?php 
					for ($i = 1; $i < 32; $i++)
					{ 
						$selected = $i == Arr::get($_POST, 'day', substr(Auth::instance()->get_user()->dob, 8, 2)) ? ' selected' : '';
						echo "<option value=\"$i\"$selected>$i</option>";
					}
					?>
				</select> 
				<select class="form-control" name="year" id="year">
					<option value="-"><?php echo I18n::get('year') ?></option>
					<?php
					$this_year = date("Y");
					for ($i = $this_year; $i > $this_year - 89; $i--)
					{ 
						$selected = $i == Arr::get($_POST, 'year', substr(Auth::instance()->get_user()->dob, 0, 4)) ? ' selected' : '';
						echo "<option value=\"$i\"$selected>$i</option>";
					}
					?>
				</select>
				
				<div class="error"><?php echo $errors['dob'] ?></div>
			</div>
		</div>
		
		<div class="spacer row">
			<label class="col-lg-3 col-md-3 control-label" control-label" for="country"><?php echo I18n::get('country') ?></label>
			<div class="col-lg-5 col-md-6">
				<select class="form-control" name="country" id="country">
					<option value="0"><?php echo I18n::get('select_one') ?></option>
					<?php
					foreach ($country_result as $bl)
					{
						$selected = $bl['id'] == Arr::get($_POST, 'country', Auth::instance()->get_user()->country_id) ? ' selected' : ''; 
						echo "<option value=\"{$bl['id']}\"$selected>{$bl['name']}</option>";
					}
					?>
				</select>
			</div>
		</div>

		<div class="spacer row">
			<label class="col-lg-3 col-md-3 control-label" for="phone"><?php echo I18n::get('phone_number') ?></label>
			<div class="col-lg-5 col-md-6">
				<input class="form-control" id="phone" maxlength="50" name="phone" type="text" value="<?php echo HTML::chars(Arr::get($_POST, 'phone', Auth::instance()->get_user()->phone)) ?>" placeholder="<?php echo I18n::get('phone') ?>">
				<div class="error"><?php echo $errors['phone'] ?></div>
			</div>
		</div>

		<br>
		<p class="text-center"><input class="btn btn-default" type="submit" value="<?php echo I18n::get('update') ?>" /></p>
	</div>
	<input type="hidden" id="u" name="u" value="<?php echo Arr::get($_POST, 'username', Auth::instance()->get_user()->username) ?>">
</form>
<script src="<?php echo $cfg["jquery.js"] ?>"></script>
<script src="<?php echo $cfg["jquery_validate.js"] ?>"></script>
<script>
$(document).ready(function() {
	var validator = $('form#fcontent').validate({
		rules: {
			username: {
				required: true,
				minlength: 3,
				remote: "/account/profile/process_username"
			},
			email: {
				required: true,
				email: true,
				remote: "/account/profile/process_email"
			},
			phone: {
				minlength: 6,
				maxlength: 11
			}
		},
		messages: {
			username: {
				remote: jQuery.format("{0} is already in use")
			},
			email: {
				remote: jQuery.format("{0} is already in use")
			}
		}
	});
});
</script>