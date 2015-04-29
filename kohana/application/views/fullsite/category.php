<?php
$cfg = Kohana::$config->load('general.default');
$domain = "http://" . $cfg['www_domain'];
$cid = (int)$cid;
$root_ids = DB::query(Database::SELECT, "SELECT name, node_path FROM categories_entity WHERE id = $cid")->execute();
$root_array = explode(".", $root_ids[0]["node_path"]);
$root_id = $root_array[0];
include( __DIR__ . '/../../classes/controller/country_list.php');

if ($country == "244")
{
	$nonus_display = "hidden";
	$us_display = "block";
}
else
{
	$nonus_display = "block";
	$us_display = "hidden";
}

function build_list($root_id, $cid)
{
	$cats = DB::query(Database::SELECT, "SELECT id, name FROM categories_entity WHERE parent_id = $root_id AND active = '1' ORDER BY name")->execute();
	foreach ($cats as $cat)
	{
		$selected = $cat["id"] == $cid ? " selected" : "";
		echo "<option value=\"{$cat["id"]}\"$selected> - {$cat["name"]}</option>";
	}
}

function build_link($key = "", $value = "")
{
	$qs = $_GET;
	
	//when people are on for sale page2, we do not want them to click on appliance (cid=552&page=2) because there's no page2 on appliance category
	unset($qs['page']);
	
	if ($key != "")
	{
		$qs[$key] = $value;
	}
	if ($key == 'cid' AND $value == 1)
	{
		unset($qs['cid']);
	}
	return "?" . htmlspecialchars(http_build_query($qs),ENT_QUOTES);
}


$counter = 1;
$col = 1;

$cookie_preference = Cookie::get("preference", 0);
if ($cookie_preference !== 0)
{
	$cookie_preference = json_decode($cookie_preference);
	$cookie_currency_code = $cookie_preference->currency_code;
	$convert_currency = $cookie_preference->convert_currency;
}
else
{
	$cookie_currency_code = 'usd';
	$convert_currency = 0;
}
?>

<div class="col20 content-left">
<?php
if (Request::current()->controller() == 'hub')
{
	$rating = $rating == '' ? '-' : "$rating%";
	$total_rating = $total_rating == '' ? '-' : $total_rating;
?>
	<div class="row">
		<div class="col-lg-10 col-md-11">
			<div class="thumbnail">
				<div class="caption">
					<h4><b><?php echo $username ?></b></h4>
					<hr>
					<h2><b><?php echo $rating ?></b></h2>
					<h6><span class="text-muted"><?php echo I18n::get('positive_feedback') ?></h6>
					<h2><b><?php echo $total_rating ?></b></h2>
					<h6><span class="text-muted"><a href="/feedback/<?php echo $username ?>"><?php echo I18n::get('total_ratings') ?></a></h6>
				</div>
			</div>
		</div>
	</div>
	<p><br><br><br></p>
<?php
}
?>
	<h4><?php echo I18n::get('categories') ?></h4>
	<div class="list-group categories">
	<?php
	$counter = 0;
	foreach ($nav as $item)
	{
		if ($counter == 0)
		{
			if ($cid == 1)
			{
				echo "<a href=\"#\" class=\"list-group-item disabled\">" . I18n::get('everything') . "</a>";
				$has_price = $has_img = 1;
			}
			else
			{
				$link = build_link("cid", 1);
				echo "<a href=\"$link\" class=\"list-group-item\">" . I18n::get('everything') . "</a>";
			}
		}
		else if ($counter == count($nav) - 1)
		{
			if ($item['id'] > 0)
			{
				echo "<a href=\"#\" class=\"list-group-item\">&#9492; {$item['name']}</a>";
			}
		}
		else
		{
			$link = build_link("cid", $item["id"]);
			echo "<a href=\"$link\" class=\"list-group-item\">&#9492; {$item['name']}</a>";
		}
		$has_img = $item['has_img'];
		$has_price = $item['has_price'];
		$counter++;
	}

	foreach ($subcats as $id => $name)
	{
		$link = build_link("cid", $id);
		echo "<a href=\"$link\" class=\"list-group-item\">&nbsp;&nbsp;&nbsp;&nbsp;$name</a>";
	}
	?>
	</div>
	<hr>
	<div class="form-group">
        <label for="item_location"><?php echo I18n::get('item_location') ?></label>
		<form action="/preference" id="ffrom" name="ffrom" method="get">
			<select class="form-control" id="item_location" name="item_location">
				<option value=""><?php echo I18n::get('select_one') ?></option>
				<?php
				foreach ($country_list as $id => $name)
				{
					$selected = $id == $item_location ? " selected" : "";
					echo "<option value=\"$id\"$selected>$name</option>";
				}
				?>
			</select>
		</form>
	</div>	
	<div class="form-group">
        <label for="ship_to"><?php echo I18n::get('ship_to') ?></label>	
		<form action="/preference" id="fto" name="fto" method="get">
			<select class="form-control" id="ship_to" name="ship_to"><option value=""><?php echo I18n::get('select_one') ?></option>
			<?php
			foreach ($country_list as $id => $name)
			{
				$selected = $id == $ship_to ? " selected" : "";
				echo "<option value=\"$id\"$selected>$name</option>";
			}
			?>
			</select>
		</form>
	</div>
