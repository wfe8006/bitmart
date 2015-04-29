<?php
$view = View::factory(TEMPLATE . '/category');

function dec2bin_i($decimal_i)
{
	bcscale(0);
	$binary_i = '';
	do
	{
		$binary_i = bcmod($decimal_i,'2') . $binary_i;
		$decimal_i = bcdiv($decimal_i,'2');
	}
	while (bccomp($decimal_i,'0'));
	return($binary_i);
}

//current category, so that when user search by keyword, we can use $c to compare with the newly selected $cid, drop all the product attributes filtering if they are not matched.
$c = Arr::get($_GET, "c", 0);
$cid = (int)Arr::get($_GET, "cid", 1);
$l = Arr::get($_GET, "l");
$s = Arr::get($_GET, "s");
$r = Arr::get($_GET, "r");
$q = Arr::get($_GET, "q", "");
$min = Arr::get($_GET, "min");
$max = Arr::get($_GET, "max");
$uid = Arr::get($_GET, "uid");
$ref = Arr::get($_GET, "ref");

$geo1 = Arr::get($_GET, "geo1");
$geo2 = Arr::get($_GET, "geo2");
$filter_type = 0;

$qs = "";
$city = '';
$state = '';
$neighborhood_arr = array();
$neighborhood_filter = array();
$rf_arr = array();
$items_arr = array();
$data_filter = array();
$data_arr = array();
$attributes_arr = array();
$zip = "";
$limit = $this->cfg['item_per_page'];
$offset = ((int)Arr::get($_GET, 'page', 1) - 1) * $limit;

$qr = "";
$params = "";
$error_location = "";
$doc_arr = array();
$listing_obj = array();
$subcats = array();


$cookie_preference = Cookie::get("preference", 0);
$cryptocurrency = 0;
if ($cookie_preference !== 0)
{
	$cookie_preference = json_decode($cookie_preference);
	$cryptocurrency = $cookie_preference->cryptocurrency;
	$item_location = $cookie_preference->item_location;
	$ship_to = $cookie_preference->ship_to;
}

$categories_entity_obj = DB::query(Database::SELECT, "SELECT node_path FROM categories_entity WHERE id = :cid")
->param(':cid', $cid)
->execute();
if (count($categories_entity_obj) > 0)
{
	$node_path = $categories_entity_obj[0]['node_path'];
	$array_category = explode('.', $node_path);
	$node_path_length = count($array_category) + 1;
}
else
{
	Request::current()->redirect("/");
}


		
$has_child = 0;
//to build the navigation bar: Home >> For Sale -> Books ....
$nav = DB::query(Database::SELECT, "WITH RECURSIVE subcategories AS (
SELECT id, parent_id, has_child, has_price, has_img, node_path FROM categories_entity WHERE id = :cid UNION ALL SELECT c.id, c.parent_id, c.has_child, c.has_price, c.has_img, c.node_path FROM categories_entity c JOIN subcategories sc ON c.id = sc.parent_id) SELECT id, name, has_child, has_price, has_img, node_path FROM categories_entity c WHERE c.id IN (SELECT id FROM subcategories GROUP BY id) ORDER BY parent_id")
->param(':cid', $cid)
->execute();
foreach($nav as $item) 
{
	$has_child = $item['has_child'];
}


$params = array();
$params2 = array();

$query_user = '';
if (Request::current()->controller() == 'hub')
{
	$query_user = "AND (ld.listing->'user_id')::integer = :user_id";
	$params[':user_id'] = $user_id;
	$params2[':user_id'] = $user_id;
	$view->user_id = $user_id;
	$view->username = $username;
	$view->rating = $rating;
	$view->total_rating = $total_rating;
}

$query_keyword = '';
if ($q != '')
{
	$query_keyword = "AND l.fts @@ to_tsquery(:q)";
	$params[':q'] = $q;
	$params2[':q'] = $q;
}

$query_item_location = '';
if (isset($item_location) AND $item_location != '')
{
	$query_item_location = "AND (ld.listing->'country_id')::integer = :item_location";
	$params[':item_location'] = $item_location;
	$params2[':item_location'] = $item_location;
}

$query_ship_to = '';
if (isset($ship_to) AND $ship_to != '')
{
	
	$country_binary = dec2bin_i(bcpow(2, $ship_to));
	$country_binary = str_pad($country_binary, 256, "0", STR_PAD_LEFT);
	$query_ship_to = "AND (ship_to & b'$country_binary') > b'0000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000'";
}

$query_cryptocurrency = '';
if ($cryptocurrency !== 0)
{
	if (array_key_exists($cryptocurrency, $this->cfg_crypto))
	{
		$query_cryptocurrency = "AND (ld.listing->'cryptocurrency')::integer & {$this->cfg_crypto[$cryptocurrency]['constant']} > 0";
	}
	else
	{
		$cryptocurrency = 0;
	}
}

