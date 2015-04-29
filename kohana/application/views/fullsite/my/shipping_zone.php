<form class="form-horizontal" id="fcontent" name="fcontent" action="/my/shipping" method="post">
	<?php include "menu.php"; ?>
	<div class="col80 content-right">
		<h4><?php echo I18n::get('shipping') ?></h4><hr>
		<ul class="nav nav-tabs">
			<li class="active"><a href="/my/shipping"><b><?php echo I18n::get('shipping_zones') ?></b></a></li>
			<li><a href="/my/shipping/shipping_method"><?php echo I18n::get('shipping_methods') ?></a></li>
		</ul>

		<p><br></p>
		<div class="alert alert-info"><?php echo I18n::get('shipping_zone_msg') ?></div>	
		<p><br></p>
		<div class="alert alert-success alert-block<?php echo empty($msg) ? " hidden" : "" ?>"><?php echo $msg?></div>
		<div class="btn-group btn-group-sm">
			<button id="select_all" type="button" class="btn btn-default" value="1"><?php echo I18n::get('check_all') ?></button>
			<button id="add" type="button" class="btn btn-default"><?php echo I18n::get('new') ?></button>
			<button id="edit" type="button" class="btn btn-default"><?php echo I18n::get('edit') ?></button>
			<button id="delete" type="button" class="btn btn-default"><?php echo I18n::get('delete') ?></button>
		</div>
		<p><br></p>
		<table class="table table-striped" width="50%">
			<tbody>
				<tr><td><div class="checkbox"><label><input type="checkbox" name="default_cb[]" class="checkbox" value="" disabled><?php echo I18n::get('default_shipping_zone') ?></label></div></td></tr>
				
		<?php
		if (count($shipping_zone_obj) > 0)
		{
			foreach ($shipping_zone_obj as $result)
			{
				echo "<tr><td><div class=\"checkbox\"><label><input type=\"checkbox\" name=\"cb[]\" class=\"cb checkbox\" value=\"{$result['id']}\"><a href=\"/my/shipping/shipping_zone_edit?id={$result['id']}\">" . HTML::chars($result['name']) . "</a></label></div></td></tr>";
			}
		}
		?>
			</tbody>
		</table>
	</div>
</form>
<script src="<?php echo $cfg["jquery.js"] ?>"></script>
<script src="<?php echo $cfg["library.js"] ?>"></script>
<script>
$(document).ready(function() {
	$('.btn').each(function(index, element)
	{
		
		$(element).click(function(){
			
			action = ($(element)[0]["id"]);
			if (action == 'select_all')
			{
				if ($(this).val() == '1')
				{
					$('.cb').prop('checked','checked');
					$('#select_all').html('<?php echo I18n::get('uncheck_all') ?>');
					$('#select_all').val('0');
				}
				else
				{
					$('.cb').removeAttr('checked');
					$('#select_all').html('<?php echo I18n::get('check_all') ?>');
					$('#select_all').val('1');					
				}
			}
			else if (action == 'add')
			{
				window.location.href = '/my/shipping/shipping_zone_add';
			}
			else
			{
				if ($( "input:checked" ).length > 0)	
				{
					if (action == 'delete')
					{
						$('form#fcontent').prop('action', '/my/shipping/shipping_zone_delete');
						document.fcontent.submit()
					}
					else if (action == 'edit')
					{
						if ($( "input:checked" ).length > 1)
						{
						}
						else
						{
							id = $( "input:checked").val()
							window.location.href = '/my/shipping/shipping_zone_edit?id='+id;
						}
					}
				}
			}
		});
	});
});
</script>