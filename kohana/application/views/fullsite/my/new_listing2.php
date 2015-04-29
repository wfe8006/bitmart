<?php
if ($t == 2)
{
	$outer_display = "block";
	if ($country_id == "244")
	{
		$nonus_display = "hidden";
		$us_display = "block";
	}
	else
	{
		$nonus_display = "block";
		$us_display = "hidden";
	}
}
else
{
	$outer_display = "block";
	
	$nonus_display = "hidden";
	$us_display = "hidden";
}
?>
<br>
<form class="form-horizontal" id="fcontent" name="fcontent" action="post" method="post">
	<?php include "menu.php"; ?>
	<div class="col80 content-right">

		<h4><?php echo $header ?></h4><hr>
		<?php
		if (count($gtin_result) > 0)
		{
		?>
		<div class="spacer row">
			<div class="col-lg-12 col-md-12"><h4><?php echo HTML::chars($gtin_result[0]['title']) ?></h4></div>
		</div>
		
		<?php
		}
		?>
		<?php if (1+1 == 3) { ?>
		<span class="bl rw bold"><?php echo I18n::get('email_anonymization') ?></span>
		<?php } ?>
		
		<div class="bold"><?php echo empty($msg) ? "" : $msg ?></div>
		<div class="error"><?php echo empty($errors['upload']) ? "" : $errors['upload'] ?></div>

		<ul class="breadcrumb">
			<li><b><?php echo I18n::get('category') ?>:</b></li>
			<?php
			$counter = 0;
			foreach ($nav as $item)
			{
				echo "<li>" . $item['name'] . "</li>";
				$counter++;
			}
		echo "</ul>";
		if (count($gtin_result) < 1)
		{
			echo "<a href=\"/my/listing/$action?s=1&sid=$cid&listing_id=$id\">" . I18n::get("change_category") . "</a>";
		}
		?>
		<p><br></p>
		<?php
		/*
		if ($has_selling_option > 0)
		{
		?>
		
		<h5 class="subtitle"><b><?php echo I18n::get('selling_options') ?></b></h5>
		<span class="text-muted"><?php echo I18n::get('selling_options_descriptions') ?></span>
		<p><br></p>
		<div class="spacer row">
			<label class="col-lg-3 col-md-3 control-label"></label>
			<div class="col-lg-5 col-md-5">
					<?php
					if ((bindec($has_selling_option) & 1) > 0)
					{
					?>
					<div class="checkbox"><label><input type="checkbox" id="p3" name="p3" class="cb" <?php if ($selling_option1 == 1) echo ' checked' ?>> <?php echo I18n::get('selling_option1') ?></label></div>
					<?php
					}
					if ((bindec($has_selling_option) & 2) > 0)
					{
					?>
					<div class="checkbox"><label><input type="checkbox" id="p4" name="p4" class="cb" <?php if ($selling_option2 == 1) echo ' checked' ?>> <?php echo I18n::get('selling_option2') ?></label></div>
					<span class="<?php if ($selling_option2 == 0) echo 'hidden '?>text-muted " id="l_p4"><?php echo I18n::get('selling_option2_details') ?><input class="form-control<?php if ($selling_option2 == 1) echo ' required' ?>" id="third_party_url" name="third_party_url" placeholder="e.g. http://www.mystore.com/product_page" value="<?php echo HTML::chars($third_party_url) ?>" type="text"></span>
					<div class="error"><?php echo $errors['third_party_url'] ?></div>
					<?php
					}
					if ((bindec($has_selling_option) & 4) > 0)
					{
					?>
					<div class="checkbox"><label><input type="checkbox" id="p5" name="p5" class="cb" <?php if ($selling_option3 == 1) echo ' checked' ?>> <?php echo I18n::get('selling_option3') ?></label></div>
					<div class="error"><?php echo $errors['store_address'] ?></div>
					<?php
					}
					?>
					<div id="selling_options_error" class="error"><?php echo $errors['selling_options'] ?></div>
			</div>
		</div>
		<hr>
		<?php
		}
		*/
		?>
		<h5 class="subtitle"><b><?php echo I18n::get('listing_location') ?></b></h5>
		<p><br></p>
		<div class="spacer row">
			<label class="col-lg-3 col-md-3 control-label" for="listing_location"></label>
			<div class="col-lg-5 col-md-5">
				<input class="form-control" id="listing_location" name="listing_location" type="text" value="<?php echo HTML::chars($listing_location) ?>" maxlength="80" readonly>
				<div class="error"><?php echo $errors['listing_location'] ?></div>
			</div>
		</div>
		
		<div id="div_location_outer" style="display: <?php echo $outer_display ?>">
			<div id="div_country" class="spacer row">
				<label class="col-lg-3 col-md-3 control-label" for="country"><?php echo I18n::get('country') ?></label>
				<div class="col-lg-5 col-md-5">
					<select class="form-control" id="country" name="country">
						<option value=""><?php echo I18n::get('select_one') ?></option>
						<?php
						foreach ($country_obj as $co)
						{
							$selected = $country_id == $co['id'] ? " selected" : "";
							echo "<option value=\"" . $co['id'] . "\"$selected>" . $co['name'] . "</option>";
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
				
				<div class="spacer row">
					<label class="col-lg-3 col-md-3 control-label" for="neighborhood"><?php echo I18n::get('neighborhood') ?></label>
					<div class="col-lg-5 col-md-5">
						<select class="form-control" id="neighborhood" name="neighborhood">
							<option value="">-</option>
							<?php
							if (isset($neighborhoods))
							{
								foreach ($neighborhoods as $n)
								{
									$select = $n["id"] == $neighborhood_id ? " selected" : "";
									
									echo "<option value=\"{$n["id"]}\"$select>{$n["name"]}</option>";					
								}
							}
							?>
						</select>
					</div>
				</div>
			</div>
		</div>
		
		<hr>
		<h5 class="subtitle"><b><?php echo I18n::get('listing_details') ?></b></h5>
		<p><br></p>
		<?php
		if ($has_item_condition == 1)
		{
		?>
		<div class="spacer row">
			<label class="col-lg-3 col-md-3 control-label" for="item_condition"><?php echo I18n::get('item_condition') ?></label>
			<div class="col-lg-5 col-md-5">
				<select class="form-control" id="item_condition" name="item_condition">
					<option value="1"<?php echo $item_condition_id == 1 ? ' selected' : '' ?>><?php echo I18n::get('new') ?></option>
					<option value="2"<?php echo $item_condition_id == 2 ? ' selected' : '' ?>><?php echo I18n::get('used') ?></option>
				</select>
			</div>
		</div>
		<?php
			if ($object_type_id == 2)
			{
		?>
		<div class="spacer row">
			<label class="col-lg-3 col-md-3 control-label" for="condition_description"><?php echo I18n::get('condition_description') ?></label>
			<div class="col-lg-5 col-md-5">
				<input class="form-control" id="condition_description" name="condition_description" type="text" value="<?php echo HTML::chars($condition_description) ?>" maxlength="80" placeholder="<?php echo I18n::get('condition_description_example') ?>">
			</div>
		
		</div>
		<?php
			}
		
		}

		if ($has_price == 1)
		{
			?>
		<div class="spacer row">
			<label class="col-lg-3 col-md-3 control-label"><?php echo I18n::get('payment_method') ?></label>
			<div class="col-lg-5 col-md-5">
			<?php
			
			$pm_count = 0;
			if (count($array_payment_option) > 0)
			{
				echo '<ul>';
				foreach ($array_payment_option as $name => $value)
				{
					$active = $value['active'];
					if ($active == 1)
					{
						echo '<li>' . $array_payment_method_name[$name] . '</li>';
						$pm_count += 1;
					}
				}
				echo '</ul>';
			}		
			if ($pm_count == 0)
			{
				echo "<a id=\"btn_payment_method\" class=\"btn btn-default\" href=\"/my/payment\">" . I18n::get('add_payment_method') . "</a><br>";
				echo "<div class=\"error\">" . $errors['payment_method'] . "</div>";

				echo "<input type=\"hidden\" id=\"payment_method\" name=\"payment_method\" value=\"0\">";
			}
			else
			{
				echo "<input type=\"hidden\" id=\"payment_method\" name=\"payment_method\" value=\"1\">";
			}
			?>
			</div><br>
			<div id="div_payment_method" name="div_payment_method"></div>
					
		</div>	
			<?php
			if ($has_shipping == 1)
			{
		?>
		
		<div class="spacer row">
			<label class="col-lg-3 col-md-3 control-label"><?php echo I18n::get('shipping_service') ?></label>
			<div class="col-lg-5 col-md-5">
			<?php
			if (count($user_shipping_method_obj) > 0)
			{
				echo '<ul>';
				foreach ($user_shipping_method_obj as $record)
				{
					echo '<li>' . $record['name'] . '</li>';
				}
				echo '</ul>';
				echo "<input type=\"hidden\" id=\"shipping_service\" name=\"shipping_service\" value=\"1\">";
			
			}
			else
			{
				echo "<a id=\"btn_shipping_service\" class=\"btn btn-default\" href=\"/my/shipping/shipping_method\">" . I18n::get('add_shipping_service') . "</a><br>";
				echo "<div class=\"error\">" . $errors['shipping_service'] . "</div>";

				echo "<input type=\"hidden\" id=\"shipping_service\" name=\"shipping_service\" value=\"0\">";
			}
			?>

			</div><br>
			<div id="div_shipping_service" name="div_shipping_service"></div>
				
		</div>
		
		
		
		
		<div class="spacer row">
			<label class="col-lg-3 col-md-3 control-label" for="price"><?php echo I18n::get('item_weight') ?></label>

			<div class="col-lg-2 col-md-2">
			<input class="form-control" id="item_weight" name="item_weight" type="text" value="<?php echo HTML::chars($item_weight) ?>" maxlength="10" placeholder="0.00">
			</div>
			<div class="col-lg-2 col-md-3">
			<select class="form-control" id="weight_unit" name="weight_unit">
				<option value="1"<?php if ($weight_unit == 1) echo ' selected' ?>><?php echo I18n::get('g_gram') ?></option>
				<option value="2"<?php if ($weight_unit == 2) echo ' selected' ?>><?php echo I18n::get('kg_kilogram') ?></option>
				<option value="3"<?php if ($weight_unit == 3) echo ' selected' ?>><?php echo I18n::get('lbs_pound') ?></option>
				<option value="4"<?php if ($weight_unit == 4) echo ' selected' ?>><?php echo I18n::get('oz_ounce') ?></option>
			</select>
			</div>
			<div class="error"><?php echo $errors['item_weight'] ?></div>
			<div class="clearfix"></div>
			<div class="col-lg-3 col-md-3"></div>
			<div class="col-lg-8 col-md-8 text-muted"><?php echo I18n::get('item_weight_info') ?></div>
					
		</div>
		<?php 
			}
			
			
			if ($has_quantity == 1)
			{
			?>
			<div class="spacer row">
			<label class="col-lg-3 col-md-3 control-label" for="quantity"><?php echo I18n::get('quantity_available') ?></label>

			<div class="col-lg-2 col-md-2">
				<input class="form-control" id="quantity" name="quantity" type="text" value="<?php echo HTML::chars($quantity) ?>" maxlength="5" placeholder="1">
			</div>
			<div class="error"><?php echo $errors['quantity'] ?></div>
		</div>
			
			<?php 								

			}
		?>
		<div class="spacer row">
			<label class="col-lg-3 col-md-3 control-label" for="price"><?php echo I18n::get('price') ?></label>
			<div>
					<div class="input-group">
							<div class="input-group col-lg-6 pull-left">
								<input class="form-control" id="price" name="price" type="text" value="<?php echo HTML::chars($price) ?>" maxlength="10"><span class="input-group-addon"><?php echo strtoupper($currency_code) ?></span>
							</div>
							<div class="col-lg-6 pull-left">
								<a id="btn_payment_method" class="btn btn-default" href="/my/general"><?php echo I18n::get('change_currency') ?></a>
							</div>
							<div class="clearfix"></div>
							
				
						</div>
					
					</div>
					<div class="error col-lg-3 col-md-3"><?php echo $errors['price'] ?></div>
			</div>
		</div>
		<?php
		}
		if (count($gtin_result) < 1)
		{
		?>
			
			<div class="spacer row">
				<label class="col-lg-3 col-md-3 control-label" for="title"><?php echo I18n::get('title') ?></label>
				<div class="col-lg-5 col-md-5">
					<input class="form-control" id="title" name="title" type="text" value="<?php echo HTML::chars($title) ?>" maxlength="80">
					<div class="error"><?php echo $errors['title'] ?></div>
				</div>
			</div>
			<div class="spacer row">
				<label class="col-lg-3 col-md-3 control-label" for="description"><?php echo I18n::get('description') ?></label>
				<div class="col-lg-7 col-md-7">
					<textarea class="form-control" id="description" name="description" cols="10" rows="6"><?php echo HTML::chars($description) ?></textarea>
					<div class="error" id="edescription"><?php echo $errors['description'] ?></div>
				</div>
			</div>
			<?php
			if ($is_digital == 1)
			{
				if ($idd == 0)
				{
					$idd = 0;
					$idd_checked = '';
					$hidden = ' hidden';
				}
				else
				{
					$idd = 1;
					$idd_checked = ' checked';
					$hidden = '';
				}
			?>
			<div class="spacer row">
				<label class="col-lg-3 col-md-3 control-label"></label>
				<div class="col-lg-7 col-md-7">
					<div class="checkbox"><label><input type="checkbox" id="idd" name="idd" class="idd checkbox" value="1"<?php echo $idd_checked ?>><?php echo I18n::get('enable_instant_digital_delivery') ?></label>
				</div>
			</div>
			<div class="clearfix"></div>
			<div class="spacer row<?php echo $hidden ?>" id="dd">
				<div class="panel panel-default">
					<div class="panel-heading">
						<h3 class="panel-title"><?php echo I18n::get('digital_content_delivery') ?></h3>
					</div>
					<div class="panel-body">
						<div class="alert alert-info"><?php echo I18n::get('digital_content_delivery_msg') ?></div>	
						<div class="row spacer" id="div_content_header">
							<span class="col-lg-6 col-md-6"><b><?php echo I18n::get('content') ?></b></span>
							<span class="col-lg-3 col-md-3"><b><?php echo I18n::get('status') ?></b></span>
							<span class="col-lg-3 col-md-3"></span>
						</div>
						<?php
						if ($idd == 0)
						{
						?>
						<div class="div_content row spacer" id="div_1">
							<span class="col-lg-6 col-md-6"><input class="form-control" id="content_1" maxlength="256" name="content_new[]" type="text" value=""></span>
							<span class="col-lg-3 col-md-3"><?php echo I18n::get('new') ?></span>
							<span class="col-lg-3 col-md-3"><button type="button" id="delete_content_1" name="delete_content_1" class="delete_content btn btn-default"><?php echo I18n::get('delete') ?></span>
						</div>
						<?php
							$rn = 2;
						}
						else
						{
							//running number
							$rn = 1;
							foreach ($digital_content_obj as $record)
							{
								if ($record['used'] == 1)
								{
									$status = I18n::get('used') . ' - ' . I18n::get('order') . ' #' . $record['order_id'];
									$disabled = ' disabled';
								}
								else
								{
									$status = I18n::get('new');
									$disabled = '';
								}
							?>
						<div class="div_content row spacer" id="div_<?php echo $record['id'] ?>">
							<span class="col-lg-6 col-md-6"><input class="form-control" id="content_<?php echo $record['id'] ?>" maxlength="256" name="content_<?php echo $record['id'] ?>" type="text" value="<?php echo html::chars($record['content']) ?>"<?php echo $disabled ?>></span>
							<span class="col-lg-3 col-md-3"><?php echo $status ?></span>
							<span class="col-lg-3 col-md-3"><button type="button" id="delete_content_<?php echo $record['id'] ?>" name="delete_content_<?php echo $record['id']?>" class="delete_content btn btn-default"><?php echo I18n::get('delete') ?></button></span>
						</div>
							<?php
								$rn = $record['id'] + 1;
							}
						}
						?>
						<button type="button" id="new_content" class="new_content btn btn-default"><?php echo I18n::get('add_new_content') ?></button>

					</div>
				</div>

			</div>
			
			<p>&nbsp;</p>
			<p>&nbsp;</p>
			<p>&nbsp;</p>
			
			<?php
			}
			$data = Array();
			foreach ($ess as $es)
			{
				$data[$es['attribute_id']][] = Array($es['id'], $es['value']);
			}
			//Expiration Month
			$month = Array("Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec");
			if (isset($data[28]))
			{
				foreach ($data[28] AS $key => $value)
				{
					$data[28][$key][1] = $month[$key];
				}
			}
			$js = 0;
			$jsc = "";
			//$data_type: refer to the categories_attribute table
			$data_type = array();
			
			//$cas = array();
			if (count($cas) > 0)
			{
				echo "<hr>";
			}
			foreach ($cas as $ca)
			{
				$data_type['t'.$ca['id']] = $ca['data_type'];
				//the $_POST value will be display when this form is resubmitted (js disabled, hack, or validation not passed)
				if (isset($listing_data_arr))
				{
					//checkboxes
					if ($ca['elm_type'] == 2)
					{
						$post = Arr::get($_POST, 't'.$ca['id'], $listing_data_arr[$ca['id']]);
					}
					else
					{
						$post = Arr::get($_POST, 't'.$ca['id'], $listing_data_arr[$ca['id']][0]);
					}
				}
				else
				{
					$post = Arr::get($_POST, 't' . $ca['id']);
				}
				$selected = array();
				if ($ca['parent_categories_attribute_data_id'] == 0)
				{
					$hide = '';
				}
				else
				{
					//print"<br>attr_data_id==================================";
					//form submitted
					if (isset($post))
					{
						//after submit, first item (-) selected, hide it
						if ($post == '')
						{
							$hide = ' hidden';
						}
						else
						{
							$hide = '';
						}
					}
					//form hasn't been posted, always hide hidden select box
					else
					{
						$hide = ' hidden';
					}
				}
				if ($ca['event_type'] == 1 OR $ca['event_type'] == 2)
				{
					$js = 1;
					$jsc .= "var e{$ca['id']} = new Postad('t{$ca['id']}');";
				}
				if ($ca['parent_id'] != 0)
				{
					$ca['name'] = $ca['name'] == '-' ? '' : $ca['name'];
					$jsc .= "e{$ca['parent_id']}.add({$ca['parent_id']}, {$ca['parent_categories_attribute_data_id']}, {$ca['id']});";
				}
				//echo "<div id=\"d{$ca['id']}\" class=\"sC{$hide}\">";
				echo "<div id=\"d{$ca['id']}\" class=\"sC{$hide} spacer row\"><label class=\"col-lg-3 col-md-3 control-label\" for=\"t{$ca['id']}\">{$ca['name']}</label><div class=\"col-lg-5 col-md-5\">";
					
			
			
				if ($ca['elm_type'] == 1)
				{
					//echo "<label>{$ca['name']}</label><select id=\"t{$ca['id']}\" name=\"t{$ca['id']}\"><option value=\"\">-</option>";
					
					//echo "<div class=\"spacer row\"><label class=\"col-lg-3 col-md-3 control-label\" for=\"t{$ca['id']}\">{$ca['name']}</label><div class=\"col-lg-5 col-md-5\">";
					echo "<select class=\"form-control\" id=\"t{$ca['id']}\" name=\"t{$ca['id']}\"><option value=\"\">-</option>";
					foreach ($data[$ca['id']] AS $row)
					{
						$select = $row[0] == $post ? " selected" : "";
						echo @"<option value=\"{$row[0]}\"$select>{$row[1]}</option>";
					}
					echo "</select>";
				}
				elseif ($ca['elm_type'] == 2)
				{
					//echo "<div id=\"d{$ca['id']}\" class=\"sC{$hide} spacer row\"><label class=\"col-lg-3 col-md-3 control-label\">{$ca['name']}</label><div class=\"col-lg-5 col-md-5\">";
					foreach ($data[$ca['id']] AS $row)
					{
						$check = "";
						$check = "";
						if (is_array($post))
						{
							$check = in_array($row[0], $post) ? " checked" : "";
						}
						echo "<label class=\"checkbox-inline\"><input type=\"checkbox\" id=\"t{$ca['id']}_{$row[0]}\" name=\"t{$ca['id']}[]\" value=\"{$row[0]}\"$check> {$row[1]}</label>";
					}
					//echo "</div></div>";
				}
				elseif ($ca['elm_type'] == 3)
				{
					//echo "<div class=\"spacer row\"><label class=\"col-lg-3 col-md-3 control-label\" for=\"t{$ca['id']}\">{$ca['name']}</label><div class=\"col-lg-5 col-md-5\">";
					echo "<input class=\"form-control\" id=\"t{$ca['id']}\" name=\"t{$ca['id']}\" type=\"text\" value=\"" . HTML::chars($post) . "\" maxlength=\"50\">";
				}
				elseif ($ca['elm_type'] == 4)
				{
					//echo "<div class=\"spacer row\"><label class=\"col-lg-3 col-md-3 control-label\" for=\"t{$ca['id']}\">{$ca['name']}</label><div class=\"col-lg-5 col-md-5\">
					echo "<select class=\"form-control\" id=\"t{$ca['id']}\" name=\"t{$ca['id']}\"><option value=\"\">-</option>";
					for ($i = date('Y'); $i >= 1900; $i--)
					{
						$select = $i == $post ? " selected" : "";
						echo @"<option value=\"$i\"$select>$i</option>";
					}
					echo "</select>";
				}
				elseif ($ca['elm_type'] == 5)
				{
					//echo "<label>{$ca['name']}</label>";
					echo "<select class=\"form-control\" id=\"t{$ca['id']}\" name=\"t{$ca['id']}\"><option value=\"\">-</option>";
					for ($i = date('Y'); $i <= (date('Y')+11); $i++)
					{
						$select = $i == $post ? " selected" : "";
						echo @"<option value=\"$i\"$select>$i</option>";
					}
					echo "</select>";
				}
				?>
				<div class="error"><?php echo empty($errors['post']['t'.$ca['id']]) ? '' : $errors['post']['t'.$ca['id']] ?></div>
				<?php 
				echo "</div></div>";
			}
		
			//Session::instance()->set('dt', $data_type);
			Session::instance()->set('cid', Arr::get($_POST, 'cid'));

			if ($has_img == 1)
			{
			
				
				echo "<div class=\"spacer row\"><label class=\"col-lg-3 col-md-3 control-label\" for=\"description\">" . I18n::get('upload_images') . "</label></div>";
				if ($img_count > 0)
				{
				
					echo "<div class=\"row\">";
					$counter = 1;
					
					echo "<div class=\"row\"><div class=\"col-lg-5 col-md-5\">";
					for($i = 1; $i < ($img_count + 1); $i++)
					{
						$img_path = implode('/', str_split($uid, 2));
						$img_path = substr($img_path, 0, 8);
						$image = "//{$cfg["static_host"]}/{$cfg["bucket_name"]}/{$cfg['size_small']}/$img_path/{$uid}_{$i}.jpg?" . rand();
						echo "<span id=\"z$i\" class=\"col-lg-3 col-md-3\"><br><div class=\"thumbnail-box\"><img class=\"thumbnail\" src=\"$image\"></div><br><button class=\"btn btn-default image\" type=\"button\" id=\"a$i\">" . I18n::get('remove') ."</button></span>";
								
					}
					echo "</div></div><br>";
				}
				echo "<div id=\"uploader\"><p>You browser doesn't have Flash, Silverlight, Gears, BrowserPlus or HTML5 support.</p></div>";
			}
			?>
			
			<p><br></p>
			<div class="error" id="eu"><?php echo empty($errors['post']['u']) ? '' : $errors['post']['u'] ?></div>

			
		<?php
		}
		?>
		
		
		
			<p class="text-center"><input class="btn btn-default" type="submit" value="<?php echo I18n::get('submit') ?>" /></p>
		
	</div>
	<input type="hidden" id="h_geo1" value="<?php echo $geo1_id ?>">
	<input type="hidden" id="h_geo2" value="<?php echo $geo2_id ?>">
	<input type="hidden" id="h_neighborhood" value="<?php echo $neighborhood_id ?>">
	<input type="hidden" id="h_city_state">
	
	
	<input type="hidden" id="code" name="code" value="<?php echo $code ?>">
	<input type="hidden" id="u" class="upload" name="u" value="1">
	<input type="hidden" id="r" name="r" value="">
	<input type="hidden" id="cid" name="cid" value="<?php echo $cid ?>">
	<input type="hidden" id="id" name="id" value="<?php echo $id ?>">
	<input type="hidden" id="t" name="t" value="<?php echo $t ?>">
	<input type="hidden" id="iq" name="iq">
	<input type="hidden" id="uuid" name="uuid" value="<?php echo $uuid ?>">
	<input type="hidden" id="c" name="c" value="0">
	<input type="hidden" id="es" name="es" value="0">
	<input type="hidden" id="selling_options" name="selling_options" value="">

</form>



<!--
<link rel="stylesheet" href="//ajax.googleapis.com/ajax/libs/jqueryui/1.8.9/themes/base/jquery-ui.css" type="text/css" />


<link rel="stylesheet" href="//<?php echo $cfg["static_domain"] ?>/css/jquery.ui.plupload.mobile.css" type="text/css" />
!-->
<script src="<?php echo $cfg["jquery.js"] ?>"></script>
<script src="<?php echo $cfg["jquery_validate.js"] ?>"></script>
<script src="<?php echo $cfg["library.js"] ?>"></script>
<!--
<script src="//ajax.googleapis.com/ajax/libs/jqueryui/1.10.2/jquery-ui.min.js"></script>
<script src="//<?php echo $cfg["static_domain"] ?>/js/plupload.js"></script>
<script src="//<?php echo $cfg["static_domain"] ?>/js/plupload.flash.js"></script>
<script src="//<?php echo $cfg["static_domain"] ?>/js/plupload.html4.js"></script>
<script src="//<?php echo $cfg["static_domain"] ?>/js/plupload.html5.js"></script>
<script src="//<?php echo $cfg["static_domain"] ?>/js/jquery.ui.plupload.js"></script>
//-->

<link rel="stylesheet" href="//ajax.googleapis.com/ajax/libs/jqueryui/1.8.9/themes/base/jquery-ui.css" type="text/css" />
<link rel="stylesheet" href="/js/jquery.ui.plupload/css/jquery.ui.plupload.css" type="text/css" />


<script type="text/javascript" src="//ajax.googleapis.com/ajax/libs/jqueryui/1.10.2/jquery-ui.min.js"></script>


<script type="text/javascript" src="/js/plupload.full.min.js"></script>
<script type="text/javascript" src="/js/jquery.ui.plupload.min.js"></script>



<script>
$(document).ready(function() {
	
	txt_new = '<?php echo I18n::get('new') ?>';
	txt_delete = '<?php echo I18n::get('delete') ?>';
	iq_value = "";
	<?php
	if (isset($id))
	{
		echo "id = $id;";
	}
	
	?>
	$('#country').trigger('change');
	/*
	$('#change_listing_location').click(function(e)
	{
		if ($('#div_country:visible').length == 0)
		{
			$('#div_location_outer').show();
		}
		else
		{
			$('#div_location_outer').hide();
		}
	});
	*/
	
	$(function() {
		/*
		$("#uploader").plupload({
			runtimes : 'flash,html5,html4',
			url : '/uploads',
			max_file_size : '5mb',
			max_file_count: 4,
			chunk_size : '1mb',
			multiple_queues : true,
			resize : {
				width : 300, 
				height : 300, 
				quality : 60,
				crop: false
			},
			rename: true,
			sortable: true,
			filters : [
				{title : "Image files", extensions : "jpg,gif,png"},
			],
			flash_swf_url : '/files/plupload.flash.swf',
		});
		*/
		$("#uploader").plupload({
			runtimes : 'html5,flash,silverlight,html4',
			url : '/uploads',
			max_file_size : '5mb',
			max_file_count: 4,
			chunk_size : '1mb',
			rename : true,
			multiple_queues : true,
			resize : {
				width : 600, 
				height : 450, 
				quality : 40,
				crop: false
			},
			filters : [
				{title : "Image files", extensions : "jpg,gif,png"},
			],
			flash_swf_url : '/files/Moxie.swf',
			silverlight_xap_url : '/files/Moxie.xap',
			rename: true,
			sortable: true,
			dragdrop: true,

			views: {
				list: false,
				thumbs: true
			},
			default_view: 'thumbs',
		});
		
		
	});
	
	jQuery.validator.addMethod("check_shipping_service",function(value) {
		return ($("#shipping_service").val() > 0);
	}, "Please add a shipping service");
	
	jQuery.validator.addMethod("check_payment_method",function(value) {
		return ($("#payment_method").val() > 0);
	}, "Please add a payment method");
	
	var validator = $('form#fcontent').validate({
		ignore: "",
		rules: {
			listing_location: {
				required: true,
				minlength: 1
			},
			title: {
				required: true,
				minlength: 3
			},
			price: {
				required: true,
				number: true
			},
			item_condition: {
				required: true,
				minlength: 1
			},
			description: {
				required: true
			},
			shipping_service: {
				check_shipping_service: true,
			},
			payment_method: {
				check_payment_method: true,
			},
		},
	
		submitHandler: function(form) {
			var uploader = $('#uploader').plupload('getUploader');
			if (uploader.files.length > 0) {
				uploader.bind('StateChanged', function() {
					if (uploader.files.length === (uploader.total.uploaded + uploader.total.failed)) {
						document.fcontent.submit();
					}
				});
				uploader.start();
			}
			else
			{
				document.fcontent.submit()
			}
		
		}
	});
	
	$("#p4").click(function() {
		if ($("#p4").prop('checked'))
		{
			$("#l_p4").show();
			$('#third_party_url').addClass('required');
		}
		else
		{
			$("#l_p4").hide();
			$('#third_party_url').removeClass('required');
		}
    }); 
	
	<?php
	
	if ($is_digital == 1)
	{
		echo "rn = $rn;";
	?>
	$('#idd').click(function() {
		if ($('#dd').hasClass('hidden'))
		{
			$('#dd').removeClass('hidden');
		}
		else
		{
			$('#dd').addClass('hidden');
		}
    });
	
	$("#new_content").click(function() {
		
		html = '<div class="div_content row spacer" id="div_' + rn + '"><span class="col-lg-6 col-md-6"><input class="form-control" id="content_' + rn + '" maxlength="256" name="content_new[]" type="text" value=""></span><span class="col-lg-3 col-md-3">' + txt_new + '</span><span class="col-lg-3 col-md-3"><button type="button" id="delete_content_new_' + rn + '" name="delete_content_new_' + rn + '" class="delete_content_new btn btn-default">' + txt_delete + '</button></span></div>';
		
		
		if ($('div').hasClass('div_content'))
		{
			last_id = $('.div_content')[$('.div_content').length - 1].id.split('_')[1];
			$('#div_' + last_id).after(html);
		}
		else
		{
			console.log(html);
			$('#div_content_header').after(html);

		}
		
		
		rn += 1;
    });
	
	$("body").on("click", ".delete_content_new", function(){ 
		id = $(this).attr('id').split('_')[3];
		$('#div_' + id).remove();
	});
	
	$("body").on("click", ".delete_content", function(){ 
		id = $(this).attr('id').split('_')[2];
		$.getJSON('/json/delete_digital_content', { id: id }, function(result) {
			if (result == 1)
			{
				$('#div_' + id).remove();
			}
			else
			{
				html = ' <span class="error"><?php echo I18n::get('error_deleting_record') ?></span>';
				$('#delete_content_' + id).after(html);
			}
		});
	});

	<?php
	}
	
	if ($img_count > 0)
	{
	?>
	$('.image').each(function(index, element)
	{
		
		$(element).click(function(){
			no = ($(element)[0]["id"]).substring(1);
			iq = $('#iq').val();
			iq_value += "_" + no;
			$("#z" + no).hide();
			$('#iq').val(iq_value)
		});
	});
	<?php
	}
	?>
});
</script>