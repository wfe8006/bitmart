<form class="form-horizontal" id="fcontent" name="fcontent" action="/account/auth/login" method="post">
	<div class="col-lg-offset-2 col-lg-8 col-md-offset-2 col-md-8">
		<h4><?php echo I18n::get('log_in') ?></h4>
		<hr>
		<div class="alert alert-warning alert-block<?php echo empty($errors['invalid_login']) ? " hidden" : "" ?>"><?php echo $errors['invalid_login']?></div>
		
		<div class="form-group row">
			<label for="username" class="col-lg-3 col-md-3 control-label"><?php echo I18n::get('username') ?></label>
			<div class="col-lg-5 col-md-6">
				<input class="form-control" id="username" maxlength="40" name="username" type="text" value="<?php echo HTML::chars(Arr::get($_POST, "username")) ?>" placeholder="<?php echo I18n::get('username') ?>">
				<div class="error"><?php echo $errors['username'] ?></div>
			</div>
		</div>
		
		<div class="form-group row">
			<label for="password" class="col-lg-3 col-md-3 control-label"><?php echo I18n::get('password') ?></label>
			<div class="col-lg-5 col-md-6">
				<input class="form-control" id="password" maxlength="40" name="password" type="password">
				<div class="error"><?php echo $errors['password'] ?></div>
			</div>
		</div>
		
		<div class="form-group">
			<label for="remember" class="col-lg-3 col-md-3 control-label"></label>
			<div class="col-lg-5 col-md-6">
				<div class="checkbox">
					<label><input id="remember" name="remember" type="checkbox" value="1"> <?php echo I18n::get('remember_me') ?></label>&nbsp;&nbsp;&nbsp;&nbsp;<a href="/account/reset"><?php echo I18n::get('forgot_your_password') ?></a>
				</div>
			</div>
		</div>

		<br>
		<p class="text-center"><input class="btn btn-default" type="submit" value="<?php echo I18n::get('login') ?>" /></p>
				
		<hr>
		<p><center><h4><?php echo I18n::get('or_connect_with') ?></h4></center></p>
		<hr>
		<div id="idps" align="center">
			<div class="col-lg-offset-4 col-lg-1 col-md-3 col-xs-6 text-center"><img class="idpico" idp="Facebook" src="<?php echo $domain ?>/images/icons/facebook.png" title="facebook" /></div>
			<div class="col-lg-1 col-md-3 col-xs-6 text-center"><img class="idpico" idp="Twitter" src="<?php echo $domain ?>/images/icons/twitter.png" title="twitter" /></div>
			<div class="col-lg-1 col-md-3 col-xs-6 text-center"><img class="idpico" idp="Yahoo" src="<?php echo $domain ?>/images/icons/yahoo.png" title="yahoo" /></div>
			<div class="col-lg-1 col-md-3 col-xs-6 text-center"><img class="idpico" idp="Google" src="<?php echo $domain ?>/images/icons/google.png" title="google" /></div>

		</div>
		<p><br><br><br></p>
		<hr>
		<p><center><a class="btn btn-default" href="/account/signup"><?php echo I18n::get('sign_up') ?></a></center></p>
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
				required: true
			},
			password: {
				required: true
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