<form class="form-horizontal" id="fcontent" name="fcontent" action="/my/tax" method="post">
	<?php include "menu.php"; ?>
	<div class="col80 content-right">
		<h4><?php echo I18n::get('taxes') ?></h4><hr>

		<p><br></p>
		<div class="alert alert-info alert-block<?php echo empty($msg) ? " hidden" : "" ?>"><?php echo $msg?></div>
		<div class="col-lg-12">

			<div class="form-group">
				<span class="col-lg-3 col-md-3 col-sm-3"><b><?php echo I18n::get('country') ?></b></span>
				<span class="col-lg-2 col-md-2 col-sm-2"><b><?php echo I18n::get('tax_rate') ?></b></span>
				<span class="col-lg-3 col-md-3 col-sm-3"><b><?php echo I18n::get('apply_tax_to') ?></b></span>
				<span class="col-lg-4 col-md-4 col-sm-4"></span>
			</div>
			<hr class="hl">
			<?php
			foreach ($country_obj as $result)
			{
			?>
			<div class="form-group">
				<span class="col-lg-3 col-md-3 col-sm-3"><?php echo $result['name'] ?></span>
				<span class="col-lg-2 col-md-2 col-sm-2"><div class="input-group"><input class="form-control rate" id="rate_<?php echo $result['id'] ?>" maxlength="6" name="rate_<?php echo $result['id'] ?>" type="text" value="<?php echo HTML::chars($array_tax[$result['id']]['rate']) ?>"> <span class="input-group-addon">%</span></div></span>
				<span class="col-lg-3 col-md-3 col-sm-3"><select class="form-control" name="type_<?php echo $result['id'] ?>"><option value="1"><?php echo I18n::get('subtotal') ?></option><option value="2"<?php echo $array_tax[$result['id']]['type'] == 2 ? ' selected' : '' ?>><?php echo I18n::get('subtotal') ?> + <?php echo I18n::get('shipping') ?></option></select></span>
				<span class="col-lg-4 col-md-4 col-sm-4">
					<?php
					if (isset($array_tax_region[$result['id']]))
					{
						$region_name = $array_tax_region[$result['id']]['name'];
						$short_name = $array_tax_region[$result['id']]['short_name'];
					?>	
					<div class="btn-group">
						<button type="button" class="btn_region btn btn-default" value="<?php echo $result['id'] ?>"><?php echo $region_name ?></button>
					</div>
					<?php
					}
					?>
				</span>
			</div>
			<hr class="hl">
				<?php
				if (isset($array_tax_region[$result['id']]))
				{
					echo "<div id=\"region_{$result['id']}\" class=\"hidden bg\"><div class=\"container\"><br>";
					foreach ($array_tax_region[$result['id']]['data'] as $index => $value)
					{
					?>
			<div class="form-group">
				<span class="col-lg-3 col-md-3"><?php echo $value ?></span>
				<span class="col-lg-2 col-md-2"><div class="input-group"><input class="form-control rate" id="rate_<?php echo $result['id'] . '_' . $index ?>" maxlength="6" name="rate_<?php echo $result['id'] . '_' . $index ?>" type="text" value="<?php echo HTML::chars($array_tax[$result['id']][$short_name][$index]['rate']) ?>"> <span class="input-group-addon">%</span></div></span>
				<span class="col-lg-3 col-md-3"><select class="form-control" name="type_<?php echo $result['id'] . '_' . $index ?>"><option value="1"><?php echo I18n::get('subtotal') ?></option><option value="2"<?php echo $array_tax[$result['id']][$short_name][$index]['type'] == 2 ? ' selected' : '' ?>><?php echo I18n::get('subtotal') ?> + <?php echo I18n::get('shipping') ?></option></select></span>
				<span class="col-lg-4 col-md-4"></span>
			</div>
			<?php
					}
					echo "</div></div><br>";
				}
			}
			?>
		</div>
		
		<p class="text-center"><br><input class="btn btn-default" type="submit" value="<?php echo I18n::get('submit') ?>" /></p>
	</div>

</form>
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

	$("body").on("blur", ".rate", function(){ 
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
	});
	
	$('.btn_region').each(function(index, element)
	{
		$(element).click(function(){
			id = element.value;
			console.log('region_' + id);
			if ($('#region_' + id).hasClass('hidden'))
			{
				$('#region_' + id).removeClass('hidden');
			}
			else
			{
				$('#region_' + id).addClass('hidden');
			}
		});
	});


});
</script>