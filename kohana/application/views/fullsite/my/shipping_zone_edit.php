<form class="form-horizontal" id="fcontent" name="fcontent" action="/my/shipping/shipping_zone_edit" method="post">
	<?php include "menu.php"; ?>
	<div class="col80 content-right">
		<h4><?php echo I18n::get('edit_shipping_zone') ?></h4><hr>
		<ul class="nav nav-tabs">
			<li class="active"><a href="/my/shipping"><b><?php echo I18n::get('shipping_zones') ?></b></a></li>
			<li><a href="/my/shipping/shipping_method"><?php echo I18n::get('shipping_methods') ?></a></li>
		</ul>

		
		<p><br></p>
		<div class="alert alert-info alert-block<?php echo empty($msg) ? " hidden" : "" ?>"><?php echo $msg?></div>
		<div class="form-group row">
			<label class="col-lg-3 col-md-3 control-label" for="name"><?php echo I18n::get('shipping_zone_name') ?></label>
			<div class="col-lg-5 col-md-6">
				<input class="form-control" id="name" maxlength="40" name="name" type="text" value="<?php echo HTML::chars($name) ?>">
				<div class="error"><?php echo $errors['name'] ?></div>
			</div>
		</div>
	
		<div class="form-group row">
			<label class="col-lg-3 col-md-3 control-label" for="name"><?php echo I18n::get('country') ?></label>
			<div class="col-lg-5 col-md-6">
				
				<div class="panel-group" id="accordion">
					<?php
					$country_region_id = 0;
					$counter = 0;
					foreach ($country_obj as $result)
					{
						if ($country_region_id != $result['country_region_id'])
						{
							$counter++;
							$country_region_id = $result['country_region_id'];
							//echo "<br>" . $array_region[$result['country_region_id']];
							
							//don't start the first according with </div>
							if ($counter > 1)
							{
								echo "</div></div></div><br>";
							}
					?>
					<div class="panel panel-default">
						<div class="panel-heading">
							<h4 class="panel-title"><a class="accordion-toggle" data-toggle="collapse" data-parent="#accordion" href="#c<?php echo $counter ?>"><?php echo $array_region[$result['country_region_id']] ?></a></h4>
							
						</div>
						<div id="c<?php echo $counter ?>" class="panel-collapse collapse in">
							<div class="panel-body">
								<button id="select_all_<?php echo $counter ?>" type="button" class="btn btn-default"><?php echo I18n::get('check_all') ?></button>
						<?php	
						}
						$checked = in_array($result['id'], $array_country) ? ' checked' : '';
						echo "<div class=\"checkbox\"><label><input type=\"checkbox\" name=\"cb[]\" class=\"cb$counter checkbox\" value=\"{$result['id']}\"$checked> " . $result['name'] . "</label></div>";
					}
					?>
							</div>
						</div>
					</div>
				</div>
		
			
			</div>
		</div>
		

		
		
		
		<p class="text-center"><br><input class="btn btn-default" type="submit" value="<?php echo I18n::get('submit') ?>" /></p>
	</div>
	
	<input type="hidden" id="id" name="id" value="<?php echo $id ?>">

</form>
<script src="<?php echo $cfg["jquery.js"] ?>"></script>
<script src="<?php echo $cfg["library.js"] ?>"></script>
<script src="<?php echo $cfg["jquery_validate.js"] ?>"></script>
<script>
$(document).ready(function() {


	//$(".collapse").collapse();
	//$('#accordion').collapse({hide: true})
	//$('#collapseOne').collapse("hide");

	$(".collapse").collapse("show");
	$('.btn').each(function(index, element)
	{
		$(element).click(function(){
			action = ($(element)[0]["id"]);
			number = action[action.length - 1];
			{
				if ($(this).html() == '<?php echo I18n::get('check_all') ?>')
				{
					$('.cb' + number).prop('checked','checked');
					$('#select_all_' + number).html('<?php echo I18n::get('uncheck_all') ?>');
				}
				else
				{
					$('.cb' + number).removeAttr('checked');
					$('#select_all_' + number).html('<?php echo I18n::get('check_all') ?>');        
				}
			}
			
		});
	});
	
	$('#name').focus();
	var validator = $('form#fcontent').validate({
		rules: {
			name: {
				required: true,
				minlength: 3,
			}
		}
	});

});
</script>