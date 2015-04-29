<style>
.thumbnail-box1 { padding-top: 8px; width: 610px; height: 500px; }
#center {
    position: relative;
    display: block;
    top: 50%;
    margin-top: -1000px;
    height: 2000px;
    text-align: center;
    line-height: 2000px;
}    
#wrap {
    line-height: 0;
}
#wrap img {
    vertical-align: middle;
}

.c6
{
	color: #999;
}

.c9
{
	color: #666;
}

.ln1
{
	padding-bottom: 10px;
	border-bottom: 1px solid #eee;
}
</style>
<?php
$cryptocurrency = 0;
$cookie_preference = Cookie::get("preference", 0);
if ($cookie_preference !== 0)
{
	$cookie_preference = json_decode($cookie_preference);
	$cookie_currency_code = $cookie_preference->currency_code;
	$convert_currency = $cookie_preference->convert_currency;
	$cryptocurrency = $cookie_preference->cryptocurrency;
	$cookie_country = $cookie_preference->country_id;
}
else
{
	$cookie_currency_code = 'usd';
	$convert_currency = 0;
	$cookie_country = 0;
}

?>

<div class="col-lg-8">
	<h3><?php echo HTML::chars($title) ?></h3>
	<hr>
	<?php
	if ($img_count > 0)
	{
		$img_path = implode("/", str_split($uid, 2));
		$img_path = substr($img_path, 0, 8);
	?>
	<div class="row">
		<img id="img" class="img-responsive" src="//<?php echo $cfg["www_domain"] . "/{$cfg["bucket_name"]}/{$cfg['size_large']}/$img_path/{$uid}_1.jpg" ?>">

			<?php
			if ($listing_obj[0]["img_count"] > 1)
			{
				echo "<div class=\"row\"><div class=\"col-lg-5 col-md-5\">";
				for ($i = 1; $i < ($listing_obj[0]["img_count"] + 1); $i++)
				{
					echo "<span class=\"col-lg-3 col-md-3\"><a href=\"#\" onclick=\"showImg('$i'); event.returnValue = false; return false; \"><img class=\"thumbnail\" src=\"//{$cfg["www_domain"]}/{$cfg["bucket_name"]}/{$cfg['size_small']}/$img_path/{$uid}_$i.jpg\"></a></span>";
				}
				echo "</div></div>";
			}
			?>
	</div>
	<hr>	
	<?php
	}
	?>
	
	
	<div class="row">
		<ul class="breadcrumb">
			<?php
			echo "<li><b>" . I18n::get('category') . "</b>: </li>";
			//hardcoded 7511
			$is_digital_goods = 0;
			foreach ($nav as $record)
			{
				if ($record['id'] == 1)
				{
					echo "<li><a href=\"/\">" . I18n::get('everything') . "</a></li>";
				}
				else
				{
					echo "<li><a href=\"/?cid={$record['id']}\">{$record['name']}</a></li>";
					if ($record['id'] == 7511)
					{
						$is_digital_goods = 1;
					}
				}
			}
			?>
		</ul>
	</div>

	
	<div class="row col-lg-12">
		<h4><?php echo I18n::get('description') ?></h4>
		<p><?php echo nl2br(html::chars($description)) ?></p>
		<br>
		<br>
	</div>
	

	
	<div class="row col-lg-8">
		<h4><?php echo I18n::get('listing_details') ?></h4>
		<table class="table table-condensed table-hover">
			<tbody>
				

	<?php
	if ($object_type_id == 1)
	{
		$rating = $rating == '' ? '' : "<b>($rating%+)</b>";
		$created = date('M d, Y', $created);
		
		//echo "<tr><td width=\"50%\">" . I18n::get('date_posted') . "</td><td>$created</td></tr>";
		echo "<tr><td>" . I18n::get('listing_by') . "</td><td><a href=\"/hub/{$listing_obj[0]['username']}\">" . $listing_obj[0]['username'] . " $rating</a></td></tr>";
		

		if (($selling_option & 1) > 0)
		{
			$qty = $quantity == 0 ? I18n::get('out_of_stock') : $quantity;
			if ($has_quantity == 1)
			{
				echo "<tr><td>" . I18n::get('quantity_available') . "</td><td>$qty</td></tr>";
			}
		}
		if ($has_item_condition == 1)
		{
			echo "<tr><td>" . I18n::get('item_condition') . "</td><td>$item_condition</td></tr>";
		}
		//non-usa
		if ($zip == "")
		{
			if ($geo1_name != "")
			{
				if ($geo2_name == "")
				{
					$listing_location = "$geo1_name, $country";
				}
				else
				{
					$listing_location = "$geo2_name, $geo1_name, $country";
				}
			}
			else
			{
				$listing_location = "$country";
			}
		}
		else
		{
			$listing_location = "$city, $state $zip, $country";
		}
	
		if ($listing_obj[0]['username'] != 'sotisoti')
		{
			echo "<tr><td>" . I18n::get('listing_location') . "</td><td>" . HTML::chars($listing_location) . "</td></tr>";
		}
		if ($neighborhood_name != '')
		{
			echo "<tr><td>" . I18n::get('neighborhood') . "</td><td>" . HTML::chars($neighborhood_name) . "</td></tr>";	
		}
		
		if ($cryptocurrency !== 0 AND ($selling_option & 1) > 0)
		{
			echo "<tr><td>" . I18n::get('escrow') . "</td><td>" . I18n::get('yes') . "</td></tr>";	
		}
	}
	?>
			</tbody>
		</table>
	</div>
	<div class="clearfix"></div>
	<br>
	
	

	
	<?php
	if (count($data_result) > 0 OR $feature_obj != "")
	{
	?>
	<div class="row">
		<h4><?php echo I18n::get('more_information') ?></h4>
	</div>
	<?php
	}
	if ($object_type_id == 1)
	{
		if (count($data_result) > 0)
		{
			$previous_ca_id = 0;
			$balanced = 1;
			foreach ($data_result as $data)
			{
				if ($balanced == 0 AND $previous_ca_id != $data["ca_id"])
				{
					//echo "</span></div>";
					echo "</span>";
					$balanced = 1;
				}
				
				if ($previous_ca_id != $data["ca_id"])
				{
					$balanced = 0;
					//for class attribute = address to show map
						
					
					if ($data['ca_id'] == 240)
					{
						$qs = http_build_query(array('q' => "{$data["ca_id"]} {$listing_obj[0]["city"]}, {$listing_obj[0]["state"]} {$listing_obj[0]["zip"]}"));
						echo "<span class=\"col-lg-2 col-md-2 c6\">{$data["name"]}</span><span class=\"col-lg-4 col-md-4 c9 spacer10\">" . HTML::chars($data["ca_value"]) . " - <a rel=\"nofollow\" href=\"http://maps.google.com/?$qs\">" . I18n::get('google_map') . "</a>";
					}
					else
					{
						echo "<span class=\"col-lg-2 col-md-2 c6\">{$data["name"]}</span><span class=\"col-lg-4 col-md-4 c9 spacer10\">" . HTML::chars($data["ca_value"]);
					}
					
				}
				else
				{
					echo ", " . HTML::chars($data["ca_value"]);
				}
				$previous_ca_id = $data["ca_id"];
			}
			echo "</span>";
		}
	}
	else
	{
		if ($feature_obj != '')
		{
			
			foreach ($feature_obj as $key => $value)
			{
				if ($key == '_empty_')
					continue;
				echo "<div class=\"spacer10 row ln\"><span class=\"col-lg-12 col-md-12\"><h5><b>" . HTML::chars($key) . "</b></h5></span>";
				if (is_object($value))
				{
					$counter = 1;
					foreach ($value as $subkey => $subvalue)
					{
						echo "<div class=\"col-lg-2 col-md-2 c6\">" . HTML::chars($subkey) . "</div><div class=\"col-lg-4 col-md-4 c9 spacer10\">" . HTML::chars($subvalue) . "</div>";
						if ($counter == 2)
						{
							echo "<div class=\"clearfix\"></div>";
							$counter = 0;
						}
						$counter++;
					}
					
				}
				
				echo "</div>";
				
			}
		}
	}
	if ($object_type_id == 2 AND count($listing_data_obj) > 0)
	{
		
	?>
		<div class="spacer row">
			<span class="col-lg-12 col-md-12"><h3><?php echo I18n::get('available_sellers') ?></h3></span>
		</div>
		<?php
		foreach($listing_data_obj as $id => $listing)
		{
			$zip = HTML::chars($listing['zip']);
			$country_id = $listing['country_id'];
			$country = $listing['country'];
			$geo1_name = $listing['geo1_name'];
			$geo2_name = $listing['geo2_name'];
			$location = HTML::chars($listing['location']);
			$city = HTML::chars($listing['city']);
			$state = HTML::chars($listing['state']);
			
			
			$user_id = $listing['user_id'];
			$username = $listing['username'];
			$item_condition = $listing['item_condition'];
			$condition_description = HTML::chars($listing['condition_description']);
			$show_phone_number = $listing['show_phone_number'];
			$phone = HTML::chars($listing['phone']);
			$buy_url = $listing['buy_url'];
			$offer_type_id = $listing['offer_type_id'];
			
			/*
			if ($listing['currency_code'] == $cookie_currency_code OR $convert_currency == 0)
			{
				$price = $listing['price'] . ' ' . strtoupper($listing['currency_code']);
				
				$fiat_code = $currency_code;
			}
			else
			{
				$price = sprintf("%0.2f", $cfg_currency['usd_' . $cookie_currency_code] * $listing['price_usd']) . ' ' . strtoupper($cookie_currency_code);
			}
			*/

			if ($zip == "")
			{
				if ($country_id == 131 OR $country_id ==171 OR $country_id == 192)
				{
					if ($geo2_name == "")
					{
						$listing_location = "$geo1_name, $country";
					}
					else
					{
						$listing_location = "$geo2_name, $geo1_name, $country";
					}
					
				}
				else
				{
					$listing_location = $location == '' ? $country : "$location, $country";
				}

			}
			else
			{
				$listing_location = "$city, $state $zip, $country";
			}
		?>
		<div class="spacer10 row ln">
			<span class="col-lg-3 col-md-3"><?php if ($item_condition != '') echo '<span class="c6">' . I18n::get('item_condition') . '</span>: ' . $item_condition . '<br>' . $condition_description . '</span>' ?></span>
			<span class="col-lg-5 col-md-5"><span class="c6"><?php echo I18n::get('seller') ?></span>: <a href="#"><?php echo $username ?></a><br><span class="c6"><?php echo I18n::get('listing_location') ?></span>: <?php echo $listing_location ?></span>
			<span class="col-lg-2 col-md-2">
			<br>
			
			<?php
			if ($offer_type_id == 1)
			{
				echo "<a target=\"_blank\" href=\"/link?id=$listing_id&uid=$user_id\"><button type=\"button\" class=\"btn btn-default btn-small\">" . I18n::get('visit_store') . "</button></a>";
						
			}
			else
			{
				/*
				echo "<a href=\"/listingcontact?id=$listing_id&uid=$user_id\"><button type=\"button\" class=\"btn btn-default btn-small ln\">" . I18n::get('email_seller') . "</button></a>";
				
				if ($show_phone_number == 1 AND $phone != '')
				{
					if (TEMPLATE == 'mobile')
					{	
						echo "<a href=\"tel:$phone\"><button type=\"button\" class=\"btn btn-default btn-small\">" . I18n::get('call') . ": $phone</button></a>";
					}
					else
					{
						echo "<button type=\"button\" class=\"btn btn-default btn-small\">" . I18n::get('call') . ": $phone</button>";
					}
				}
				*/
			}
			?>
			</span>

			<span class="col-lg-2 col-md-2"><h4><?php echo $price ?></h4></span>
		</div>
		<?php
		}
	}
	
	if ($shippable == 1 OR ($has_price == 1 AND $price != 0))
	{
	?>
	
	<div class="row col-lg-8">
		<h4><?php echo I18n::get('shipping_and_payment') ?></h4>
		<table class="table table-condensed table-hover">
			<tbody>
	<?php
	}
	if ($has_price == 1 AND $price != 0)
	{
	?>
				<tr>
					<td width="50%"><?php echo I18n::get('payment_method') ?></td>
					<td>
					<?php
			$array_payment_method_name = array();
			$array_payment_method_name['cash_on_delivery'] = I18n::get('cash_on_delivery');
			$array_payment_method_name['bank_deposit'] = I18n::get('bank_deposit');
			$array_payment_method_name['money_order'] = I18n::get('money_order');
			$array_payment_method_name['cashier_check'] = I18n::get('cashier_check');
			$array_payment_method_name['personal_check'] = I18n::get('personal_check');
			foreach ($cfg_crypto as $symbol => $record)
			{
				$array_payment_method_name[$symbol] = ucfirst($record['name']);
			}
			$payment_method = '';
			
			foreach ($payment_option as $key => $value)
			{
				if ($value['active'] == 1)
				{
					$payment_method .= $array_payment_method_name[$key] . '<br>';
				}
			}
			if ($payment_method == '')
			{
				echo "-";
			}
			else
			{
				echo "$payment_method";
			}
			?>
			
					</td>
				</tr>
	<?php
	
	}
	if ($shippable == 1)
	{
		include  __DIR__ . "/../../classes/controller/country_list.php"; 

		$shipping_country = trim($listing_obj[0]['shipping_country']);
		if ($shipping_country == 'worldwide')
		{
			$shipping = 1;
			$shipping_country = I18n::get('worldwide');
		}
		else if ($shipping_country == '')
		{
			$shipping = 2;
			$shipping_country = '';
		}
		else
		{
			$shipping = 3;
			$array_country = explode(',', $shipping_country);
			$array_ship_to = array();
			foreach ($array_country as $country_id)
			{
				$array_ship_to[$country_id] = $country_list[$country_id];
			}
			asort($array_ship_to);
			$shipping_country = implode(', ', $array_ship_to);
		}
		?>
		
		
				<tr>
					<td width="50%"><?php echo I18n::get('ship_to') ?></td>
					<td><?php echo $shipping_country ?></td>
				</tr>
				<tr>
					<td><?php echo I18n::get('shipping_rate') ?></td>
					<td>
						<select class="form-control country " name="country" id="country">
							<option value="0"><?php echo I18n::get('select_one') ?></option>
							<?php
							if ($shipping == 1)
							{
								foreach ($country_list as $country_id => $name)
								{
									$selected = $country_id == $cookie_country ? ' selected' : '';
									echo "<option value=\"$country_id\"$selected>$name</option>";
								}
							}
							else if ($shipping == 3)
							{
								foreach ($array_ship_to as $country_id => $name)
								{
									$selected = $country_id == $cookie_country ? ' selected' : '';
									echo "<option value=\"$country_id\"$selected>$name</option>";
								}
							}
							?>
						</select>
						<button type="button" id="get_rate" class="btn btn-default btn-small"><?php echo I18n::get('get_rate') ?></button>
					</td>
				</tr>

		
				
	
		
	
	<?php
	}
	else if ($is_digital_goods == 1)
	{
		if ($idd == 1)
		{
			$digital_delivery = "<h5><span class=\"label label-primary\">" . I18n::get('instant_delivery') . "</span></h5>";
		}
		else
		{
			$digital_delivery = I18n::get('digital_delivery');
		
		}
	?>
				<tr>
					<td width="50%"><?php echo I18n::get('shipping_method') ?></td>
					<td><?php echo $digital_delivery ?></td>
				</tr>

	<?php
	}

	?>
			</tbody>
		</table>
		<div id="shipping_method" class="hidden1"></div>
	</div>
	

