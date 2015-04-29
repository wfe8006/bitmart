<form class="form-horizontal" id="fcontent" name="fcontent" action="/my/shipping/shipping_method_<?php echo $method ?>" method="post">
	<?php include "menu.php"; ?>
	<div class="col80 content-right">
		<h4><?php echo $title ?></h4><hr>
		<ul class="nav nav-tabs">
			<li><a href="/my/shipping"><?php echo I18n::get('shipping_zones') ?></a></li>
			<li class="active"><a href="/my/shipping/shipping_method"><b><?php echo I18n::get('shipping_services') ?></b></a></li>
		</ul>
		<p><br></p>
		<div class="alert alert-info"><?php echo I18n::get('shipping_service_add_msg') ?></div>	
		<p><br></p>

		<img class="img-thumbnail" src="/img/shipping_service.png">
		<p><br></p>
		<p><br></p>
		<p><br></p>
		
		
		<div class="form-group row">
			<label class="col-lg-2 col-md-3 control-label" for="shipping_method_name"><?php echo I18n::get('shipping_service_name') ?></label>
			<div class="col-lg-10 col-md-6">
				<input class="form-control" id="shipping_method_name" maxlength="40" name="shipping_method_name" type="text" value="<?php echo HTML::chars($shipping_method_name) ?>">
				<div class="error"><?php echo $errors['shipping_method_name'] ?></div>
			</div>
		</div>
		
		<div class="form-group row">
			<label class="col-lg-2 col-md-3 control-label" for="shipping_zone"><?php echo I18n::get('shipping_zone') ?></label>
			<div class="col-lg-10 col-md-6">
				<!--<div class="checkbox"><label><input type="checkbox" id="sz_1" name="sz_1" class="sz checkbox" value="all" data-zone="Worldwide"><?php echo I18n::get('worldwide') ?></label></div>
				//-->
			
			<?php
				echo "<a id=\"btn_shipping_zone\" class=\"btn btn-default\" href=\"/my/shipping/shipping_zone\">" . I18n::get('add_shipping_zone') . "</a><br><br>";
				foreach ($shipping_zone_array as $key => $value)
				{
					if ($array_user_shipping_method_data AND array_key_exists($key, $array_user_shipping_method_data))
					{
						$checked = ' checked';
					}
					else
					{
						$checked = '';
					}
					echo "<div class=\"checkbox\"><label><input type=\"checkbox\" id=\"sz_$key\" name=\"sz[]\" class=\"sz checkbox\" value=\"$key\" data-zone=\"$value\"$checked>$value</label></div>";
				}
				
				
			?>
				<input type="hidden" name="sz_t">
				<div id="div_sz" name="div_sz"></div>
				<div class="error"><?php echo $errors['shipping_zone'] ?></div>
			</div>
		</div>
	
		<div id="div_cost" class="<?php echo count($array_user_shipping_method_data) == 0 ? 'hidden' : '' ?>">
			<div class="form-group row">
				<label class="col-lg-2 col-md-3 control-label" for="shipping_calculation"><?php echo I18n::get('shipping_calculation') ?></label>
				<div class="col-lg-10 col-md-6">
					<!--
					<div class="radio"><label><input type="radio" name="shipping_calculation" id="sc1" value="1"><?php echo I18n::get('real_time_shipping_rate') ?></label><p class="text-muted"><?php echo I18n::get('real_time_shipping_rate_info') ?></p></div>
					//-->
					<div class="radio"><label><input class="sc" type="radio" name="shipping_calculation" id="sc1" value="1"<?php if ($shipping_calculation == 1) echo ' checked' ?>><?php echo I18n::get('flat_rate_per_item') ?></label><p class="text-muted"><?php echo I18n::get('flat_rate_per_item_info') ?></p></div>
					<div class="radio"><label><input class="sc" type="radio" name="shipping_calculation" id="sc2" value="2"<?php if ($shipping_calculation == 2) echo ' checked' ?>><?php echo I18n::get('flat_rate_per_order') ?></label><p class="text-muted"><?php echo I18n::get('flat_rate_per_order_info') ?></p></div>
					<div class="radio"><label><input class="sc" type="radio" name="shipping_calculation" id="sc3" value="3"<?php if ($shipping_calculation == 3) echo ' checked' ?>><?php echo I18n::get('custom_shipping_table') ?></label><p class="text-muted"><?php echo I18n::get('custom_shipping_table_info') ?></p></div>
					
					<div class="error"></div>
				</div>
			</div>
			
			<div id="div_shipping_rule">
				<div id="cstt" class="form-group row<?php echo $shipping_calculation != 3 ? ' hidden' : '' ?>">
					<label class="col-lg-2 col-md-3 control-label" for="cst_type"><?php echo I18n::get('shipping_rate_based_on') ?></label>
					<div class="col-lg-10 col-md-6">
						<div class="radio"><label><input class="srt" type="radio" name="cst_type" value="1"<?php if ($cst_type == 1) echo ' checked' ?>><?php echo I18n::get('order_weight') ?></label></div>
						<div class="radio"><label><input class="srt" type="radio" name="cst_type" value="2"<?php if ($cst_type == 2) echo ' checked' ?>><?php echo I18n::get('order_total') ?></label></div>
					</div>
				</div>	
			
			
				<div id="cst" class="form-group row<?php echo $shipping_calculation != 3 ? ' hidden' : '' ?>">
					<label class="col-lg-2 col-md-3 control-label" for="shipping_rate_category"><?php echo I18n::get('shipping_rate_category') ?></label>
					<div class="col-lg-10 col-md-6 table-responsive">
						<table class="table">
							<thead>
								<tr>
									<th class="col-lg-1 col-md-1"></th>
									<th class="col-lg-2 col-md-3 min_title"><?php echo $min_title ?></th>
									<th class="col-lg-2 col-md-3 max_title"><?php echo $max_title ?></th>
								</tr>
							</thead>
							<tbody>
								<?php
								if ( ! isset($array_user_shipping_method))
								{
								?>
								<tr class="srb" id="srb_1">
									<td><div class="checkbox"><input id="cb1" type="checkbox" name="cb[]" class="cb checkbox" value="" disabled></div></td>
									<td><input class="form-control minmax" id="cst_min_1" maxlength="10" name="cst_min_1" type="text" value="" placeholder=""></td>
									<td><input class="form-control minmax" id="cst_max_1" maxlength="10" name="cst_max_1" type="text" value="" placeholder=""></td>
								</tr>
								<?php
									$rn = 2;
								}
								else
								{
									//running number
									$rn = 1;
									
									foreach ($array_user_shipping_method as $key => $value)
									{
										if (is_numeric($key))
										{
											if ($rn == 1)
											{
												$cb_disabled = ' disabled';
												$cb_value = '';
											}
											else
											{
												$cb_disabled = '';
												$cb_value = $rn;
											}	
											
										?>	
								<tr class="srb" id="srb_<?php echo $rn ?>">
									<td><div class="checkbox"><input id="cb<?php echo $rn ?>" type="checkbox" name="cb[]" class="cb checkbox" value="<?php echo $cb_value ?>"<?php echo $cb_disabled ?>></div></td>
									<td><input class="form-control minmax" id="cst_min_<?php echo $rn ?>" maxlength="10" name="cst_min_<?php echo $rn ?>" type="text" value="<?php echo HTML::chars($value['min']) ?>" placeholder=""></td>
									<td><input class="form-control minmax" id="cst_max_<?php echo $rn ?>" maxlength="10" name="cst_max_<?php echo $rn ?>" type="text" value="<?php echo HTML::chars($value['max']) ?>" placeholder=""></td>
								</tr>
								<?php
											$rn++;
										}
									}
								}
								?>
								<tr>
									<td colspan="4" align="center">
										<div class="btn-group btn-group-sm">
											<button id="select_all" type="button" class="btn btn-default" value="1"><?php echo I18n::get('check_all') ?></button>
											<button id="add" type="button" class="btn btn-default srb_add"><?php echo I18n::get('new') ?></button>
											<button id="delete" type="button" class="btn btn-default srb_delete"><?php echo I18n::get('delete') ?></button>
										</div>
									</td>
								</tr>	
							</tbody>
						</table>
					</div>
				</div>
				

				<div class="form-group row">
					<label class="col-lg-2 col-md-3 control-label" for="shipping_zone_rate"><?php echo I18n::get('shipping_zone_rate') ?></label>
					<div class="col-lg-10 col-md-9">
						<div class="panel-group" id="accordion">
							<?php
							$count = 1;
							foreach ($shipping_zone_array as $key => $value)
							{
								if ($array_user_shipping_method_data AND array_key_exists($key, $array_user_shipping_method_data))
								{
									$hidden = '';
								}
								else
								{
									$hidden = ' hidden';
								}
							?>
							
							<div id="szr_<?php echo $key ?>" class="panel panel-default szr<?php echo $hidden ?>">
								<div class="panel-heading">
									<h4 class="panel-title"><?php echo $value ?></h4>
								</div>
								<div class="panel-collapse collapse in">
									<div class="panel-body">
									
										<div class="form-group row">
											<div class="col-lg-5 col-md-4"><label><?php echo I18n::get('estimated_delivery_time') ?></label></div>
											<div class="clearfix"></div>
											<div class="col-lg-5 col-md-4">
												<select class="form-control" id="from_<?php echo $key ?>" name="from_<?php echo $key ?>">
													<option value="0"><?php echo I18n::get('from') ?></option>
													<?php
													for ($i = 1; $i < 11; $i++)
													{
														$selected = $array_user_shipping_method_data[$key]['estimated_from'] == $i ? ' selected' : '';
														echo "<option value=\"$i\"$selected>$i</option>";
													}
													?>
												</select>
												<select class="form-control" id="to_<?php echo $key ?>" name="to_<?php echo $key ?>">
													<option value="0"><?php echo I18n::get('to') ?></option>
													<?php
													for ($i = 1; $i < 11; $i++)
													{
														$selected = $array_user_shipping_method_data[$key]['estimated_to'] == $i ? ' selected' : '';
														echo "<option value=\"$i\"$selected>$i</option>";
													}
													if ($array_user_shipping_method_data[$key]['estimated_dayweek'] == 1)
													{
														$day_week_1 = ' checked';
														$day_week_2 = '';
													}
													else if ($array_user_shipping_method_data[$key]['estimated_dayweek'] == 2)
													{
														$day_week_1 = '';
														$day_week_2 = ' checked';
													}
													else
													{
														$day_week_1 = ' checked';
														$day_week_2 = '';
													}
													?>
												</select>
													
												<div class="radio"><label><input class="" type="radio" name="day_week_<?php echo $key ?>" value="1"<?php echo $day_week_1 ?>><?php echo I18n::get('business_days') ?></label></div>
												<div class="radio"><label><input class="" type="radio" name="day_week_<?php echo $key ?>" value="2"<?php echo $day_week_2 ?>><?php echo I18n::get('business_weeks') ?></label></div>
												<div class="error"></div>
											</div>
										</div>
										<div class="table-responsive">
											<table class="table ct<?php echo $shipping_calculation != 3 ? ' hidden' : '' ?>" id="ct_<?php echo $key ?>">
												<thead>
													<tr>
														<th class="min_title"><?php echo $min_title ?></th>
														<th class="max_title"><?php echo $max_title ?></th>
														<th class="text-center" colspan="2"><?php echo I18n::get('fee') ?></th>
													</tr>
												</thead>
												<tbody>
												
													<?php
													if ( ! isset($array_user_shipping_method))
													{
													?>
													<tr id="srb_<?php echo $key ?>_1">
														<td><input class="form-control" id="cst_min_<?php echo $key ?>_1" maxlength="10" name="cst_min_<?php echo $key ?>_1" type="text" disabled></td>
														<td><input class="form-control" id="cst_max_<?php echo $key ?>_1" maxlength="10" name="cst_max_<?php echo $key ?>_1" type="text" disabled></td>
														<td>
															<select class="form-control" id="cst_fee_type_<?php echo $key ?>_1" name="cst_fee_type_<?php echo $key ?>_1">
																<option value="1"><?php echo I18n::get('fixed_rate') . " (" . strtoupper($currency_code) . ")" ?></option>
																<option value="2"><?php echo I18n::get('percentage_rate') . " (%)"  ?></option>
															</select>
														</td>
														<td><input class="form-control" id="cst_fee_<?php echo $key ?>_1" maxlength="10" name="cst_fee_<?php echo $key ?>_1" type="text"></td>
													</tr>
													<?php
													}
													else
													{
														
														
														foreach ($array_user_shipping_method as $row => $value)
														{
															if (is_numeric($row))
															{
													?>
													<tr id="srb_<?php echo "{$key}_{$row}" ?>">
														<td><input class="form-control" id="cst_min_<?php echo "{$key}_{$row}" ?>" maxlength="10" name="cst_min_<?php echo "{$key}_{$row}" ?>" type="text" value="<?php echo HTML::chars($array_user_shipping_method[$row]['min']) ?>" placeholder="" disabled></td>
														<td><input class="form-control" id="cst_max_<?php echo "{$key}_{$row}" ?>" maxlength="10" name="cst_max_<?php echo "{$key}_{$row}" ?>" type="text" value="<?php echo HTML::chars($array_user_shipping_method[$row]['max']) ?>" placeholder="" disabled></td>
														<td>
															<select class="form-control" id="cst_fee_type_<?php echo "{$key}_{$row}" ?>" name="cst_fee_type_<?php echo "{$key}_{$row}" ?>">
																<option value="1"<?php echo $array_user_shipping_method_data[$key][$row]['fee_type'] == 1 ? ' selected' : '' ?>><?php echo I18n::get('fixed_rate') . " (" . strtoupper($currency_code) .")" ?></option>
																<option value="2"<?php echo $array_user_shipping_method_data[$key][$row]['fee_type'] == 2 ? ' selected' : '' ?>><?php echo I18n::get('percentage_rate') . " (%)"  ?></option>
															</select>
														</td>
														<td><input class="form-control" id="cst_fee_<?php echo "{$key}_{$row}" ?>" maxlength="10" name="cst_fee_<?php echo "{$key}_{$row}" ?>" type="text" value="<?php echo HTML::chars($array_user_shipping_method_data[$key][$row]['fee']) ?>"></td>
													</tr>
													<?php
															}
														}
													}
													?>
												</tbody>
											</table>
											
											<table class="table fr<?php echo $shipping_calculation != 3 ? '' : ' hidden' ?>" id="fr_<?php echo $key ?>">
												<thead>
													<tr>
														<th class="col-lg-5 col-md-6 text-center" col="2"><?php echo I18n::get('fee') ?></th>
													</tr>
												</thead>
												<tbody>
													<tr>
														<td>
															<select class="form-control fr_fee_type" id="fr_fee_type_<?php echo $key ?>" name="fr_fee_type_<?php echo $key ?>"<?php echo $shipping_calculation == 1 ? ' disabled' : '' ?>>
																<option value="1"<?php echo $array_user_shipping_method_data[$key]['fee_type'] == 1 ? ' selected' : '' ?>><?php echo I18n::get('fixed_rate') . " (" . strtoupper($currency_code) . ")"  ?></option>
																<option value="2"<?php echo $array_user_shipping_method_data[$key]['fee_type'] == 2 ? ' selected' : '' ?>><?php echo I18n::get('percentage_rate') . " (%)"  ?></option>
															</select>
														</td>
														<td><input class="form-control" id="fr_fee_<?php echo $key ?>" maxlength="10" name="fr_fee_<?php echo $key ?>" type="text" value="<?php echo HTML::chars($array_user_shipping_method_data[$key]['fee']) ?>"></td>
													</tr>
												</tbody>
											</table>
										</div>
									</div>
								</div>
							</div>
							<p><br></p>
							<?php
								$count++;
							}
							?>
						</div>
					</div>
				</div>	
			</div>
		</div>
		<p class="text-center"><br><input class="btn btn-default" type="submit" value="<?php echo I18n::get('submit') ?>" /></p>
	</div>
	<input type="hidden" name="id" value="<?php echo $id ?>">
	<input type="hidden" name="minmax[]" id="minmax_1" value="1">
	<?php
	//running number for hidden input
	for ($hrn = 2; $hrn < $rn; $hrn++)
	{
		echo "<input type=\"hidden\" name=\"minmax[]\" id=\"minmax_$hrn\" value=\"$hrn\">";
	}
	?>
