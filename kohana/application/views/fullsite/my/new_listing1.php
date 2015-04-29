<?php
$count = 0;
?>
<?php include "menu.php"; ?>
<div class="col80 content-right">
	<h4><?php echo I18n::get('new_listing') ?></h4><hr>
	<?php
	
	$pm_count = 0;
	if (count($array_payment_option) > 0)
	{
		foreach ($array_payment_option as $name => $value)
		{
			$active = $value['active'];
			if ($active == 1)
			{
				$pm_count += 1;
			}
		}
	}

	$payment_method = $pm_count == 0 ? 0 : 1;
	$shipping_service = count($user_shipping_method_obj) > 0 ? 1 : 0;
	

	if ($payment_method == 0 OR $shipping_service == 0)
	{
	?>
	
	<div class="jumbotron">
		<h2><?php echo I18n::get('selling_for_the_first_time_title') ?></h2>
		<p class="lead"><?php echo I18n::get('selling_for_the_first_time_message') ?></p>
		<p><?php if ($payment_method == 0) echo "<a id=\"btn_payment_method\" class=\"btn btn-success\" href=\"/my/payment\">" . I18n::get('add_payment_method') . "</a>" ?> <?php if ($shipping_service == 0) echo "<a id=\"btn_shipping_service\" class=\"btn btn-success\" href=\"/my/shipping/shipping_method\">" . I18n::get('add_shipping_service') . "</a>" ?> <a id="btn_payment_method" class="btn btn-success" href="/my/general"><?php echo I18n::get('change_currency') ?></a></p>
	</div>
	<?php
	}
	?>
	<br><div class="alert alert-info"><?php echo I18n::get('selling_digital_goods_message') ?></div>
	
	<form class="form-horizontal" id="fcontent2" name="fcontent2" action="/my/listing/<?php echo $action ?>" method="post">		
		<div class="col-lg-8 col-md-8 pull-left">
			<!--<h2>No barcode?</h2>//-->
			<p><?php echo I18n::get('please_select_a_category') ?></p>
		</div>
		<div class="clearfix"></div>
		

		<?php
		//current max level
		$nav_bar = "";
		$selected = 0;
		
		
		//$cat_arr[0] always contain the root parent id eg: for sale, jobs, etc. it's not needed here, so we always start from index[1]
		foreach ($cats_result as $cat_result)
		{
			echo "<div id=\"b$count\" class=\"spacer col-lg-4\"><select class=\"form-control\" id=\"cat$count\" name=\"cat$count\" size=\"10\"><option value=\"0\">" . I18n::get('select_one') . "</option>";	
			foreach ($cat_result as $cat)
			{
				$selected = $cat['id'] == $cat_arr[$count] ? " selected" : "";
				if ($cat["id"] == $cat_arr[$count])
				{
					$selected = " selected";
					$nav_bar .= "<li class=\"active\"><a href=\"/?cid={$cat['id']}\">{$cat['name']}</a></li>";
				}
				else
				{
					$selected = "";
				}
				$arrow = $cat['has_child'] == 1 ? ' >' : '';
				echo "<option value=\"{$cat['id']}\"$selected>{$cat['name']}$arrow</option>";
			}
			$ncount = $count + 1;
			echo "</select></div>";
			$count++;
		}
		//$nav_bar = substr($nav_bar, 0, -3);
		$selected = 1;
		$count -= 1;
		



		?>


		<div class="clearfix"></div>
		<ul id="breadcrumb" class="breadcrumb"><li><b><?php echo I18n::get('selected_category') ?>:</b></li> <?php echo $nav_bar ?></ul>
		<div id="msg" class="alert alert-block hidden"><?php echo $msg?></div>	
		<p class="text-center"><input class="btn btn-default" type="submit" value="<?php echo I18n::get('continue') ?>" /></p>

		<input type="hidden" id="s" name="s" value="2">
		<input type="hidden" id="cid" name="cid" value="0">
		<input type="hidden" id="listing_id" name="listing_id" value="<?php echo $listing_id ?>">
	</form>
</div>

<script src="<?php echo $cfg["jquery.js"] ?>"></script>
<script src="<?php echo $cfg["library.js"] ?>"></script>
<script>

$(document).ready(function() {

	$('#search').click(function(e)
	{
		$('#fcontent1').submit();
	});

	$('#fcontent2').submit(function(e)
	{
		error = 1;
		if ($('#cat' + selected)) 
		{
			if ($('#cat' + selected).val() < 1)
				error = 1;
			else
				error = 0;
		}
		else 
		{
			error = 1;
		}

		if (has_child == 1 || error == 1)
		{
			error = 1;
		}

		if (error == 1)
		{
			$('#msg').html('<span class="error">Please select a category</span>');
			$('#msg').show();
			return false;
		}
		else
		{
			$('#cid').val($('#cat' + selected).val());
			return true;
		}
	});

	dl = 1;
	selected = 0;
	has_child = 1;
	error = 0;
	keystore = new Object();
	cl = 0;
	ml = <?php echo $count ?>;
	selected = dl = ml;
	if ($('#cat0').val() > 0)
	{
		if (ml > 0)
		{
			has_child = 0;
		}
	}
	for (i = 0; i <= ml; i++)
	{
		$('#cat' + i).change(function(e)
		{
			ii = e.target.name.substring(3);
			loadcat(ii);
		});
	}

	$('#id1').change(function(){
		id = $('#id').val();
		window.location.replace('<?php echo $action ?>?id=' + id + '<?php echo $url ?>');
	});
});
</script>