</div>


<div class="col-lg-4">
	<h3>&nbsp;</h3>
	<hr>
	
<?php
if ($object_type_id == 1)
{
	if ($has_price == 1 AND $price != 0)
	{
		if (($selling_option & 1) > 0)
		{
			$accept_crypto = 0;
			if ($cfg_crypto[$currency_code]['active'] == 1)
			{
				$listing_currency_is_crypto = 1;
			}
			else
			{
				$listing_currency_is_crypto = 0;
			}
			$array_crypto = array();
			foreach ($payment_option as $key => $value)
			{
				if ($cfg_crypto[$key]['active'] == 1 AND $payment_option[$key]['active'] == 1)
				{
					$accept_crypto = 1;
					$array_crypto[$key] = 1;
				}
			}
			
			//calculate currency and price in fiat
			if ($listing_currency_is_crypto == 1)
			{
				if ($convert_currency == 1)
				{
					// btc -> usd -> myr
					$fiat = '~' . sprintf("%0.2f", $price * $cfg_currency[$currency_code . '_usd'] * $cfg_currency['usd_' . $cookie_currency_code]) . ' ' . strtoupper($cookie_currency_code);
				}
				else
				{
					// btc -> usd
					$fiat = '~' . sprintf("%0.2f", $price * $cfg_currency[$currency_code . '_usd']) . ' ' . 'USD';
				}
			}
			else
			{
				if ($convert_currency == 1)
				{
					
					//cad to myr
					$fiat = '~' . sprintf("%0.2f", $price * $cfg_currency[$currency_code . '_usd'] * $cfg_currency['usd_' . $cookie_currency_code]) . ' ' . strtoupper($cookie_currency_code);
				}
				else
				{
					//cad to cad
					//$fiat_price = $price . ' ' . strtoupper($currency_code);
					$fiat = '';
				}
			}

			echo "<h4>" . I18n::get('price') . "</h4>";
			echo "<table class=\"table table-condensed table-hover\" style=\"font-weight:bold\">";
			if ($accept_crypto == 1)
			{
				if ($listing_currency_is_crypto	== 1)
				{
					$price = sprintf("%0.5f", $price);
					$price_usd = sprintf("%0.2f", $price * $cfg_currency[$currency_code . '_usd']);
					foreach ($array_crypto as $key => $value)
					{
						if ($key == $currency_code)
						{
							$price_new = sprintf("%0.5f", $price);
						}
						else
						{
							$price_new = sprintf("%0.5f", $cfg_currency['usd_' . $key] * $price_usd);

						}
						echo "<tr><td>{$array_payment_method_name[$key]}</td><td align=\"right\">$price_new " . strtoupper($key). "</td></tr>";
					}
					echo "<tr><td class=\"light\">" . I18n::get('listed_amount') . "</td><td class=\"light\" align=\"right\">$price " . strtoupper($currency_code). "<br>$fiat</td></tr>";

				}
				else
				{
					foreach ($array_crypto as $key => $value)
					{
						$price_new = sprintf("%0.5f", $cfg_currency['usd_' . $key] * $price_usd);
						echo "<tr><td>{$array_payment_method_name[$key]}</td><td align=\"right\">$price_new " . strtoupper($key). "</td></tr>";
					}
					echo "<tr><td class=\"light\">" . I18n::get('listed_amount') . "</td><td class=\"light\" align=\"right\">$price " . strtoupper($currency_code). "<br>$fiat</td></tr>";
				}
			}
			else
			{
				if ($listing_currency_is_crypto	== 1)
				{
					$price_usd = sprintf("%0.2f", $price * $cfg_currency[$currency_code . '_usd']);
					$price_in_text = sprintf(I18n::get('price_in_currency'), strtoupper('usd'));
					echo "<tr><td>$price_in_text</td><td align=\"right\">$price_usd USD</td></tr>";
					echo "<tr><td class=\"light\">" . I18n::get('listed_amount') . "</td><td class=\"light\" align=\"right\">$price " . strtoupper($currency_code). "<br>$fiat</td></tr>";
				}
				else
				{
					echo "<tr><td>" . I18n::get('listed_amount') . "</td><td align=\"right\">$price " . strtoupper($currency_code). "<br>$fiat</td></tr>";
				}
			}
			echo "</table>";
			
			
			echo "<hr><form action=\"/cart/add\" method=\"post\">";
			if ($quantity > 0)
			{
				echo "<input class=\"btn btn-default\" type=\"submit\" value=\"" . I18n::get('add_to_cart') . "\">";
		
			}
			echo "<input type=\"hidden\" name=\"id\" value=\"{$listing_obj[0]['id']}\">";
			echo "<input type=\"hidden\" name=\"uid\" value=\"$user_id\">";
			echo "</form><br>";
		}
	
		if (($selling_option & 2) > 0)
		{
			echo "<a target=\"_blank\" href=\"/link?id=$listing_id&uid=$user_id\"><button type=\"button\" class=\"btn btn-default btn-small\">" . I18n::get('go_to_store') . "</button></a><br><br>";
		}

		if (($selling_option & 4) > 0)
		{
			echo "<button id=\"btn_retail_store_address\" type=\"button\" class=\"btn btn-default btn-small\">" . I18n::get('retail_store_address') . "</button><br><div id=\"retail_store_address\"></div>";
		}
		echo "<hr>";
	}
	?>
	<!--
	<div>
		<ul class="nav nav-list">
			
			<li class="nav-header"><?php echo I18n::get('contact') . ' ' . $listing_obj[0]['username'] ?></li>
			<li><a href="/listingcontact?id=<?php echo $listing_obj[0]['id'] ?>&uid=<?php echo $user_id ?>"><?php echo I18n::get('by_email') ?></a></li>
			<?php
			if ($preference_obj->show_phone_number == 1 AND $listing_obj[0]['phone'] != '')
			{
				if (TEMPLATE == 'mobile')
				{	
					echo "<li><a href=\"tel:{$listing_obj[0]['phone']}\">" . I18n::get('by_phone') . ": {$listing_obj[0]['phone']}</a></li>";
				}
				else
				{
					echo "<li><a href=\"#\">" . I18n::get('by_phone') . ": {$listing_obj[0]['phone']}</a></li>";
				}
			}
			?>
		</ul>
	</div>
	//-->
<?php
}
?>
</div>