</form>
<script>
fixed_rate = '<?php echo I18n::get('fixed_rate') . " (" . strtoupper($currency_code) . ")"  ?>';
percentage_rate = '<?php echo I18n::get('percentage_rate') . " (%)"  ?>';
check_all = '<?php echo I18n::get('check_all') ?>';
uncheck_all = '<?php echo I18n::get('uncheck_all') ?>';
min_weight = '<?php echo I18n::get('min_weight') . " ($weight_unit)" ?>';
max_weight = '<?php echo I18n::get('max_weight') . " ($weight_unit)" ?>';
min_subtotal = '<?php echo I18n::get('min_subtotal') . " (" . strtoupper($currency_code) . ")" ?>';
max_subtotal = '<?php echo I18n::get('max_subtotal') . " (" . strtoupper($currency_code) . ")" ?>';
rn = <?php echo $rn ?>;

</script>
<script src="<?php echo $cfg["jquery.js"] ?>"></script>
<script src="<?php echo $cfg["library.js"] ?>"></script>
<script src="<?php echo $cfg["jquery_validate.js"] ?>"></script>
<script>
function isNumber(n) {
	return !isNaN(parseFloat(n)) && isFinite(n);
}

function isInt(n) {
   return n % 1 === 0;
}
$(document).ready(function() {


	//$(".collapse").collapse();
	//$('#accordion').collapse({hide: true})
	//$('#collapseOne').collapse("hide");

	//$(".collapse").collapse();
	//$(".collapse").collapse("show");	

	
	$("body").on("blur", ".minmax", function(){ 
		id = $(this).attr("id");
		if (isNumber($('#' + id).val()))
		{
			number = $('#' + id).val();
			if (isInt(number))
			{
				number = parseInt(number).toFixed(2);
			}
			else
			{
				number = parseFloat(number).toFixed(2);
			}
			$('#' + id).val(number);
		}
		minmax = id.split('_')[1];
		no = id.split('_')[2];
		
		
		
		$('.sz').each(function(index, element)
		{
			zone_id = element.id.split('_')[1];
			$('#cst_' + minmax + '_' + zone_id + '_' + no).val($('#' + id).val());
		});
	});
	
	$('.btn').each(function(index, element)
	{
		
		$(element).click(function(){
			action = ($(element)[0]["id"]);
			if (action == 'select_all')
			{
				if ($(this).val() == '1')
				{
					$('.cb').prop('checked','checked');
					$('#cb1').removeAttr('checked');
					$('#select_all').html(uncheck_all);
					$('#select_all').val('0');
				}
				else
				{
					$('.cb').removeAttr('checked');
					$('#select_all').html(check_all);
					$('#select_all').val('1');
				}
			}
			
			
			if (action == 'delete')
			{
				$('.cb').each(function(index, element)
				{
					if (element.checked)
					{
						id = element.value;
						$('#srb_' + id).remove();
						$('#select_all').html(check_all);
						$('#select_all').val('1');
						$('#minmax_' + id).remove();
						
						$('.sz').each(function(index, element)
						{
							zone_id = element.id.split('_')[1];
							$('#srb_' + zone_id + '_' + id).remove();
						});
					
					}
				});
			}
			
			
			if (action == 'add')
			{
				no = rn;
				last_id = $('.srb')[$('.srb').length - 1].id.split('_')[1];
				last_max_value = $('#cst_max_' + last_id).val();

				if (isNumber(last_max_value))
				{
					last_max_value = parseFloat(last_max_value).toFixed(2);
					new_min = parseFloat(last_max_value) + 0.01;
					new_min = new_min.toFixed(2);
				}
				else
				{
					new_min = '';
				}
				
				html = '<tr class="srb" id="srb_' + no + '"><td><div class="checkbox"><input type="checkbox" name="cb[]" class="cb checkbox" value="' + no + '"></div></td><td><input class="form-control minmax" id="cst_min_' + no + '" maxlength="10" name="cst_min_' + no + '" type="text" value="' + new_min + '"></td><td><input class="form-control minmax" id="cst_max_' + no + '" maxlength="10" name="cst_max_' + no + '" type="text" value=""></td></tr>';
				$('#srb_' + last_id).after(html);
				
				$('.sz').each(function(index, element)
				{
					id = element.id.split('_')[1];
					html = '<tr id="srb_' + id + '_' + no + '"><td><input class="form-control" id="cst_min_' + id + '_' + no + '" maxlength="10" name="" type="text" value="' + new_min + '" placeholder="" disabled></td><td><input class="form-control" id="cst_max_' + id + '_' + no + '" maxlength="10" name="" type="text" value="" placeholder="" disabled></td><td><select class="form-control" id="cst_fee_type_' + id + '_' + no + '" name="cst_fee_type_' + id + '_' + no + '"><option value="1">' + fixed_rate + '</option><option value="2">' + percentage_rate + '</option></select></td><td><input class="form-control" id="cst_fee_' + id + '_' + no + '" maxlength="10" name="cst_fee_' + id + '_' + no + '" type="text" value=""></td></tr>';
					$('#srb_' + id + '_' + last_id).after(html);
					
				});
				
				$('<input>').attr({
					type: 'hidden',
					name: 'minmax[]',
					id: 'minmax_' + no,
					value: no
				}).appendTo('form');
				rn += 1;
			}
			
			
		});
	});	
		
	
	$('.sz').each(function(index, element)
	{
		$(element).click(function(){
			id = element.id.split('_')[1];
			if (element.checked)
			{
				if (element.value == '0')
				{
					$('.sz').removeAttr('checked');
					$('.sz').first().prop('checked','checked');
					
					$('.szr').addClass('hidden');
					$('.szr').first().removeClass('hidden');
				}
				else
				{
					$('.sz').first().removeAttr('checked');
					$('.szr').first().addClass('hidden');

					$('#szr_' + id).removeClass('hidden');
				}
				zone_name = $('#sz_'+id).data("zone");
			}
			else
			{
				id = element.id.split('_')[1];
				$('#szr_' + id).addClass('hidden');
			}
			
			if ($(".sz:checked").length > 0)
			{
				$('#div_cost').removeClass('hidden');
			}
			else
			{
				$('#div_cost').addClass('hidden');
			}
		});
		
	});
	
	$('.sc').each(function(index, element)
	{
		$(element).click(function()
		{
			if (element.id == 'sc3')
			{
				$('#div_shipping_rule').removeClass('hidden');
				$('.fr').addClass('hidden');
				$('.ct').removeClass('hidden');
				
				$('#cst').removeClass('hidden');
				$('#cstt').removeClass('hidden');
			}
			else if (element.id == 'sc1' || element.id == 'sc2')
			{
				$('#div_shipping_rule').removeClass('hidden');
				$('.fr').removeClass('hidden');
				$('.ct').addClass('hidden');
				$('#cst').addClass('hidden');
				$('#cstt').addClass('hidden');
				if (element.id == 'sc1')
				{
					$('.fr_fee_type option:first-child').attr("selected", "selected");
					$('.fr_fee_type').prop('disabled', 'disabled');
				}
				else
				{
					$('.fr_fee_type').prop('disabled', false);
				}
			}
		});
		
	});
	
	$('.srt').each(function(index, element)
	{
		$(element).click(function()
		{
			
			if (element.value == '1')
			{
				$('.min_title').html(min_weight);
				$('.max_title').html(max_weight);
			}
			else
			{
				$('.min_title').html(min_subtotal);
				$('.max_title').html(max_subtotal);
			}
		});
		
	});
	
	
	//$('#shipping_method_name').focus();

	jQuery.validator.addMethod("check_shipping_zone",function(value) {
		return ($(".sz:checked").length > 0);
	}, "This field is required.");
	
	
	var validator = $('form#fcontent').validate({
		ignore: "",
		rules: {
			shipping_method_name: {
				required: true,
				minlength: 3,
			},
			sz_t: {
				check_shipping_zone: true,
			},


		},
		errorPlacement: function(error, element) {
			if (element.attr("name") == "sz_t") {
				error.insertAfter("#div_sz");
			} else {
				error.insertAfter(element);
			}
		}
		
	});

});
</script>