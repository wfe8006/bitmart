<?php include  __DIR__ . "/../my_menu.php"; ?>





	<div class="col80 content-right">
		<h4><?php echo $title ?></h4><hr>
<?php
function object_to_array($object)
{
	if ( ! is_object($object) AND ! is_array($object))
	{
		return $object;
	}
	if (is_object($object))
	{
		$object = get_object_vars($object);
	}
	return array_map('object_to_array', $object);
}

if ( ! empty($msg))
{
	echo "<div class=\"alert alert-info alert-block\">$msg</span></div>";
}

if (count($order_obj) > 0)
{
	echo "<div class=\"hidden-xs col-lg-6 col-md-6 col-sm-6\"><b>" . I18n::get('item') . "</b></div><div class=\"hidden-xs col-lg-2 col-md-2 col-sm-2\"><b>" . I18n::get('status') . "</b></div><div class=\"hidden-xs col-lg-2 col-md-2 col-sm-2\"><b>" . I18n::get('date') . "</b></div><div class=\"hidden-xs col-lg-2 col-md-2 col-sm-2\"><b>" . I18n::get('total') . "</b></div><hr>";
	foreach ($order_obj as $record)
	{
		$title_str = '<br>';
		$array_data = object_to_array(json_decode($record['data']));
		
		foreach ($array_data['item'] as $ld_id => $value)
		{
			$title_str .= "<br><a href=\"/st/{$value['uid']}\">" . HTML::chars($value['title']) . "</a> x " . HTML::chars($value['quantity']);
		}
		
		echo "<div class=\"col-lg-6 col-md-6 col-sm-6\"><a class=\"btn btn-default\" href=\"https://" . $cfg['www_domain'] . "/my/$url/detail?id={$record['order_id']}\">" . I18n::get('view_order') . "</a>$title_str<br><br></div><div class=\"col-lg-2 col-md-2 col-sm-2\">{$record['order_status']}</div><div class=\"col-lg-2 col-md-2 col-sm-2\">" . date('M d, Y', $record['submitted']) ."</div><div class=\"col-lg-2 col-md-2 col-sm-2\"><b>{$array_data['new_total']} " . strtoupper($array_data['new_currency_code']) . "</b></div><div class=\"clearfix\"></div><hr>";


		
	}

	echo $pagination;
	
}

else
{
	echo "<div>" . I18n::get('no_results') . "</div>";
}
?>
		
<script src="<?php echo $cfg["jquery.js"] ?>"></script>