//if this category $cid has child
if ($has_child == 1)
{
	$direct_category_obj = DB::query(Database::SELECT, "SELECT id, name FROM categories_entity WHERE parent_id = :cid AND active = '1' AND count > '0' ORDER BY name")
	->param(':cid', $cid)
	->execute();
	$category_query = '';
	$array_subcat = array();


	if (count($direct_category_obj) > 0)
	{
		foreach ($direct_category_obj as $record)
		{
			$id = $record['id'];
			$name = $record['name'];
			$category_query .= "ce.node_path ~ :cat$id OR ";
			$array_subcat[$id] = $name;
			$params[":cat$id"] = "*.$id.*";
		}
		
		$category_query = substr($category_query, 0, -4);
		$category_obj = DB::query(Database::SELECT, "SELECT subpath(ce.node_path, 0, :node_path_length) AS node_path, count(ld.id) AS count FROM listing_data ld LEFT JOIN listing l ON ld.listing_id = l.id LEFT JOIN categories_entity ce ON l.cid = ce.id WHERE (ld.listing->'quantity')::integer > 0 AND ld.status = '1' $query_keyword $query_cryptocurrency AND ($category_query) AND ce.count > 0 $query_user $query_item_location $query_ship_to GROUP BY subpath(ce.node_path, 0, :node_path_length)")
		->param(':node_path_length', $node_path_length)
		->parameters($params)
		->execute();

		$subcats = array();
		foreach ($category_obj as $record)
		{
			$array_node_path = explode('.', $record['node_path']);
			$cid = $array_node_path[$node_path_length - 1];
			if (array_key_exists($cid, $array_subcat))
			{
				//push valid filtered category to $subcats array
				$subcats[$cid] = $array_subcat[$cid];
			}
		}
	}
}


$listing_obj = DB::query(Database::SELECT, "SELECT min(ld.listing->'price_usd') AS price_usd, ld.listing->'price' AS price, ld.listing->'currency_code' AS currency_code, ld.listing->'idd' AS idd, ld.listing_id, l.title, l.img_count, l.uid FROM listing_data ld LEFT JOIN listing l ON ld.listing_id = l.id LEFT JOIN categories_entity ce ON l.cid = ce.id WHERE (ld.listing->'quantity')::integer > 0 AND ld.status = '1' AND ce.node_path ~ :node_path_query $query_keyword $query_cryptocurrency $query_user $query_item_location $query_ship_to GROUP BY ld.listing->'price', ld.listing->'currency_code', ld.listing->'idd', ld.listing_id, l.title, l.img_count, l.uid ORDER BY ld.listing_id DESC LIMIT :limit OFFSET :offset")
->param(':node_path_query', "$node_path.*")
->param(':limit', $limit)
->param(':offset', $offset)
->parameters($params2)
->execute();


$listing_count_obj = DB::query(Database::SELECT, "SELECT count(ld.id) AS total FROM listing_data ld LEFT JOIN listing l ON ld.listing_id = l.id LEFT JOIN categories_entity ce ON l.cid = ce.id WHERE (ld.listing->'quantity')::integer > 0 AND ld.status = '1' AND ce.node_path ~ :node_path_query $query_keyword $query_cryptocurrency $query_user $query_item_location $query_ship_to")
->param(':node_path_query', "$node_path.*")
->parameters($params2)
->execute();


//sort neighborhoods, A-Z
//ksort($neighborhood_arr);

$pagination = Pagination::factory(array(
		'query_string'   => 'page',
		'total_items'    => $listing_count_obj[0]["total"],
		'items_per_page' => $limit,
		'style'          => 'classic',
		'auto_hide'      => TRUE
	));
	


$view->cid = $cid;
//$view->q = $q;

$this->template->q = $q;
$view->cryptocurrency = $cryptocurrency;
$view->min = $min;
$view->max = $max;
$view->r = $r;
$view->s = $s;
$view->l = $l;
$view->error_location = $error_location;

$view->item_location = $item_location;
$view->ship_to = $ship_to;

$view->pagination = $pagination->render();
$view->listing_obj = $listing_obj;
$view->nav = $nav;
$view->subcats = $subcats;
$view->neighborhood_arr = $neighborhood_arr;
$view->neighborhood_filter = $neighborhood_filter;
$view->items_arr = $items_arr;
$view->attributes_arr = $attributes_arr;
$view->data_filter = $data_filter;
$view->uid = $uid;
$view->cfg = $this->cfg;
$view->cfg_currency = $this->cfg_currency;
$view->cfg_crypto = $this->cfg_crypto;
$view->static_root = $this->cfg['static_root'];
$view->country = $country;

//$view->load_geo = $load_geo;
$view->load_geo = 1;
$geo1 = Arr::get($_GET, "geo1");
$geo2 = Arr::get($_GET, "geo2");
$view->geo1 = $geo1 == "" ? 0 : $geo1;
$view->geo2 = $geo2 == "" ? 0 : $geo2;
$view->cfg = $this->cfg;
$this->template->content = $view;
		
?>