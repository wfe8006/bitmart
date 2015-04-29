<form class="form-horizontal" id="fcontent" name="fcontent" action="/my/message" method="post">
<?php include  __DIR__ . "/../my_menu.php"; ?>
	<div class="col80 content-right">
		<h4><?php echo I18n::get($action) ?></h4><hr>
		<div class="btn-group btn-group-sm">
			<?php
			/*
			$type = 1: inbox
			$type = 2: sent
			$type = 3: archive
			$type = 4: trash
			*/
			$check_all = '';
			$new = "<button id=\"new\" type=\"button\" class=\"btn btn-default\">" . I18n::get('new') . "</button>";
			$archive = '';
			$delete = '';
			$delete_forever = '';
			$move_to_inbox = '';
			if (count($message_obj) > 0)
			{
				$check_all = "<button id=\"select_all\" type=\"button\" class=\"btn btn-default\" value=\"1\">" . I18n::get('check_all') . "</button>";
				$archive = "<button id=\"archive\" type=\"button\" class=\"btn btn-default\">" . I18n::get('archive') . "</button>";
				$delete = "<button id=\"delete\" type=\"button\" class=\"btn btn-default\">" . I18n::get('delete') . "</button>";
				$delete_forever = "<button id=\"delete_forever\" type=\"button\" class=\"btn btn-default\">" . I18n::get('delete_forever') . "</button>";
				$move_to_inbox = "<button id=\"inbox\" type=\"button\" class=\"btn btn-default\">" . I18n::get('move_to_inbox') . "</button>";
			}
			
			if ($type == 1 OR $type == 2)
			{
				$button = "$check_all $new $archive $delete";
			}
			else if ($type == 3)
			{
				$button = "$check_all $new $move_to_inbox $delete";
			}
			else if ($type == 4)
			{
				$button = "$check_all $new $move_to_inbox $archive $delete_forever";
			}
			echo $button;
			?>
			
			
		</div>
		<p><br></p>
<?php
if ( ! empty($msg))
{
	echo "<div class=\"alert alert-success\">$msg</span></div>";
}

if (count($message_obj) > 0)
{
?>
		<p><br></p>
		
		<div class="list-group">
		
		
		
	<?php
	if ($type == 1 OR $type == 2)
	{
			
		



		foreach ($message_obj as $record)
		{
			//inbox or sent folder?
			if ($type == 1)
			{
				$from_to_text = I18n::get('from');
				$from_to = $record['from_user'];
			}
			else
			{
				$from_to_text = I18n::get('to');
				$from_to = $record['to_user'];
			}
			echo "<div class=\"list-group-item\"><input type=\"checkbox\" name=\"cb[]\" class=\"cb checkbox\" value=\"{$record['id']}\"><p class=\"subject\"><b>$from_to_text: $from_to</b></p><p class=\"subject\"><b>" . I18n::get('date') . ": " . date('M d, Y', $record['posted']) . "</b></p><p><b><a href=\"/my/message/detail?id={$record['id']}\">" . HTML::chars($record['subject']) . "</a></b></p></div>";
		}
	}
	else
	{

		foreach ($message_obj as $record)
		{
			echo "<div class=\"list-group-item\"><input type=\"checkbox\" name=\"cb[]\" class=\"cb checkbox\" value=\"{$record['id']}\"><p class=\"subject\"><b>" . I18n::get('from') . ": {$record['from_user']} </b></p><p class=\"subject\"><b>" . I18n::get('to') . ": {$record['to_user']} </b></p><p class=\"subject\"><b>" . date('M d, Y', $record['posted']) . "</b><p><b><a href=\"/my/message/detail?id={$record['id']}\">" . HTML::chars($record['subject']) . "</a></b></p></div>";
			
		}
	}
	?>
		</div>
	<?php
	echo $pagination;
}

else
{
	echo "<div>" . I18n::get('no_results') . "</div>";
}
?>
		
	</div>
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
			else if (action == 'new')
			{
				window.location.href = '/my/message/new';
			}
			else
			{
				if ($( "input:checked" ).length > 0)	
				{
					if (action == 'delete')
					{
						$('form#fcontent').prop('action', '/my/message/do_delete');
					}
					else if (action == 'archive')
					{
						$('form#fcontent').prop('action', '/my/message/do_archive');
					}
					else if (action == 'inbox')
					{
						$('form#fcontent').prop('action', '/my/message/do_inbox');
					}
					else if (action == 'delete_forever')
					{
						$('form#fcontent').prop('action', '/my/message/do_delete_forever');
					}
					document.fcontent.submit()
				}
			}
		});
	});
});
</script>
