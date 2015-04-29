<form id="fcontent" name="fcontent" action="/my/listing" method="post">
	<?php include "menu.php"; ?>
	<div class="col80 content-right">
		<h4><?php echo I18n::get('my_listings') ?></h4><hr>

	
	
		<?php
		if ( ! empty($msg))
		{
			echo "<div class=\"alert alert-info alert-block\">$msg</span></div>";
		}
		?>

		<div class="spacer row">
			<div class="col col-lg-3 col-md-3">
				<select class="form-control" id="t" name="t">
					<option value="1"<?php if ($t == 1) echo " selected" ?>><?php echo I18n::get('active_listings') ?></option>
					<option value="2"<?php if ($t == 2) echo " selected" ?>><?php echo I18n::get('past_listings')  ?></option>
					<option value="4"<?php if ($t == 4) echo " selected" ?>><?php echo I18n::get('blocked_listings') ?></option>
				</select>
			</div>
		</div>
		
			<?php
			if (count($listing_result) > 0)
			{
			?>
			<div class="btn-group btn-group-sm">
				<button id="select_all" type="button" class="btn btn-default" value="1"><?php echo I18n::get('check_all') ?></button>
				<?php
				if ($t == 1)
				{
				?>
					<button id="edit" type="button" class="btn btn-default"><?php echo I18n::get('edit') ?></button>
					<button id="close" type="button" class="btn btn-default"><?php echo I18n::get('close') ?></button>
					<button id="delete" type="button" class="btn btn-default"><?php echo I18n::get('delete') ?></button>
				
				<?php
				}
				else
				{
				?>
					<?php if ($t == 2) echo "<button id=\"repost\" type=\"button\" class=\"btn btn-default\">" . I18n::get('repost') . "</button>"; ?>
					<button id="delete" type="button" class="btn btn-default"><?php echo I18n::get('delete') ?></button>
				<?php
				}
				?>
			</div>
			<p><br></p>


			

				<?php
				$counter = 1;
				foreach ($listing_result as $listing)
				{
					if ($counter > 4)
					{
						$counter = 1;
					}
					if ($listing["img_count"] > 0 OR $listing['object_type_id'] == 2)
					{
						$img_path = implode('/', str_split($listing['uid'], 2));
						$img_path = substr($img_path, 0, 8);
						$image = "//{$cfg["www_domain"]}/{$cfg["bucket_name"]}/70/$img_path/{$listing['uid']}_1.jpg";

					}
					else
					{
						$image = "//".$cfg["static_domain"] . "/img/80_no_image.jpg";
					}
					$img = "<img class=\"img-thumbnail\" src=\"$image\">";
					$quantity = $listing['quantity'] == 0 ? "<h5><span class=\"label label-default\">" . I18n::get('out_of_stock') . "</span></h5>" : '';
				?>
				
				<div class="media">
					<div class="pull-left2"><a href="<?php echo '/st/' . $listing['uid'] ?>"><?php echo $img ?></a></div>
					<div class="media-body">
						<h5 class="bold"><a href="<?php echo '/st/' . $listing['uid'] ?>"><?php echo HTML::chars($listing['title']) ?></a></h5>
						<?php echo $quantity ?>

						
						<span class="pull-left"><input type="checkbox" name="cb[]" class="cb" value="<?php echo $listing["id"] ?>"></span> 
						<span class="pull-right"><h4><?php if ($listing['price'] != '') echo HTML::chars($listing['price']) . ' ' . strtoupper($listing['currency']) ?></h4></span>
						
					</div><br>
				</div>
				<hr class="bb">
				
			<?php
				$counter++;
			}
			echo $pagination;
		}
		else
		{
			echo "<div>" . I18n::get('no_results') . "</div>";
		}
		?>
		
	</div>
	<input type="hidden" id="code" name="code">
	<input type="hidden" id="t" name="t" value="<?php echo $t ?>">
</form>	
<script src="<?php echo $cfg["jquery.js"] ?>"></script>
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
					$('input:checkbox').prop('checked','checked');
					$('#select_all').html('<?php echo I18n::get('uncheck_all') ?>');
					$('#select_all').val('0');
				}
				else
				{
					$('input:checkbox').removeAttr('checked');
					$('#select_all').html('<?php echo I18n::get('check_all') ?>');
					$('#select_all').val('1');					
				}
			}
			if ($( "input:checked" ).length > 0)	
			{
				if (action == 'delete')
				{
					$('form#fcontent').prop('action', '/my/listing/' + action);
					document.fcontent.submit()
				}
				else if (action == 'edit')
				{
					if ($( "input:checked" ).length > 1)
					{
					}
					else
					{
						$('form#fcontent').prop('action', '/my/listing/' + action);
						document.fcontent.submit()
					}
				}
				else if (action == 'close' || action == 'repost')
				{
					$('form#fcontent').prop('action', '/my/listing/' + action);
					document.fcontent.submit()
				}
			}
		});
	});

	$('#t').change(function() {
		id = $('#t').val();
		if (id == 1)
			window.location.replace('/my/listing');
		else if (id == 2)
			window.location.replace('/my/listing/past');
		else if (id == 4)
			window.location.replace('/my/listing/blocked');
	});
});
</script>