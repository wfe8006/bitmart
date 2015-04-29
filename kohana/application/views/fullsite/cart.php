<form id="fcontent" name="fcontent" action="/cart" method="post">
	<div class="col-lg-12 col-md-12">
		<h4><?php echo I18n::get('cart') ?></h4><hr>
		<?php
		if ($msg)
		{
			echo "<div class=\"alert alert-success\">$msg</div>";
		}

		include  __DIR__ . "/../../classes/controller/country_list.php"; 
		
		$cryptocurrency = 0;
		if (count($array_listing) > 0)
		{
			$cookie_preference = Cookie::get("preference", 0);

			if ($cookie_preference !== 0)
			{
				$cookie_preference = json_decode($cookie_preference);
				$currency_code = $cookie_preference->currency_code;
				$convert_currency = $cookie_preference->convert_currency;
				$cryptocurrency = $cookie_preference->cryptocurrency;
			}
			else
			{
				$currency_code = 'usd';
				$convert_currency = 0;
			}
			?>

			<?php
			$counter = 1;
			$array_js = array();
			foreach ($array_listing as $seller_id => $listing)
			{
				$count = 1;
				$valid_cryptocurrency = 0;
				$payment_method = '';
				if ($cryptocurrency !== 0)
				{
					if (count($array_payment_method[$seller_id]) > 0)
					{
						foreach ($array_payment_method[$seller_id] as $value => $name)
						{
							if (array_key_exists($value, $cfg_crypto))
							{
								//to hacking prevention, user might have some unknown coin eg 'ZZC' in cookie cryptocurrency value, we will make sure the cookie data is valid in the first place, if ZZC is not found, set valid_cryptocurrency = 0 and use the first record in array_payment_method as default payment option
								$valid_cryptocurrency = 1;
							}
						}
					}
				}
			
				$count = 1;
				if ($valid_cryptocurrency == 0)
				{
					
					$cryptocurrency = 0;
					if (count($array_payment_method[$seller_id]) > 0)
					{
						foreach ($array_payment_method[$seller_id] as $value => $name)
						{
							$checked = $count == 1 ? ' checked' : '';
							$payment_method .= "<div><span class=\"radio\"><input id=\"pm_{$seller_id}_{$value}\" class=\"pm\" type=\"radio\" name=\"pm_$seller_id\" value=\"$value\"$checked>$name</span></div>";
							$count++;
						}
					}
				}
				else
				{
					if (count($array_payment_method[$seller_id]) > 0)
					{
						foreach ($array_payment_method[$seller_id] as $value => $name)
						{
							
							if ($value == $cryptocurrency AND array_key_exists($value, $cfg_crypto) AND $count == 1)
							{
								$checked = ' checked';
								$count++;
							}
							else
							{
								$checked = '';
							}
							$payment_method .= "<div><span class=\"radio\"><input id=\"pm_{$seller_id}_{$value}\" class=\"pm\" type=\"radio\" name=\"pm_$seller_id\" value=\"$value\"$checked>$name</span></div>";
							
						}
					}
				}
				
				$new_grand_subtotal = 0.00;
				$ori_grand_subtotal = 0.00;
				$has_shipping = $array_listing[$seller_id]['has_shipping'];
	
	
				?>
				<!--
				<table class="table">
				<tr>
					<td></td>
					<td><b><?php echo I18n::get('listing') ?></b></td>
					<td class="text-right"><b><?php echo I18n::get('unit_price') ?></b></td>
					<td><b><?php echo I18n::get('quantity') ?></b></td>
					<td class="text-right"><b><?php echo I18n::get('total') ?></b></td>
				</tr>
				//-->
				<div class="media">
					<div class="media-body">
						<span class="col-lg-1 col-md-1 col-xs-1 hidden-xs"></span>
						<span class="col-lg-9 col-md-9 col-xs-6"><b><?php echo I18n::get('item') ?></b></span>
						<!--<span class="col-lg-2 col-md-2 text-right"><b><?php echo I18n::get('unit_price') ?></b></span>//-->
						<span class="col-lg-2 col-md-2 col-xs-6 text-right"><b><?php echo I18n::get('subtotal') ?></b></span>
					</div>
				</div>

				<hr>

				<?php
				foreach ($listing as $ld_id => $result)
				{
					if ($ld_id != 'has_shipping')
					{
						if ($counter > 4)
						{
							$counter = 1;
						}
						if ($result['img_count'] > 0 OR $result['object_type_id'] == 2)
						{
							$img_path = implode('/', str_split($result['uid'], 2));
							$img_path = substr($img_path, 0, 8);
							$image = "//{$cfg["www_domain"]}/{$cfg["bucket_name"]}/70/$img_path/{$result['uid']}_1.jpg";
						}
						else
						{
							$image = "//".$cfg["static_domain"] . "/img/80_no_image.jpg";
						}
						$img = "<div class=\"displayed\"><img class=\"wh\" src=\"$image\"></div>";
						
						
						
						
						if ($cryptocurrency === 0)
						{
							if ($convert_currency == 1)
							{
								//convert currency: convert from one currency to another
								$array_cart[$seller_id]['f'] = $currency_code;
								$array_cart[$seller_id]['t'] = $result['currency_code'];
						
								$new_currency = $result['currency_code'];
								$new_price = $result['price'];
								$new_subtotal = sprintf("%0.2f", $result['quantity'] * $result['price']);
								$new_grand_subtotal += sprintf("%0.2f", $new_subtotal);
						
								$ori_currency = $currency_code;
								$ori_price = sprintf("%0.2f", $result['price_usd'] * $cfg_currency['usd_' . $ori_currency]);
								$ori_subtotal = sprintf("%0.2f", $result['quantity'] * $result['price_usd'] * $cfg_currency['usd_' . $ori_currency]);
								$ori_grand_subtotal += sprintf("%0.2f", $ori_subtotal);
								//display currency converted from message
								$show_display = 1;
								
								//subtotal price in original non-crypto currency format posted by the lister, used to calculate taxable grand subtotal, without this, the subtotal item, in this block will be calculated in USD, instead of its original currency AMD
								//$listing_subtotal = $new_subtotal;
							}
							else		
							{
								$array_cart[$seller_id]['f'] = $result['currency_code'];
								$array_cart[$seller_id]['t'] = $result['currency_code'];
							
								$new_currency = $result['currency_code'];
								$new_price = $result['price'];
								$new_subtotal = sprintf("%0.2f", $result['quantity'] * $result['price']);
								$new_grand_subtotal += sprintf("%0.2f", $new_subtotal);
								
								$ori_currency = $result['currency_code'];
								$ori_price = $result['price'];
								$ori_subtotal = sprintf("%0.2f", $result['quantity'] * $result['price']);
								$ori_grand_subtotal += sprintf("%0.2f", $ori_subtotal);
								
								$show_display = 0;
							}
							$listing_subtotal = $new_subtotal;
						}
						else
						{
							//convert currency: convert from one currency to another
							//$cryptocurrency = $cryptocurrency;
							
	
							if ($convert_currency == 1)
							{
								$array_cart[$seller_id]['f'] = $currency_code;
								$array_cart[$seller_id]['t'] = $cryptocurrency;
								
								$new_currency = $cryptocurrency;
								$new_price = sprintf("%0.5f", $cfg_currency['usd_' . $cryptocurrency] * $result['price_usd']);
								$new_subtotal = sprintf("%0.5f", $result['quantity'] * $cfg_currency['usd_' . $cryptocurrency] * $result['price_usd']);
								$new_grand_subtotal += $new_subtotal;
						
								$ori_currency = $currency_code;
								$ori_price = sprintf("%0.2f", $result['price_usd'] * $cfg_currency['usd_' . $ori_currency]);
								$ori_subtotal = sprintf("%0.2f", $result['quantity'] * $result['price_usd'] * $cfg_currency['usd_' . $ori_currency]);
								$ori_grand_subtotal += sprintf("%0.2f", $ori_subtotal);
								
								$show_display = 1;
							}
							else
							{
								$array_cart[$seller_id]['f'] = $result['currency_code'];
								$array_cart[$seller_id]['t'] = $cryptocurrency;	
								
								if ($result['currency_code'] == $cryptocurrency)
								{
									$new_currency = $result['currency_code'];
									$new_price = sprintf("%0.5f", $result['price']);
									$new_subtotal = sprintf("%0.5f", $result['quantity'] * $result['price']);
									$new_grand_subtotal += $new_subtotal;
									
									$ori_currency = $result['currency_code'];
									$ori_price = sprintf("%0.5f", $result['price']);
									$ori_subtotal = sprintf("%0.5f", $result['quantity'] * $result['price']);
									$ori_grand_subtotal += $ori_subtotal;
									$show_display = 0;
								}
								else
								{
									$new_currency = $cryptocurrency;
									$new_price = sprintf("%0.5f", $cfg_currency['usd_' . $cryptocurrency] * $result['price_usd']);
									$new_subtotal = sprintf("%0.5f", $result['quantity'] * $cfg_currency['usd_' . $cryptocurrency] * $result['price_usd']);
									$new_grand_subtotal += $new_subtotal;
									
									$ori_currency = $result['currency_code'];
									$ori_price = sprintf("%0.2f", $result['price']);
									$ori_subtotal = sprintf("%0.2f", $result['quantity'] * $result['price']);
									$ori_grand_subtotal += $ori_subtotal;
									
									$show_display = 1;
								
								}
								
								
							}
							$listing_subtotal = sprintf("%0.2f", $result['quantity'] * $result['price']);
							
						}
						
						/*
							param 1 = non-crypto price (eg USD, AUD, CAD) during the first load, printed in php, not calculated using javascript
							param 2 = price converted in usd for calculation later
							param 3 = quantity
						*/
						$array_js[$seller_id][$ld_id] = array($result['price'], $result['price_usd'], $result['quantity']);
						//$array_js[$seller_id][$ld_id] = array($ori_price, $result['price_usd'], $result['quantity']);
	
				?>

		<!--
		<tr>
			<td class="col-lg-1 col-md-1"><?php echo $img ?></td>
			<td><b><a href="<?php echo '/st/' . $result['uid'] ?>"><?php echo HTML::chars($result['title']) ?></a></b><br><br><a href="javascript:void(false)" id="delete_<?php echo $ld_id ?>" class="button"><?php echo I18n::get('delete') ?></a></td>
			<td class="col-lg-2 col-md-2 text-right" id="unit_price_<?php echo $ld_id ?>"><?php echo HTML::chars($new_price) . ' ' . strtoupper($new_currency) ?><?php echo $show_display == 1 ? "<br><span class=\"text-muted\">" . HTML::chars($ori_price) . ' ' . strtoupper($ori_currency) . "</span>" : '' ?></td>
			<td class="col-lg-1 col-md-1"><input class="input-small form-control" id="quantity_<?php echo $ld_id ?>" name="quantity_<?php echo $ld_id ?>" type="text" value="<?php echo HTML::chars($result['quantity']) ?>"><a href="javascript:void(false)" id="update_<?php echo $ld_id ?>" class="button"><?php echo I18n::get('update') ?></a></td>
			<td class="col-lg-2 col-md-2 text-right" id="price_<?php echo $ld_id ?>"><?php echo HTML::chars($new_subtotal) . ' ' . strtoupper($new_currency) ?><?php echo $show_display == 1 ? "<br><span class=\"text-muted\">" . HTML::chars($ori_subtotal) . ' ' . strtoupper($ori_currency) . "</span>" : '' ?></td>
		</tr>
		//-->
		<div class="media">
			
			<div class="media-body">
				<span class="col-lg-1 col-md-1 col-sm-1 hidden-xs"><?php echo $img ?></span>
				<span class="col-lg-9 col-md-9 col-sm-9 col-xs-6"><b><a href="<?php echo '/st/' . $result['uid'] ?>"><?php echo HTML::chars($result['title']) ?></a></b>
				
				

				</span>
				<!--<span class="col-lg-2 col-md-2 text-right" id="unit_price_<?php echo $ld_id ?>"><?php echo HTML::chars($new_price) . ' ' . strtoupper($new_currency) ?><?php echo $show_display == 1 ? "<br><span class=\"text-muted\">" . HTML::chars($ori_price) . ' ' . strtoupper($ori_currency) . "</span>" : '' ?></span>//-->
				
				
				
				
				
				
				
				<span class="col-lg-2 col-md-2 col-sm-2 col-xs-6 text-right" id="price_<?php echo $ld_id ?>"><?php echo HTML::chars($new_subtotal) . ' ' . strtoupper($new_currency) ?><?php echo $show_display == 1 ? "<br><span class=\"text-muted\">" . HTML::chars($ori_subtotal) . ' ' . strtoupper($ori_currency) . "</span>" : '' ?></span>
			</div>
			<br>
			<div>
				<span class="col-lg-1 col-md-1 col-sm-1 hidden-xs"></span>
				<span class="col-lg-11 col-md-11 col-sm-11 col-xs-11">
					<div class="col-lg-3 col-md-3 col-sm-4 col-xs-12">
					<div class="input-group">
					  <span><input class="input-small form-control" id="quantity_<?php echo $ld_id ?>" name="quantity_<?php echo $ld_id ?>" type="text" value="<?php echo HTML::chars($result['quantity']) ?>" placeholder="<?php echo I18n::get('qty') ?>"></span>
					  <span class="input-group-btn">
						<a class="button btn btn-default" href="javascript:void(false)" id="update_<?php echo $ld_id ?>" class="button"><?php echo I18n::get('update') ?></a>
						<a class="button btn btn-default" href="javascript:void(false)" id="delete_<?php echo $ld_id ?>" class="button"><?php echo I18n::get('delete') ?></a>
					  </span>
					</div>
					</div>
				</span>

			</div>
			<br>
			<br>
			<hr>
		</div>
		
		
				<?php
						//calculate tg = taxable grand subtotal, used by /json/get_estimate, grand subtotal only includes physical products. 
						if ($result['has_shipping'] == 1)
						{
							$taxable_orig_grand_subtotal += $listing_subtotal;
						}
					}
				}
				//echo "</table>";
				$ori_grand_subtotal = sprintf("%0.2f", $ori_grand_subtotal);
				if ($cryptocurrency === 0)
				{
					$new_grand_subtotal = sprintf("%0.2f", $new_grand_subtotal);
					$array_cart[$seller_id]['tg'] = sprintf("%0.2f", $taxable_orig_grand_subtotal);
					$array_cart[$seller_id]['s'] = $has_shipping;
				}
				else
				{
					$new_grand_subtotal = sprintf("%0.5f", $new_grand_subtotal);
					$array_cart[$seller_id]['tg'] = sprintf("%0.5f", $taxable_orig_grand_subtotal);
					$array_cart[$seller_id]['s'] = $has_shipping;
				}

				?>
		<!--<input type="hidden" id="hs_<?php echo $seller_id ?>" value="<?php echo $has_shipping ?>">//-->
		<input type="hidden" id="ori_currency_<?php echo $seller_id ?>" value="<?php echo $array_cart[$seller_id]['f'] ?>">
		<div class="col-lg-12 text-right" id="grand_subtotal_<?php echo $seller_id ?>"><?php echo I18n::get('subtotal') ?>: <?php echo "$new_grand_subtotal" . ' ' . strtoupper($new_currency) ?><?php echo $show_display == 1 ? "<br><span class=\"text-muted\">" . HTML::chars($ori_grand_subtotal) . ' ' . strtoupper($ori_currency) . "</span>" : '' ?></div>
		<br><br>
		

		
		<div class="col-lg-offset-3 col-lg-6 col-md-offset-3 col-md-6 panel panel-default">
			<div class="panel-body col-lg-12 col-md-12">
				
			
				<?php
				if (count($array_payment_method) > 0)
				{
				?>
				<div class="col-lg-12 col-md-12 text-left">
					<div class="form-group row">
						<p></p>
						<h4 class="text-left"><?php echo I18n::get('payment_method') ?></h4>
						<hr>
						<?php
							echo $payment_method;
						?>
					</div>
				</div>
				<?php
				}
			

				if ($has_shipping == 1)
				{
					if ($country == '')
					{
						$country = 244;
					}
				
				
				?>
				
				<div class="col-lg-12 col-md-12">
					<div class="form-group row">
						<p><br><br></p>
						<h4 class="text-left"><?php echo I18n::get('shipping_method') ?></h4>
						<hr>
						<label class="col-lg-4 col-md-4 control-label" for="country"><?php echo I18n::get('country') ?></label>
						<div class="col-lg-8 col-md-8">
							<select class="form-control country col-lg-4 col-md-4" name="country" id="country_<?php echo $seller_id ?>">
								<option value="0"><?php echo I18n::get('select_one') ?></option>
								<?php
								$shipping_country = $array_country[$seller_id];
								if ($shipping_country == 'worldwide')
								{
									foreach ($country_list as $country_id => $name)
									{
										$selected = $country_id == $country ? ' selected' : '';
										echo "<option value=\"$country_id\"$selected>$name</option>";
									}
								}
								else if ($shipping_country == '')
								{
								}
								else
								{
									$arr_country = explode(',', $shipping_country);
									$array_ship_to = array();
									foreach ($arr_country as $country_id)
									{
										$array_ship_to[$country_id] = $country_list[$country_id];
									}
									asort($array_ship_to);
									foreach ($array_ship_to as $country_id => $name)
									{
										$selected = $country_id == $country ? ' selected' : '';
										echo "<option value=\"$country_id\"$seletecd>$name</option>";
									}
								}
								?>
							</select>
						</div>
					</div>
					<?php
					foreach ($array_tax_region as $country_id => $result)
					{
						$hidden = $country_id == $country ? '' : ' hidden';
					?>
					<div class="form-group row div_region_<?php echo $seller_id ?><?php echo $hidden ?>" id="div_region_<?php echo $country_id ?>_<?php echo $seller_id ?>">
						<label class="col-lg-4 col-md-4 control-label" for="region_<?php echo $country_id ?>_<?php echo $seller_id ?>"><?php echo $result['name'] ?></label>
						<div class="col-lg-8 col-md-8">
							<select class="form-control" name="region_<?php echo $country_id ?>_<?php echo $seller_id ?>" id="region_<?php echo $country_id ?>_<?php echo $seller_id ?>">
								<option value="0"><?php echo I18n::get('select_one') ?></option>
								<?php
								foreach ($result['data'] as $id => $name)
								{
										echo "<option value=\"$id\">$name</option>";
								}
								?>
							</select>
						</div>
					</div>
					<?php
					}
					?>
					
					<span class="col-lg-offset-4 col-md-offset-4"><button id="estimate_<?php echo $seller_id ?>" type="button" class="btn btn-default estimate"><?php echo I18n::get('select_a_shipping_service') ?></button></span>
					<br><br>
					
					<div class="text-left form-group row shipping_method_<?php echo $seller_id ?>" id="shipping_method_<?php echo $seller_id ?>">
					</div>
				</div>
				<?php
					$hidden = ' hidden';
				}
				else
				{
					$hidden = '';
					echo "<div class=\"col-lg-6 col-md-6 text-right\"></div>";
					?>
					<button id="estimate_<?php echo $seller_id ?>" type="button" class="btn btn-default estimate hidden"><?php echo I18n::get('select_a_shipping_service') ?></button>
					<?php
				}
				?>

				<div id="div_checkout_<?php echo $seller_id ?>" class="col-lg-12 col-md-12 text-right<?php echo $hidden ?>">
					<p><br><br></p>
					<h4 class="text-left"><?php echo I18n::get('price') ?></h4>
					<table class="table" id="summary_<?php echo $seller_id ?>" style="font-size: 12px">
					
					<?php
					if ($has_shipping == 0)
					{
					?>
						<tr><td><?php echo I18n::get('subtotal') ?></td><td><span class="col-lg-12"><?php echo $new_grand_subtotal . ' ' . strtoupper($new_currency) ?></span><?php echo $show_display == 1 ? "<br><span class=\"col-lg-12 text-muted text-right\">" . HTML::chars($ori_grand_subtotal) . ' ' . strtoupper($ori_currency) . "</span>" : '' ?></td></tr>
						<tr><td><?php echo I18n::get('estimated_order_total') ?></td><td><span class="col-lg-12"><?php echo $new_grand_subtotal . ' ' . strtoupper($new_currency)  ?></span><?php echo $show_display == 1 ? "<br><span class=\"col-lg-12 text-muted\">" . HTML::chars($ori_grand_subtotal) . ' ' . strtoupper($ori_currency) . "</span>" : '' ?></td></tr>
					<?php
					}
					?>
					</table>
					<hr>
					
						
				
					<div class="row">
					<button id="checkout_<?php echo $seller_id ?>" type="button" class="btn btn-default checkout"><?php echo I18n::get('proceed_to_checkout') ?></button></div>
				</div>
			</div>
			
		
			
		</div>
		
		
		<div class="clearfix"></div>
		<hr>
	
		<?php
			}
			cookie::set("cart", json_encode($array_cart), 1209600);

		}
		else
		{
			echo "<div class=\"alert alert-info alert-block\">" . I18n::get('shopping_cart_empty') . "</div>";
		}
		?>
	</div>
	<input type="hidden" id="id" name="id">
	<input type="hidden" id="json" value="0">
