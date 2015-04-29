<form class="form-horizontal" id="fcontent" name="fcontent" action="/my/general" method="post">
	<?php include "menu.php"; ?>
	<div class="col80 content-right">
		<h4><?php echo I18n::get('general') ?></h4><hr>
		<?php
		if ($msg)
		{
			echo "<div class=\"alert alert-$alert_type\">$msg</div>";
		}
		?>
		<!--
		<div class="alert alert-info"><?php echo I18n::get('listing_currency_info') ?></div>
		//-->
		
		<div class="spacer row">
			<label class="col-lg-3 col-md-3 control-label" for="store_name"><?php echo I18n::get('store_name') ?></label>
			<div class="col-lg-5 col-md-6">
				<input class="form-control" id="store_name" maxlength="30" name="store_name" type="text" value="<?php echo HTML::chars($store_name) ?>" placeholder="<?php echo I18n::get('store_name') ?>">
				<div class="error"><?php echo $errors['store_name'] ?></div>

			</div>
		</div>
		
		<div class="spacer row">
			<label class="col-lg-3 col-md-3 control-label" for="currency"><?php echo I18n::get('listing_currency') ?></label>
			<div class="col-lg-5 col-md-6">
				<select class="form-control" id="currency" name="currency">
				<?php
				foreach ($cfg_crypto as $symbol => $value)
				{
					$selected = $symbol == $currency_code ? ' selected' : '';
					echo "<option value=\"$symbol\"$selected>" . strtoupper($symbol) . ' - ' . I18n::get($value['name']) . "</option>";
				}
				echo "<option value\"\" disabled>----------------------------------------</option>";
				foreach($currency_obj as $currency)
				{
					$selected = $currency['iso4217'] == $currency_code ? ' selected' : '';
					echo "<option value=\"{$currency['iso4217']}\"$selected>" . strtoupper($currency['iso4217']) . " - {$currency['name']}</option>";
				}
				?>
				</select>
			</div>
		</div>
		
		<!--
		<div class="spacer row">
			<label class="col-lg-3 col-md-3 control-label" for="currency"><?php echo I18n::get('accept_the_following_cryptocurrencies') ?></label>
			<div class="col-lg-5 col-md-6">
				<?php
				foreach ($cfg_crypto as $symbol => $record)
				{
					$active = $record['active'];
					$name = $record['name'];
					if ($active == 1)
					{
						$checked = $$symbol == 1 ? ' checked' : '';
						echo "<div class=\"checkbox\"><label><input name=\"$symbol\" type=\"checkbox\" class=\"cb\" value=\"1\"$checked> <b>" . strtoupper($symbol) . ' - ' . ucfirst($name) . "</b></label></div>";
					}
				}
				?>
			</div>
		</div>
		//-->
	
		
		<div class="spacer row">
			<label class="col-lg-3 col-md-3 control-label" for="weight_unit"><?php echo I18n::get('weight_unit') ?></label>
			<div class="col-lg-5 col-md-6">
				<select class="form-control" id="weight_unit" name="weight_unit">
					<option value="1"<?php if ($weight_unit == 1) echo ' selected' ?>><?php echo I18n::get('g_gram') ?></option>
					<option value="2"<?php if ($weight_unit == 2) echo ' selected' ?>><?php echo I18n::get('kg_kilogram') ?></option>
					<option value="3"<?php if ($weight_unit == 3) echo ' selected' ?>><?php echo I18n::get('lbs_pound') ?></option>
					<option value="4"<?php if ($weight_unit == 4) echo ' selected' ?>><?php echo I18n::get('oz_ounce') ?></option>
				</select>
			</div>
		</div>
	
		<p class="text-center"><input class="btn btn-default" type="submit" value="<?php echo I18n::get('update') ?>" /></p>
	</div>

</form>
<script src="<?php echo $cfg["jquery.js"] ?>"></script>
<script src="<?php echo $cfg["jquery_validate.js"] ?>"></script>
<script>
$(document).ready(function() {
	var validator = $('form#fcontent').validate({
		rules: {
			store_name: {
				required: true,
			}
		},
	});
});
</script>