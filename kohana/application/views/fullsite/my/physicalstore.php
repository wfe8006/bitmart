<form class="form-horizontal" id="fcontent" name="fcontent" action="/my/physicalstore" method="post">
	<?php include "menu.php"; ?>
	<div class="col80 content-right">
		<h4><?php echo I18n::get('physical_stores') ?></h4><hr>
		<?php
		if ($msg)
		{
			echo "<div class=\"alert alert-$alert_type\">$msg</div>";
		}
		?>
		<a id="store"></a>
		<div class="alert alert-info"><?php echo I18n::get('physical_stores_descriptions') ?></div>
		<div class="spacer row">
			<label class="col-lg-3 col-md-3 control-label" for=""></label>
			<div class="col-lg-5 col-md-6">
				<div class="checkbox"><label><input id="clear" name="clear" value="1" type="checkbox"> <?php echo I18n::get('clear_store_address') ?></label></div>
			</div>
		</div>

		<div class="spacer row">
			<label class="col-lg-3 col-md-3 control-label" for="store_name"><?php echo I18n::get('store_name') ?> *</label>
			<div class="col-lg-5 col-md-6">
				<input class="form-control" id="store_name" name="store_name" value="<?php echo HTML::chars($store_name) ?>" maxlength="100" type="text">
				<div class="error"><?php echo $errors['store_name'] ?></div>
			</div>
		</div>
		
		<div class="spacer row">
			<label class="col-lg-3 col-md-3 control-label" for="street1"><?php echo I18n::get('street_address') ?> *</label>
			<div class="col-lg-5 col-md-6">
				<input class="form-control" id="street1" name="street1" value="<?php echo HTML::chars($street1) ?>" maxlength="100" type="text">
				<div class="error"><?php echo $errors['street1'] ?></div>
			</div>
			
		</div>
		
		<div class="spacer row">
			<label class="col-lg-3 col-md-3 control-label" for="street2"></label>
			<div class="col-lg-5 col-md-6">
				<input class="form-control" id="street2" name="street2" value="<?php echo HTML::chars($street2) ?>" maxlength="100" type="text">
			</div>
		</div>
		
		<div id="d_layer"><?php if (isset($html)) echo $html ?></div>
		<div class="spacer row">
			<label class="col-lg-3 col-md-3 control-label" for="country1"><?php echo I18n::get('country') ?> *</label>
			<div class="col-lg-5 col-md-6">
				<select class="form-control" id="country1" name="country1">
					<option value=""><?php echo I18n::get('select_one') ?></option>
					<?php
					foreach ($country_obj as $result)
					{
						$selected = $result['id'] == $country ? ' selected' : '';
						echo "<option value=\"{$result['id']}\"$selected>{$result['name']}</option>";
					}
					?>
				</select>
				<div class="error"><?php echo $errors['country1'] ?></div>
			</div>
		</div>
		
		<div class="spacer row">
			<label class="col-lg-3 col-md-3 control-label" for="main_phone"><?php echo I18n::get('main_phone') ?> *</label>
			<div class="col-lg-5 col-md-6">
				<input class="form-control" id="main_phone" name="main_phone" value="<?php echo HTML::chars($main_phone) ?>" maxlength="20" type="text">
				<div class="error"><?php echo $errors['main_phone'] ?></div>
			</div>
		</div>
		
		<p class="text-center"><input class="btn btn-default" type="submit" value="<?php echo I18n::get('update') ?>" /></p>
	</div>
</form>
<script src="<?php echo $cfg["jquery.js"] ?>"></script>
<script src="<?php echo $cfg["library.js"] ?>"></script>
<script src="<?php echo $cfg["jquery_validate.js"] ?>"></script>
<script>
<?php
if ($array_h_data)
{
	foreach ($array_h_data as $key => $value)
	{
		echo "$key = '$value';";
	}
}
?>
country_ori = $('#country1').val();
$(document).ready(function() {

	var validator = $('form#fcontent').validate({
		ignore: "",
		rules: {

		},
	
	});
	$('#country1').trigger('change');

});
</script>
