<?php
$outer_display = "block";
$nonus_display = "hidden";
$us_display = "hidden";
?>
<form class="form-horizontal" id="fcontent" name="fcontent" action="/preference" method="post">
	<div class="col-lg-offset-2 col-lg-8 col-md-offset-2 col-md-8">
		<h4><?php echo I18n::get('search_preferences') ?></h4><hr>
		<div class="alert alert-info alert-block<?php echo empty($msg) ? " hidden" : "" ?>"><?php echo $msg?></div>	
		
		<div class="form-group row">
			<label class="col-lg-3 col-md-3 control-label" for="convert_currency"></label>
			<div class="col-lg-6 col-md-6">
				<div class="checkbox"><label><input type="checkbox" id="convert_currency" name="convert_currency" value="1"<?php if ($convert_currency == 1) echo ' checked' ?>> <?php echo I18n::get('display_listing_price') ?></label></div>
			</div>
		</div>
		
		<div class="form-group row">
			<label class="col-lg-3 col-md-3 control-label" for="currency"><?php echo I18n::get('currency') ?></label>
			<div class="col-lg-5 col-md-6">
				<select class="form-control" name="currency" id="currency"<?php if ($convert_currency != 1) echo ' disabled' ?>>
					<option value=""><?php echo I18n::get('select_one') ?></option>
					<?php
					foreach ($currency_obj as $result)
					{
						$selected = $result['id'] == $currency ? ' selected' : ''; 
						echo "<option value=\"{$result['id']}\"$selected>" . strtoupper($result['iso4217']) . " - {$result['name']}</option>";
					}
					?>
				</select>
				<div class="error"><?php echo $errors['currency'] ?></div>
			</div>
		</div>
		
		<div class="form-group row">
			<label class="col-lg-3 col-md-3 control-label" for="cryptocurrency"><?php echo I18n::get('only_search_crypto_currency_listing') ?></label>
			<div class="col-lg-5 col-md-6">
				<?php
				foreach ($cfg_crypto as $symbol => $record)
				{
					$active = $record['active'];
					$name = $record['name'];
					if ($active == 1)
					{
						$checked = $$name == 1 ? ' checked' : '';
						echo "<div class=\"checkbox\"><label><input id=\"$name\" name=\"cc[]\" type=\"checkbox\" class=\"cc cb\" value=\"$symbol\"$checked> <b>" . strtoupper($symbol) . ' - ' . ucfirst($name) . "</b></label></div>";
					}
				}
				?>
				<div class="error"><?php echo $errors['cryptocurrency'] ?></div>
			</div>
		</div>
		
		<div id="div_location_outer" style="display: <?php echo $outer_display ?>">
			<div id="div_country" class="spacer row">
				<label class="col-lg-3 col-md-3 control-label" for="country"><?php echo I18n::get('country') ?></label>
				<div class="col-lg-5 col-md-5">
					<select class="form-control" id="country" name="country">
						<option value=""><?php echo I18n::get('select_one') ?></option>
						<?php
						foreach ($country_obj as $record)
						{
							$selected = $country_id == $record['id'] ? " selected" : "";
							echo "<option value=\"" . $record['id'] . "\"$selected>" . $record['name'] . "</option>";
						}
						?>
					</select>
					<div class="error"><?php echo $errors['country'] ?></div>
				</div>
			</div>
			
			<div id="div_location" class="spacer row hidden">
				<label class="col-lg-3 col-md-3 control-label" for="listing_location"><?php echo I18n::get('location') ?></label>
				<div class="col-lg-5 col-md-5">
					<input class="form-control" id="location" name="location" type="text" value="<?php echo HTML::chars($location) ?>" maxlength="80" placeholder="e.g. city, state">
				</div>
			</div>
		
			
			<div id="nonus" style="display: <?php echo $nonus_display ?>">
				<div id="g1" class="spacer row">
					<label class="col-lg-3 col-md-3 control-label" for="geo1"><?php echo I18n::get('location') ?></label>
					<div class="col-lg-5 col-md-3">
						<select class="form-control" id="geo1" name="geo1">
							<option value=""><?php echo I18n::get('select_one') ?></option>
							<?php
							if ($geo1_id > 0)
							{
								echo "<option value=\"$geo1_id\" selected>" . I18n::get('loading') . "</option>";
							}
							?>
						</select>
						<div class="error"><?php echo $errors['geo1'] ?></div>
					</div>
				</div>
				
				<div id="g2" class="spacer row hidden">
					<label class="col-lg-3 col-md-3 control-label" for="geo2"><?php echo I18n::get('area_or_city') ?></label>
					<div class="col-lg-5 col-md-5">
						<select class="form-control" id="geo2" name="geo2">
							<option value=""><?php echo I18n::get('select_one') ?></option>
							<?php
							if ($geo2_id > 0)
							{
								echo "<option value=\"$geo2_id\" selected>" . I18n::get('loading') . "</option>";
							}
							?>
						</select>
						<div class="error"><?php echo $errors['geo2'] ?></div>
					</div>
				</div>
		
			</div>
			
			<div id="us" style="display: <?php echo $us_display ?>">
				
				<div class="spacer row">
					<label class="col-lg-3 col-md-3 control-label" for="zip"><?php echo I18n::get('zip_code') ?></label>
					<div class="col-lg-5 col-md-5">
						<input class="form-control" id="zip" name="zip" value="<?php echo HTML::chars($zip) ?>" maxlength="5" type="text">
						<div class="error"><?php echo $errors['zip'] ?></div>
					</div>
				</div>
				
			</div>
		</div>
		

		<p class="text-center"><input class="btn btn-default" type="submit" value="<?php echo I18n::get('submit') ?>" /></p>
		<?php
		if ($session->get('requested_url') != '')
		{
		?>
		<hr>
		<div class="form-group row">
			<div class="col-lg-12">
				<center><a class="btn btn-default" href="<?php echo $session->get('requested_url') ?>"><?php echo I18n::get('Back') ?></a></center>
			</div>
		</div>
		<?php
		}
		?>
	</div>
	<input type="hidden" id="h_geo1" name="h_geo1" value="<?php echo $h_geo1 ?>">
	<input type="hidden" id="h_geo2" name="h_geo2" value="<?php echo $h_geo2 ?>">
</form>
<script src="<?php echo $cfg["jquery.js"] ?>"></script>
<script src="<?php echo $cfg["library.js"] ?>"></script>
<script>
$(document).ready(function() 
{
	$("#convert_currency").click(function() 
	{
		if ($("#convert_currency").prop('checked'))
		{
			$('#currency').removeAttr("disabled");
		}
		else
		{
			$('#currency').attr("disabled", "disabled");
		}
	}); 
	$('#country').trigger('change');
	
	$('.cc').each(function(index, element)
	{
		$(element).click(function(){
			id = element.id;
			if (element.checked)
			{
				$('.cc').removeAttr('checked');
				$('#' + id).prop('checked','checked');
			}
		});
		
	});
	
	
});



		

</script>