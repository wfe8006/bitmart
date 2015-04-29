<div class="col2 col-lg-12 col-md-12">
	<h4><?php echo I18n::get('feedback_for') . " $username" ?></h4><hr>
	<a href="/hub/<?php echo $username ?>"><?php echo I18n::get('back_to') . " $username " . I18n::get('hub') ?></a>
	<p><br><br></p>
	<?php
	function object_to_array($object)
    {
        if (! is_object($object) AND ! is_array($object))
        {
            return $object;
        }
        if (is_object($object))
        {
            $object = get_object_vars($object);
        }
        return array_map('object_to_array', $object);
    }
	
	
	if (count($rating_obj) > 0)
	{
		echo "<table class=\"table\"><thead><tr><td class=\"col-lg-2 col-md-2\"><b>" . I18n::get('order_id') . "</b></td><td class=\"col-lg-1 col-md-1\"><b>" . I18n::get('ratings') . "</b></td><td><b>" . I18n::get('feedback') . "</b></td><td class=\"col-lg-2 col-md-2\"><b>" . I18n::get('username') . "</b></td><td class=\"col-lg-1 col-md-2\"><b>" . I18n::get('date') . "</b></td></tr></thead><tbody>";
		foreach ($rating_obj as $record)
		{
			$order_id = $record['order_id'];
			$feedback_username = $record['username'];
			$rating = json_decode($record['rating']);
			$rating = $rating == '' ? '' : "<b>({$rating}%+)</b>";
			$array_feedback = object_to_array(json_decode($record['feedback']));
			if ($array_feedback['rating'] == '')
			{
				$img = '';
			}
			else
			{
				$img = "<img src=\"/img/{$array_feedback['rating']}star.gif\">";
			}
			
			$feedback = $array_feedback['feedback'];
			$date = date('M d, Y', $array_feedback['timestamp']);
			
			echo "<tr><td>#$order_id</td><td>$img</td><td>$feedback</td><td><a href=\"/hub/$feedback_username\">$feedback_username $rating</a></td><td>$date</td></tr>";

		}
		echo "</tbody></table>";
	}
	?>
</div>
<script src="<?php echo $cfg["jquery.js"] ?>"></script>