<div class="clearfix"></div>
<script src="<?php echo $cfg["jquery.js"] ?>"></script>
<script type="text/javascript" src="//s7.addthis.com/js/300/addthis_widget.js#pubid=ra-4de608745048f1cc"></script>
<script>
store_info = '<br><?php echo I18n::get('retail_store_address_info') ?><br><br>';
shipping_service = '<?php echo I18n::get('shipping_service') ?>';
shipping_and_handling = '<?php echo I18n::get('shipping_and_handling') ?>';
estimated_delivery_time = '<?php echo I18n::get('estimated_delivery_time') ?>';
seller_id = <?php echo $user_id ?>;

function showImg(name)
{
	$('#img').attr('src',  "//<?php echo "{$cfg['www_domain']}/{$cfg["bucket_name"]}/600/$img_path/{$uid}_" ?>" + name + ".jpg");
}

$(document).ready(function() {
	$('#btn_retail_store_address').click(function(){
		$.getJSON('/json/get_store_address', { seller_id: seller_id }, function(data) {
			if (data != "")
			{
				$('#retail_store_address').html(store_info + data);
			}
		});
	});
	
	$('#get_rate').click(function(){
	
		country_id = $('#country').val();
		$.getJSON('/json/get_estimate', { country_id: country_id, uid: '<?php echo $uid ?>' }, function(data) {
			if (data != null)
			{
				if (data.invalid == 1)
				{
					shipping_method_content = shipping_error;
					$('#shipping_method_' + seller_id).html('<div class="alert alert-danger alert-block">' + shipping_method_content + '</div>');
				}
				else
				{
					shipping_method_content = '<table class="table" widht="100%"><tr><td><b>' + shipping_service + '</b></td><td><b>' + estimated_delivery_time + '</b></td><td><b>' + shipping_and_handling + '</b></td></tr>';
					counter = 1;
					
					cc = data.cc;
					new_currency = data.converted;
					ori_currency = data.orig;
					
					$.each(data, function(id, result)
					{
						if (id != 'currency_rate' && id != 'cc' && id != 'invalid' && id != 'orig' && id != 'converted')
						{
							ori_shipping = result.shipping_orig;
							new_shipping = result.shipping_converted;

							if (counter == 1)
							{
								checked = ' checked';
							}
							else
							{
								checked = '';
							}
							if (ori_currency == new_currency)
							{
								//shipping_method_content += '<hr class="hl"><div class="form-group"><span class="radio"><span class="col-lg-3 col-md-3">' + result.name + '</span><span class="col-lg-4 col-md-4 text-right">' + new_shipping + ' ' + new_currency.toUpperCase() + '</span><span class="col-lg-4 col-md-4">' + result.from + ' - ' + result.to + ' ' + result.dayweek + '</span></span></div>';
								shipping_method_content += '<tr><td>' + result.name + '</td><td>' + result.from + ' - ' + result.to + ' ' + result.dayweek + '</td><td>' + new_shipping + ' ' + new_currency.toUpperCase() + '</td></tr>';
							}
							else
							{
								//shipping_method_content += '<hr class="hl"><div class="form-group"><span class="radio"><span class="col-lg-3 col-md-3">' + result.name + '</span><span class="col-lg-4 col-md-4 text-right">' + new_shipping + ' ' + new_currency.toUpperCase() + '<br><span class="text-muted text-right">' + ori_shipping + ' ' + ori_currency.toUpperCase() + '</span></span><span class="col-lg-4 col-md-4">' + result.from + ' - ' + result.to + ' ' + result.dayweek + '</span></span></div>';
								shipping_method_content += '<tr><td>' + result.name + '</td><td>' + result.from + ' - ' + result.to + ' ' + result.dayweek + '</td><td>' + new_shipping + ' ' + new_currency.toUpperCase() + '<br><span class="text-muted">' + ori_shipping + ' ' + ori_currency.toUpperCase() + '</td></tr>';
							}
							counter++;
						}
					});
					shipping_method_content += '</table>';
					$('#shipping_method').removeClass('hidden');
					$('#shipping_method').html(shipping_method_content);
				}
			}
		});
	});
});
</script>