</form>
<?php
//print"<pre>";
//print_r($array_js);
//print"</pre>";
?>
<script src="<?php echo $cfg["jquery.js"] ?>"></script>
<script>
pricestore = new Object();

<?php
if (count($array_js) > 0)
{
	
	
	foreach($array_js as $seller_id => $subarray)
	{
		echo "pricestore[$seller_id] = new Object();";
		foreach ($subarray as $ld_id => $subvalue)
		{
			echo "pricestore[$seller_id][$ld_id] = new Array($subvalue[0], $subvalue[1], $subvalue[2]);";
		}
	}
}
?> 

label_subtotal = '<?php echo I18n::get('subtotal') ?>';
estimated_shipping = '<?php echo I18n::get('estimated_shipping_and_handling') ?>';
estimated_tax = '<?php echo I18n::get('estimated_tax') ?>';
estimated_order_total = '<?php echo I18n::get('estimated_order_total') ?>';
shipping_service = '<?php echo I18n::get('shipping_service') ?>';
shipping_and_handling = '<?php echo I18n::get('shipping_and_handling') ?>';
estimated_delivery_time = '<?php echo I18n::get('estimated_delivery_time') ?>';
shipping_error = "<?php echo I18n::get('seller_doesnt_ship_to_this_country') ?>";
converted_from_the_listed_amount = '<?php echo I18n::get('converted_from_the_listed_amount') ?>';
keystore = new Object();
cv = '<?php echo $convert_currency ?>';
cv_rate = '<?php echo $cfg_currency['usd_' . $ori_currency] ?>';
$(document).ready(function() {
	$('.button').each(function(index, element)
	{
		$(element).click(function(){
			explode = action = element.id.split('_')
			action = explode[0];
			id = explode[1];
			if (action == 'delete')
			{
				window.location.href = "/cart/delete?id=" + id;
			}
			else if (action == 'update')
			{
				val = $('#quantity_' + id).val();
				window.location.href = '/cart/update?id=' + id + '&val=' + val;
			}
		});
	});
	
	
	$('.country').each(function(index, element)
	{
		$(element).change(function(){
			explode = action = element.id.split('_')
			seller_id = explode[1];
			country_id = element.value;
			$('.div_region_' + seller_id).addClass('hidden');
			if ($('#div_region_' + country_id + '_' + seller_id))
			{
				$('#div_region_' + country_id + '_' + seller_id).removeClass('hidden');
			}
			$('#shipping_method_' + seller_id).html('');
			$('#div_checkout_' + seller_id).addClass('hidden');
			
		});
	});
	
	// 'on' event handler has to be used on element that already exists (eg: document);
	$(document).on('click', '.service', function()
	{
		explode = this.name.split('_')
		seller_id = explode[1];
		service = this.value;
		if (keystore[seller_id])
		{
			ori_shipping = keystore[seller_id][service]['orig'][0];
			new_shipping = keystore[seller_id][service]['converted'][0];
			ori_tax = keystore[seller_id][service]['orig'][1];
			new_tax = keystore[seller_id][service]['converted'][1];
			ori_grand_subtotal = keystore[seller_id][service]['orig'][2];
			new_grand_subtotal = keystore[seller_id][service]['converted'][2];
			ori_total = keystore[seller_id][service]['orig'][3];
			new_total = keystore[seller_id][service]['converted'][3];
			if (ori_currency == new_currency)
			{
				$('#grand_subtotal_' + seller_id).html(new_grand_subtotal + ' ' + new_currency.toUpperCase());
				
				summary = '<tr><td width="40%">' + label_subtotal + '</td><td>' + '<span class="col-lg-12">' + new_grand_subtotal + ' ' + new_currency.toUpperCase() + '</span></td></tr>';
				summary += '<tr><td>' + estimated_shipping + '</td><td>' + '<span class="col-lg-12">' + new_shipping  + ' ' + new_currency.toUpperCase() + '</span></td></tr>';
				summary += '<tr><td>' + estimated_tax + '</td><td>' + '<span class="col-lg-12">' + new_tax  + ' ' + new_currency.toUpperCase() + '</span></td></tr>';
				summary += '<tr><td>' + estimated_order_total + '</td><td>' + '<span class="col-lg-12">' + new_total  + ' ' + new_currency.toUpperCase() + '</td></tr>';
			}
			else
			{
				$('#grand_subtotal_' + seller_id).html(new_grand_subtotal + ' ' + new_currency.toUpperCase() + '<br><span class="text-muted">' + ori_grand_subtotal + ' ' + ori_currency.toUpperCase() + '</span>');
				
				summary = '<tr><td width="40%">' + label_subtotal + '</td><td>' + '<span class="col-lg-12">' + new_grand_subtotal  + ' ' + new_currency.toUpperCase() + '</span><br><span class="col-lg-12 text-muted">' + ori_grand_subtotal + ' ' + ori_currency.toUpperCase() + '</span></td></tr>';
				summary += '<tr><td>' + estimated_shipping + '</td><td>' + '<span class="col-lg-12">' + new_shipping  + ' ' + new_currency.toUpperCase() + '</span><br><span class="col-lg-12 text-muted">' + ori_shipping + ' ' + ori_currency.toUpperCase() + '</span></td></tr>';
				summary += '<tr><td>' + estimated_tax + '</td><td>' + '<span class="col-lg-12">' + new_tax  + ' ' + new_currency.toUpperCase() + '</span><br><span class="col-lg-12 text-muted">' + ori_tax + ' ' + ori_currency.toUpperCase() + '</span></td></tr>';
				summary += '<tr><td>' + estimated_order_total + '</td><td>' + '<span class="col-lg-12">' + new_total  + ' ' + new_currency.toUpperCase() + '</span><br><span class="col-lg-12 text-muted">' + ori_total + ' ' + ori_currency.toUpperCase() + '</span></td></tr>';
			}
			($('#summary_' + seller_id)).html(summary);
		}
    });
	
	$('.checkout').each(function(index, element)
	{
		$(element).click(function(){
			explode = action = element.id.split('_')
			seller_id = explode[1];
			pm = $('[name="pm_' + seller_id + '"]:checked').val();
			ss = $('input:radio[name=service_' + seller_id + ']:checked').val();

			//hs = 0
			if ($('#json').val() == 0)
			{
				window.location.href = "/cart/checkout?id=" + seller_id + "&pm=" + pm;
			}
			else
			{
				window.location.href = "/cart/checkout?id=" + seller_id + "&pm=" + pm + '&ss=' + ss;
			}
			
		});
	});
	
	$('.pm').each(function(index, element)
	{
		
		$(element).click(function(){
			explode = element.id.split('_')
			seller_id = explode[1];
			$('#estimate_' + seller_id).click();
		});
	});
	
	$('.estimate').each(function(index, element)
	{
		$(element).click(function(){
			explode = action = element.id.split('_')
			seller_id = explode[1];
			country_id = $('#country_' + seller_id).val();
			region_id = 0;
			if ($('#region_' + country_id + '_' + seller_id))
			{
				region_id = $('#region_' + country_id + '_' + seller_id).val();
			}
			pm = $('[name="pm_' + seller_id + '"]:checked').val();
			$.getJSON('/json/get_estimate', { country_id: country_id, region_id: region_id, seller_id: seller_id, pm: pm}, function(data){
				//$('#json').val();
				if (data != null)
				{
					if (data.invalid == 1)
					{
						shipping_method_content = shipping_error;
						$('#shipping_method_' + seller_id).html('<div class="alert alert-danger alert-block">' + shipping_method_content + '</div>');
					}
					else
					{
						shipping_method_content = '';
						counter = 1;
						keystore[seller_id] = new Object();
						cc = data.cc;
						//new_currency = data.currency;
						new_currency_rate = data.currency_rate;
							
						ori_grand_subtotal = 0;
						new_grand_subtotal = 0;
					
						
						new_currency = data.converted;
						ori_currency = data.orig;
						for (var i in pricestore[seller_id])
						{
							op = pricestore[seller_id][i][0];
							opu = pricestore[seller_id][i][1];
							oq = pricestore[seller_id][i][2];
							if (cc == 1)
							{
								np = opu * new_currency_rate;
								nst = np * oq;
								if (cv == 1)
								{
									op = opu * cv_rate;
									ost = op * oq;
									sd = 1;
								}
								else
								{
									op = op;
									ost = op * oq;
									if (ori_currency == new_currency)
									{
										np = op;
										nst = np * oq;
										sd = 0;
									}
									else
									{
										
										sd = 1;
									}
							
								}
								np = np.toFixed(5);
								nst = nst.toFixed(5);
								new_grand_subtotal += parseFloat(nst);
							}
							else
							{
								np = op;
								nst = op * oq;
								if (cv == 1)
								{
									op = opu * cv_rate;
									ost = op * oq;
									sd = 1;
								}
								else
								{
									op = op;
									ost = op *oq;
									sd = 0;
								}
								np = np.toFixed(2);
								nst = nst.toFixed(2);
								new_grand_subtotal += parseFloat(nst);
							}
							op = op.toFixed(2);
							ost = ost.toFixed(2);
							ori_grand_subtotal += parseFloat(ost);
							//show display
							if (sd == 1)
							{
								$('#unit_price_' + i).html(np + ' ' + new_currency.toUpperCase() + '<br><span class="text-muted">' + op + ' ' + ori_currency.toUpperCase() + '</span>');
								$('#price_' + i).html(nst + ' ' + new_currency.toUpperCase() + '<br><span class="text-muted">' + ost + ' ' + ori_currency.toUpperCase() + '</span>');
							}
							else
							{
								$('#unit_price_' + i).html(np + ' ' + new_currency.toUpperCase());
								$('#price_' + i).html(nst + ' ' + new_currency.toUpperCase());
							}
						}

						if (cc == 1)
						{
							ori_grand_subtotal = ori_grand_subtotal.toFixed(2);
							new_grand_subtotal = new_grand_subtotal.toFixed(5);
						}
						else
						{
							ori_grand_subtotal = ori_grand_subtotal.toFixed(2);
							new_grand_subtotal = new_grand_subtotal.toFixed(2);
						}
						//has shipping
						if ($('#json').val() == 1)
						{
							$.each(data, function(id, result)
							{
								if (id != 'currency_rate' && id != 'cc' && id != 'invalid' && id != 'orig' && id != 'converted')
								{
									ori_shipping = result.shipping_orig;
									new_shipping = result.shipping_converted;
									ori_tax = result.tax_orig;
									new_tax = result.tax_converted;
									ori_total = parseFloat(ori_shipping) + parseFloat(ori_tax) + parseFloat(ori_grand_subtotal);
									new_total = parseFloat(new_shipping) + parseFloat(new_tax) + parseFloat(new_grand_subtotal);
									ori_total = ori_total.toFixed(2);
									if (cc == 1)
									{
										new_total = new_total.toFixed(5);
									}
									else
									{
										new_total = new_total.toFixed(2);
									}
									
									keystore[seller_id][id] = new Object();
									keystore[seller_id][id]['orig'] = new Array(ori_shipping, ori_tax, ori_grand_subtotal, ori_total);
									keystore[seller_id][id]['converted'] = new Array(new_shipping, new_tax, new_grand_subtotal, new_total);
									if (counter == 1)
									{
										if (ori_currency == new_currency)
										{
											summary = '<tr><td width="40%">' + label_subtotal + '</td><td>' + '<span class="col-lg-12">' + new_grand_subtotal + ' ' + new_currency.toUpperCase() + '</span></td></tr>';
											summary += '<tr><td>' + estimated_shipping + '</td><td>' + '<span class="col-lg-12">' + new_shipping + ' ' + new_currency.toUpperCase() + '</span></td></tr>';
											summary += '<tr><td>' + estimated_tax + '</td><td>' + '<span class="col-lg-12">' + new_tax + ' ' + new_currency.toUpperCase() + '</span></td></tr>';
											summary += '<tr><td>' + estimated_order_total + '</td><td>' + '<span class="col-lg-12">' + new_total + ' ' + new_currency.toUpperCase() + '</span></td></tr>';
										}
										else
										{
											summary = '<tr><td width="40%">' + label_subtotal + '</td><td align="right">' + '<span class="col-lg-12">' + new_grand_subtotal + ' ' + new_currency.toUpperCase() + '</span><br><span class="col-lg-12 text-muted">' + ori_grand_subtotal + ' ' + ori_currency.toUpperCase() + '</span></td></tr>';
											summary += '<tr><td>' + estimated_shipping + '</td><td>' + '<span class="col-lg-12">' + new_shipping + ' ' + new_currency.toUpperCase() + '</span><br><span class="col-lg-12 text-muted">' + ori_shipping + ' ' + ori_currency.toUpperCase() + '</span></td></tr>';
											summary += '<tr><td>' + estimated_tax + '</td><td>' + '<span class="col-lg-12">' + new_tax + ' ' + new_currency.toUpperCase() + '</span><br><span class="col-lg-12 text-muted">' + ori_tax + ' ' + ori_currency.toUpperCase() + '</span></td></tr>';
											summary += '<tr><td>' + estimated_order_total + '</td><td>' + '<span class="col-lg-12">' + new_total + ' ' + new_currency.toUpperCase() + '</span><br><span class="col-lg-12 text-muted">' + ori_total + ' ' + ori_currency.toUpperCase() + '</span></td></tr>';
										}
										$('#summary_' + seller_id).html(summary);
										checked = ' checked';
									}
									else
									{
										checked = '';
									}
									if (ori_currency == new_currency)
									{
										shipping_method_content += '<hr class="hl"><div class="form-group"><span class="radio"><input class="service" type="radio"' + ' name="service_' + seller_id + '" value="' + id + '"' + checked + '>' + result.name + ' (' + + result.from + ' - ' + result.to + ' ' + result.dayweek + ')<br>' + new_shipping + ' ' + new_currency.toUpperCase() + '</span></div>';
									}
									else
									{
										shipping_method_content += '<hr class="hl"><div class="form-group"><span class="radio"><input class="service" type="radio" name="service_' + seller_id + '" value="' + id + '"' + checked + '>' + result.name + ' (' + result.from + ' - ' + result.to + ' ' + result.dayweek + ')<br>' + new_shipping + ' ' + new_currency.toUpperCase() + '<br><span class="text-muted text-right">' + ori_shipping + ' ' + ori_currency.toUpperCase() + '</span></span></div>';
									}
									counter++;
								}
							});
							$('#shipping_method_' + seller_id).html(shipping_method_content);
						}
						else
						{
							ori_total = ori_grand_subtotal;
							new_total = new_grand_subtotal;
							if (ori_currency == new_currency)
							{
								summary = '<tr><td width="40%">' + label_subtotal + '</td><td>' + '<span class="col-lg-12">' + new_grand_subtotal + ' ' + new_currency.toUpperCase() + '</span></td></tr>';
								summary += '<tr><td>' + estimated_order_total + '</td><td>' + '<span class="col-lg-12">' + new_total + ' ' + new_currency.toUpperCase() + '</span></td></tr>';
							}
							else
							{
								summary = '<tr><td width="40%">' + label_subtotal + '</td><td>' + '<span class="col-lg-12">' + new_grand_subtotal + ' ' + new_currency.toUpperCase() + '</span><br><span class="col-lg-12 text-muted">' + ori_grand_subtotal + ' ' + ori_currency.toUpperCase() + '</span></td></tr>';
								summary += '<tr><td>' + estimated_order_total + '</td><td>' + '<span class="col-lg-12">' + new_total + ' ' + new_currency.toUpperCase() + '</span><br><span class="col-lg-12 text-muted">' + ori_total + ' ' + ori_currency.toUpperCase() + '</span></td></tr>';
							}
						}
						if (ori_currency == new_currency)
						{
							$('#grand_subtotal_' + seller_id).html(new_grand_subtotal + ' ' + new_currency.toUpperCase());
						}
						else
						{
							$('#grand_subtotal_' + seller_id).html(new_grand_subtotal + ' ' + new_currency.toUpperCase() + '<br><span class="text-muted">' + ori_grand_subtotal + ' ' + ori_currency.toUpperCase() + '</span>');
						}
						$('#summary_' + seller_id).html(summary);
						$('#div_checkout_' + seller_id).removeClass('hidden');
					}
				}
				else
				{
					$('#shipping_method_' + seller_id).html('');
					$('#summary_' + seller_id).html('');
					$('#div_checkout_' + seller_id).addClass('hidden');
				}
			});
		});
	});
});
</script>