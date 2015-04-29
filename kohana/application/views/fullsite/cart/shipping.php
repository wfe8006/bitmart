<form class="form-horizontal" id="fcontent" name="fcontent" action="/cart/shipping" method="post">
	<div class="col-lg-12 col-md-12">
		<h4><?php echo I18n::get('cart') . ' - ' . I18n::get('shipping_address') ?></h4><hr>
		<div class="alert alert-info alert-block<?php echo empty($msg_top) ? " hidden" : "" ?>"><?php echo $msg_top ?></div>	
		<div class="col-lg-offset-2 col-lg-8 col-md-offset-2 col-md-8 col-sm-12">
			<?php
			if (count($array_shipping_address) > 0)
			{
				echo '<div><br><br></div><h5><b>' . I18n::get('select_a_shipping_address') . '</b></h5><hr>';
				$count = 1;
				foreach ($array_shipping_address as $index => $value)
				{
					$address =  HTML::chars($value['address']);
					$address_old = array('&lt;address&gt;', '&lt;/address&gt;', '&lt;b&gt;', '&lt;/b&gt;', '&lt;br&gt;');
					$address_new = array('<address>', '</address>', '<b>', '</b>', '<br>');
					$address = str_replace($address_old, $address_new, $address);
					echo "<div class=\"col-lg-4 col-md-4 col-sm-4\">$address<button type=\"button\" class=\"btn btn-default shipto\" value=\"$index\">" . I18n::get('ship_to_this_address') . "</button><br><br><br></div>";
					if ($count % 3 == 0)
					{
						echo '<div class="clearfix"></div>';
						$count = 0;
					}
					$count++;
				}
			}
			?>
			<div class="clearfix"></div><div><br><br><br><br></div>
			
			<h5><b><?php echo I18n::get('create_a_shipping_address') ?></b></h5><hr>
			<div class="alert alert-info alert-block<?php echo empty($msg) ? " hidden" : "" ?>"><?php echo $msg?></div>	
			
			<div class="spacer row">
				<label class="col-lg-3 col-md-3 control-label" for="firstname"><?php echo I18n::get('firstname') ?></label>
				<div class="col-lg-5 col-md-6">
					<input class="form-control required" id="firstname" maxlength="50" name="firstname" type="text" value="<?php echo HTML::chars(Arr::get($_POST, 'firstname', Auth::instance()->get_user()->firstname)) ?>" placeholder="<?php echo I18n::get('firstname') ?>">
					<div class="error"><?php echo $errors['firstname'] ?></div>
				</div>
			</div>
			
			<div class="spacer row">
				<label class="col-lg-3 col-md-3 control-label" for="lastname"><?php echo I18n::get('lastname') ?></label>
				<div class="col-lg-5 col-md-6">
					<input class="form-control required" id="lastname" maxlength="50" name="lastname" type="text" value="<?php echo HTML::chars(Arr::get($_POST, 'lastname', Auth::instance()->get_user()->lastname)) ?>" placeholder="<?php echo I18n::get('lastname') ?>">
					<div class="error"><?php echo $errors['lastname'] ?></div>
				</div>
			</div>
			
			
			<div class="spacer row">
				<label class="col-lg-3 col-md-3 control-label" for="street1"><?php echo I18n::get('street_address') ?> *</label>
				<div class="col-lg-5 col-md-6">
					<input class="form-control required" id="street1" name="street1" value="<?php echo HTML::chars($street1) ?>" maxlength="100" type="text">
					<div class="error"><?php echo $errors['street1'] ?></div>
				</div>
				
			</div>
			
			<div class="spacer row">
				<label class="col-lg-3 col-md-3 control-label" for="street2"></label>
				<div class="col-lg-5 col-md-6">
					<input class="form-control" id="street2" name="street2" value="<?php echo HTML::chars($street2) ?>" maxlength="100" type="text">
				</div>
			</div>
			<div id="d_layer"></div>
			<div class="spacer row">
				<label class="col-lg-3 col-md-3 control-label" for="country1"><?php echo I18n::get('country') ?> *</label>
				<div class="col-lg-5 col-md-6">
					<select class="form-control" id="country1" name="country1">
						<option value=""><?php echo I18n::get('select_one') ?></option>
						<?php
						foreach ($country_obj as $record)
						{
							$selected = $record['id'] == $country ? ' selected' : '';
							echo "<option value=\"{$record['id']}\"$selected>{$record['name']}</option>";
						}
						?>
					</select>
					<div class="error"><?php echo $errors['country1'] ?></div>
				</div>
			</div>
			<br>
			<p class="text-center"><input class="btn btn-default" type="submit" value="<?php echo I18n::get('submit') ?>" /></p>
		</div>
	</div>
</form>	
<script src="<?php echo $cfg["jquery.js"] ?>"></script>
<script src="<?php echo $cfg["jquery_validate.js"] ?>"></script>
<script src="<?php echo $cfg["library.js"] ?>"></script>
<script>
<?php
if ($array_h_data)
{
	foreach ($array_h_data as $key => $value)
	{
		echo HTML::chars($key) . " = '" . HTML::chars($value) . "';";
	}
}
?>
country_ori = $('#country1').val();
$(document).ready(function() {

	var validator = $('form#fcontent').validate({
		rules: {
			country1: {
				required: true,
				min: 1
			}
		},
		
		messages: {
			country1: "This field is required.",
		}
		
	});

	$('.shipto').each(function(index, element)
	{
		$(element).click(function(){
			window.location.href = "/cart/shipping?id=" + element.value;
		});
	});

});
</script>