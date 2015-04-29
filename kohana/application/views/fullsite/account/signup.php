<form class="form-horizontal" id="fcontent" name="fcontent" action="/account/signup" method="post">
	<div class="col-lg-offset-2 col-lg-8">
		<h4><?php echo I18n::get('sign_up') ?></h4>
		<hr>
		<div class="form-group row">
			<label for="username" class="col-lg-3 control-label"><?php echo I18n::get('username') ?></label>
			<div class="col-lg-5">
				<input class="form-control" id="username" maxlength="40" name="username" type="text" value="<?php echo HTML::chars(Arr::get($_POST, "username")) ?>" placeholder="<?php echo I18n::get('username') ?>">
				<div class="error"><?php echo $errors['username'] ?></div>
			</div>
		</div>
		
		<div class="form-group row">
			<label for="password" class="col-lg-3 control-label"><?php echo I18n::get('password') ?></label>
			<div class="col-lg-5">
				<input class="form-control" type="password" id="password" name="password" maxlength="50">
				<div class="error"><?php echo $errors['password'] ?></div>
			</div>
		</div>
		
		<div class="form-group row">
			<label for="cpassword" class="col-lg-3 control-label"><?php echo I18n::get('cpassword') ?></label>
			<div class="col-lg-5">
				<input class="form-control" type="password" id="cpassword" name="cpassword" maxlength="50">
				<div class="error"><?php echo $errors['cpassword'] ?></div>
			</div>
		</div>
		
		<div class="form-group row">
			<label for="email" class="col-lg-3 control-label"><?php echo I18n::get('email') ?></label>
			<div class="col-lg-5">
				<input class="form-control" id="email" maxlength="100" name="email" type="text" value="<?php echo HTML::chars(Arr::get($_POST, "email")) ?>" placeholder="<?php echo I18n::get('email') ?>">
				<div class="error"><?php echo $errors['email'] ?></div>
			</div>
		</div>
		
		<br>
		<p class="text-center"><input class="btn btn-default" type="submit" value="<?php echo I18n::get('submit') ?>" /></p>
		<hr>
		<p><center><h4><?php echo I18n::get('or_connect_with') ?></h4></center></p>
		<hr>
		<div id="idps" align="center">
			<div class="col-lg-offset-4 col-lg-1 col-md-3 col-xs-6 text-center"><img class="idpico" idp="Facebook" src="<?php echo $domain ?>/images/icons/facebook.png" title="facebook" /></div>
			<div class="col-lg-1 col-md-3 col-xs-6 text-center"><img class="idpico" idp="Twitter" src="<?php echo $domain ?>/images/icons/twitter.png" title="twitter" /></div>
			<div class="col-lg-1 col-md-3 col-xs-6 text-center"><img class="idpico" idp="Yahoo" src="<?php echo $domain ?>/images/icons/yahoo.png" title="yahoo" /></div>
			<div class="col-lg-1 col-md-3 col-xs-6 text-center"><img class="idpico" idp="Google" src="<?php echo $domain ?>/images/icons/google.png" title="google" /></div>

		</div>
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
			},
			password: {
				required: true,
				minlength: 6
			},
			cpassword: {
				required: true,
				minlength: 6,
				equalTo: "#password"
			},
			email: {
				required: true,
				email: true,
				remote: "/account/signup/process_email"
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
	
	$("img.idpico").click(function () {
		idp = this.attributes["idp"].value;
		switch(idp)
		{ 
			case "Google"  : case "Twitter" : case "Yahoo" : case "Facebook": case "AOL" : 
			case "Vimeo" : case "Myspace" : case "Tumblr" : case "Lastfm" : case "Live" :
			case "linkedin" : 
			start_auth("//<?php echo $cfg["www_domain"] ?>/account/auth/social_login/" + idp);
			break; 
		}
	});

	function start_auth(params)
	{
		start_url = params;
		window.open(
			start_url, 
			"hybridauth_social_sing_on", 
			"location=0,status=0,scrollbars=0,width=800,height=500"
		); 
	}	
});
</script>