</div>


<div class="col80 content-right">
  <h4>&nbsp;</h4>
 
 <div> 
<?php 
foreach ($listing_obj as $id => $listing)
{
	$price_usd = $listing['price_usd'];
	$price = $listing['price'];
	$currency_code = $listing['currency_code'];
	if ($counter > 4)
	{
		$counter = 1;
	}

	$idd = $listing['idd'] == 1 ? "<h5><div class=\"label label-primary\">" . I18n::get('instant_delivery') . "</div></h5>" : '';
	$url = '/st/' . $listing['uid'];
	$bucket = 'st';

	if ($listing['img_count'] > 0 OR $listing['object_type_id'] == 2)
	{
		$img_path = implode('/', str_split($listing['uid'], 2));
		$img_path = substr($img_path, 0, 8);
		$image = "<a href=\"$url\"><img class=\"wh img-responsive\" src=\"//{$cfg["www_domain"]}/{$cfg["bucket_name"]}/{$cfg['size_medium']}/$img_path/{$listing['uid']}_1.jpg\"></a>";
	}
	else
	{
		$image = "<a href=\"$url\"><img class=\"wh img-responsive\" src=\"//".$cfg["static_domain"] . "/img/{$cfg['size_medium']}_no_image.jpg\"></a>";

	}
	
	/*
	if ($col > 4)
	{
		echo '</div><hr>';
		$col = 1;
	}
	if ($col == 1)
	{
		echo '<div class="row">';
	}
	*/
	if ($listing['price'] == '')
	{
		$price = '';
	}
	else
	{
		

		if ($cryptocurrency === 0)
		{
			if (array_key_exists($currency_code, $cfg_crypto))
			{
				$final_price = sprintf("%0.5f", $price) . ' ' . strtoupper($currency_code);
			}
			else
			{
				$final_price = "$price " . strtoupper($currency_code);
			}
			if ($currency == $cookie_currency_code OR $convert_currency == 0)
			{
				$show_display = 0;
			}
			else
			{
				$show_display = 1;
				$ori_price = sprintf("%0.2f", $cfg_currency['usd_' . $cookie_currency_code] * $price_usd) . ' ' . strtoupper($cookie_currency_code);
			}
		}
		else
		{
			//listing currency is crypto, if selected crypto currency is the same as listed currency
			if (array_key_exists($currency_code, $cfg_crypto) AND $cryptocurrency == $currency_code)
			{
				$final_price = sprintf("%0.5f", $price) . ' ' . strtoupper($currency_code);
				$show_display = 0;
			}
			else
			{
				$final_price = sprintf("%0.5f", $cfg_currency['usd_' . $cryptocurrency] * $price_usd) . " " . strtoupper($cryptocurrency);
			}
			if ($convert_currency == 0)
			{
				if ($cryptocurrency != $currency_code)
				{
					if (array_key_exists($currency_code, $cfg_crypto))
					{
						$ori_price = sprintf("%0.5f", $price) .' ' . strtoupper($currency_code);
					}
					else
					{
						$ori_price = "$price " . strtoupper($currency_code);
					}
					$show_display = 1;
				}
			}
			else
			{
				$show_display = 1;
				$ori_price = sprintf("%0.2f", $cfg_currency['usd_' . $cookie_currency_code] * $price_usd) . ' ' . strtoupper($cookie_currency_code);
			}
		}
		

		if ($price != '')
		{
			if ($show_display == 1)
			{
				$price = "<p><span class=\"main_price1\"><b>$final_price</b></span><br><span class=\"main_price_ori1\">$ori_price</span></p>";
			}
			else
			{
				$price = "<p><span class=\"main_price1\"><b>$final_price</b></span></p><br>";
			}
		}

		
		//$price = "<p class=\"pull-right\"><h4>$cc</h4> <h5 class=\"text-warning\"><b>" . HTML::chars($listing['price']) . ' ' . strtoupper($listing['currency_code']) . "</b></h5></p>";
		
		
		/*
		if ($cryptocurrency === 0)
		{
			if ($listing['currency_code'] == $currency_code OR $convert_currency == 0)
			{
				$price = "<p class=\"pull-right\"><h4>" . HTML::chars($listing['price']) . ' ' . strtoupper($listing['currency_code']) . "</h4></p>";
			}
			else
			{
				$price = "<p class=\"pull-right\"><h4>" . sprintf("%0.2f", $cfg_currency['usd_' . $currency_code] * $listing['price_usd']) . " " . strtoupper($currency_code) . "</h4></p>";
			}
		}
		else
		{

			$cc = sprintf("%0.5f", $listing['price_usd'] * $cfg_currency['usd_' . $cryptocurrency]) . " " . strtoupper($cryptocurrency);
			if ($listing['currency_code'] == $currency_code OR $convert_currency == 0)
			{
				$price = "<p class=\"pull-right\"><h4>$cc</h4> <h5 class=\"text-warning\"><b>" . HTML::chars($listing['price']) . ' ' . strtoupper($listing['currency_code']) . "</b></h5></p>";
			}
			else
			{
				$price = "<p class=\"pull-right\"><h4>$cc</h4> <h5 class=\"text-warning\"><b>" . sprintf("%0.2f", $cfg_currency['usd_' . $currency_code] * $listing['price_usd']) . " " . strtoupper($currency_code) . "</b></h5></p>";
			}
		}
		*/
	}
	?>
	

	
	
	
	<div class="media">
		<div class="col-lg-3 col-md-3 col-sm-3 pull-left tn">
		  <a class="" href="#">
			<div class="displayed"><center><?php echo $image ?></center></div>
		  </a>
		</div>
		<div class="media-body col-lg-9 col-md-9 col-sm-9">
			<div class="col-lg-9 col-md-9 col-sm-9">
				<p><a href="<?php echo $url ?>"><b><?php echo HTML::chars($listing['title']) ?></b></a><p>
				<p><?php echo $idd ?></p>
			</div>
			<div class="media-body col-lg-3 col-md-3 col-sm-3">
			<?php echo $price ?>
			</div>
		</div>
	</div>
	<hr>
	
	

	<?php
	$counter++;
	
	
	
	
	
	//$col++;
}
//echo "</div>";
?>
</div>


 

<div class="clearfix"></div><?php echo $pagination ?> 
 

 
</div>


<script src="<?php echo $cfg["jquery.js"] ?>"></script>
<script>
$(document).ready(function() {
	$('#item_location').change(function(){
		$('form#ffrom').submit();
	});
	$('#ship_to').change(function(){
		$('form#fto').submit();
	});
});
</script>
