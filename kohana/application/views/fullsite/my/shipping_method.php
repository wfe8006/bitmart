<form class="form-horizontal" id="fcontent" name="fcontent" action="/my/shipping/shipping_method" method="post">
	<?php include "menu.php"; ?>
	<div class="col80 content-right">
		<h4><?php echo I18n::get('shipping') ?></h4><hr>
		<ul class="nav nav-tabs">
			<li><a href="/my/shipping"><?php echo I18n::get('shipping_zones') ?></a></li>
			<li class="active"><a href="/my/shipping/shipping_method"><b><?php echo I18n::get('shipping_services') ?></b></a></li>
		</ul>
		
		
		
		<p><br></p>
		<div class="alert alert-success<?php echo empty($msg) ? " hidden" : "" ?>"><?php echo $msg?></div>
		<p></p>
		<div class="alert alert-info"><?php echo I18n::get('shipping_service_msg') ?></div>	
		<div class="btn-group btn-group-sm">
			<button id="select_all" type="button" class="btn btn-default" value="1"><?php echo I18n::get('check_all') ?></button>
			<button id="add" type="button" class="btn btn-default"><?php echo I18n::get('new') ?></button>
			<button id="edit" type="button" class="btn btn-default"><?php echo I18n::get('edit') ?></button>
			<button id="delete" type="button" class="btn btn-default"><?php echo I18n::get('delete') ?></button>
		</div>
		<p><br></p>
		
		<?php
		if (count($user_shipping_method_obj) > 0)
		{
			echo "<table class=\"table table-striped\" width=\"50%\"><tbody>";
			
			foreach ($user_shipping_method_obj as $result)
			{
				echo "<tr><td><div class=\"checkbox\"><label><input type=\"checkbox\" name=\"cb[]\" class=\"cb checkbox\" value=\"{$result['id']}\"><a href=\"/my/shipping/shipping_method_edit?id={$result['id']}\">" . HTML::chars($result['name']) . "</a></label></div></td></tr>";
			}
			echo "</tbody></table>";
		}
		else
		{
			echo "<div>" . I18n::get('no_results') . "</div>";
		}
		?>
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
			
			if ($( "input:checked" ).length > 0)	
			{
				if (action == 'delete')
				{
					$('form#fcontent').prop('action', '/my/shipping/shipping_method_delete');
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
						window.location.href = '/my/shipping/shipping_method_edit?id='+id;
					}
				}
			}
			
			if (action == 'add')
			{
				window.location.href = '/my/shipping/shipping_method_add';

				
			}
			
			
		});
	});
});
</script>