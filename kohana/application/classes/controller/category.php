<?php
define("FILTER_ZIP_RADIUS", 1);
define("FILTER_ZIP", 2);
define("FILTER_CITY_RADIUS", 4);
define("FILTER_CITY", 8);
define("FILTER_PRICE", 16);
define("FILTER_ATTRIBUTE", 32);
define("FILTER_RANGE", 64);
define("FILTER_QUERY", 128);
define("FILTER_NEIGHBORHOOD", 256);
define("FILTER_USERNAME", 512);
define("FILTER_GEO1", 1024);
define("FILTER_GEO2", 2048);
define("FILTER_COUNTRY", 4096);

class Controller_Category extends Controller_System
{
	private $latitude = 0.0;
	private $longitude = 0.0;
	private $circle = 0;
	private $zip_array = array();
	private $zip;
	private $min;
	private $max;
	private $data_arr = array();
	private $rf_arr = array();
	private $neighborhood_arr = array();
	private $uid;
	private $geo1;
	private $geo2;
	private $country;
	
	public function before()
	{
		parent::before();
		$this->session = Session::instance();
		$this->auth = Auth::instance();


		//$this->auth->auto_login();



include Kohana::find_file('libraries', 'Crypto');
include Kohana::find_file('libraries', 'jsonRPCClient');
$array_crypto_cfg = $this->cfg_crypto[$crypto];
$array_crypto_cfg['crypto'] = $crypto;
$array_crypto_cfg['crypto_commission'] = $this->cfg['crypto_commission'];
$crypto_obj = new Crypto($array_crypto_cfg);






		
		if (Request::current()->protocol() == "https")
		{
			Request::current()->redirect('http://' . $this->cfg['www_domain']);
		}	
		/*
		if ($this->auth->logged_in())
		{
			$this->user = $this->auth->get_user();
		}
		else
		{
			Request::current()->redirect('https://' . $this->cfg['www_domain'] . '/account/login');
		}
		*/
		include Kohana::find_file('libraries', 'Sphinxapi');
	}
		

	
	function build_query(&$cl, $filter=0)
	{
		//filtered by zip and radius
		if (($filter & FILTER_ZIP_RADIUS) > 0)
		{
			$cl->SetGeoAnchor('latitude1', 'longitude1', (float) deg2rad($this->latitude), (float) deg2rad($this->longitude));
			$cl->SetFilterFloatRange('@geodist', 0.0, $this->circle);
		}
		
		//filtered by zip
		if (($filter & FILTER_ZIP) > 0)
		{
			//$cl->setFilter("zip", array(12345));
			//$cl->setFilter("zip", array(90001));
			$cl->setFilter("zip", array($this->zip));
		}
		
		//filtered by city and radius
		if (($filter & FILTER_CITY_RADIUS) > 0)
		{
			$cl->SetGeoAnchor('latitude2', 'longitude2', (float) deg2rad($this->latitude), (float) deg2rad($this->longitude));
			$cl->SetFilterFloatRange('@geodist', 0.0, $this->circle);
		}
		
		//filtered by city
		if (($filter & FILTER_CITY) > 0)
		{
			foreach ($this->zip_array as $key => $value)
			{
				$this->zip_array[$key] = $value;
			}
			$cl->setFilter("zip", $this->zip_array);
		}
		
		if (($filter & FILTER_PRICE) > 0)
		{
			if ($this->min <= $this->max)
			{
				$cl->setFilterRange("price", $this->min, $this->max);
			}
		}

		if (($filter & FILTER_ATTRIBUTE) > 0)
		{
			/*
			by default sphinx does OR for the array element in setFilter
			do multiple setFilter for AND operation: condition1 AND condition2 AND condition3
			Region: (attribute)
				US (value)
				Europe (value)
			Edition (attribute)
				DVD
				Blue-ray
			Do: (Region US OR Region Europe) AND (Edition DVD OR Edition Blue-ray) AND xxxxxx
			*/
			foreach ($this->data_arr as $attr)
			{
				$elm = array();
				foreach ($attr as $idx => $value){
					$elm[] = (int)$value;
				}
				$cl->setFilter("attribute_data", $elm);
			}
		}
		
		if (($filter & FILTER_RANGE) > 0)
		{
			foreach ($this->rf_arr as $key => $data)
			{
				$min = isset($data["min"]) ? (int)$data["min"] : 0;
				$max = isset($data["max"]) ? (int)$data["max"] : 4294967295;
				if ($min <= $max)
				{
					//$cl->setFilterRange((string)$key, $min, $max);
					//currently only supports 1 range filter, which is hardcoded in rf1 field in listing table
					$cl->setFilterRange("rf1", $min, $max);
				}
			}
		}
		
		if (($filter & FILTER_NEIGHBORHOOD) > 0)
		{
			//neighborhood1 OR neighborhood2 OR neighborhood3
			$cl->setFilter("neighborhood_id", $this->neighborhood_arr);
		}
		
		if (($filter & FILTER_USERNAME) > 0)
		{
			$cl->setFilter("user_id", array($this->uid));
		}
		
		if (($filter & FILTER_GEO1) > 0)
		{
			
			$cl->setFilter("geo1_id", array($this->geo1));
		}
		
		if (($filter & FILTER_GEO2) > 0)
		{
			$cl->setFilter("geo2_id", array($this->geo2));
		}
		
		if (($filter & FILTER_COUNTRY) > 0)
		{
			$cl->setFilter("country_id", array($this->country));
		}
	}

	function action_index()
	{

		/*
		$a = 1;
		$b = 2;
		$c = 4;
		$d = 8;
		
		$test = 5;

		if (($test & $c) > 0)
		echo "yes";
		else
		echo "no";
		*/
	

	
		//three kind of tables are selected
		//1) normal query through keyword, no "with recursive", through listing table
		//2) query with radius, select from zip table
		//3) with recursive to select all the subcategories of a given cat_id
		
	


		/* $country priority,
		we first check if $country is available via query string
		if not, check if the value is stored in cookie, if it's not then create a new cookie based on maxmind geoip result
		*/
		
		/*
		if (isset($country))
		{
			cookie::set("country", $country, 86400);
		}
		else
		{
			$country = Cookie::get("country", 0);
			if ($country == "none")
			{
				$cc = geoip_country_code_by_name(Request::$client_ip);
				if ($cc == "MY")
				{
					$country = 131;
				}
				else if ($cc == "PH")
				{
					$country = 171;
				}
				else
				{
					$country = 244;
				}
				cookie::set("country", $country, 86400);
			}
			else
			{
				$country = $_GET["country"] = cookie::get("country");
			}
		}

		if ($country == 244)
		{
			unset($geo1);
			unset($geo2);
			cookie::delete("geo1");
			cookie::delete("geo2");
		}
		else
		{
			unset($l);
			unset($r);
			cookie::delete("l");
			cookie::delete("r");
		}
		

		if (isset($s))
		{
			cookie::set("s", $s, 86400);
		}
		else
		{
			$s = Cookie::get("s");
			if ($s == "")
			{
				cookie::set("s", "d", 86400);
			}
			else
			{
				$s = $_GET["s"] = cookie::get("s");
			}
		}

		
		$cookie_l = cookie::get('l');
		if ( ! isset($l) AND $cookie_l != "")
		{	
			$l = $_GET["l"] = $cookie_l;
		}
		if ($cookie_l != $l)
		{
			$_GET["l"] = l;
			cookie::set("l", $l, 86400);
		}
		
		
		
		$cookie_r = cookie::get('r');
		if ( ! isset($r) AND $cookie_r != "")
		{	
			$r = $_GET["r"] = $cookie_r;
		}
		if ($cookie_r != $r)
		{
			$_GET["r"] = $r;
			cookie::set("r", $r, 86400);
		}
		
		
		$cookie_geo1 = cookie::get('geo1');
		if ( ! isset($geo1) AND $cookie_geo1 != "")
		{	
			$geo1 = $_GET["geo1"] = $cookie_geo1;
		}
		if ($cookie_geo1 != $geo1)
		{
			cookie::set("geo1", $geo1, 86400);
		}
		
		
		if ( ! isset($geo2) AND $cookie_geo2 != "")
		{	
			$geo2 = $_GET["geo2"] = $cookie_geo2;
		}
		if ($cookie_geo2 != $geo2)
		{
			$_GET["geo2"] = $geo2;
			cookie::set("geo2", $geo2, 86400);
		}
		*/
		
		
		
		/*
		
		//copy the query string to a new var $get, drop unneccessary params and need push them to $data_arr as we need to use sphinx to process do product filtering
		$get = $_GET;
		unset($get["l"]);
		unset($get["min"]);
		unset($get["max"]);
		unset($get["q"]);
		unset($get["c"]);
		unset($get["s"]);
		unset($get["cid"]);
		unset($get["r"]);
		unset($get["page"]);
		unset($get["uid"]);
		unset($get["ref"]);
		unset($get["country"]);
		unset($get["geo1"]);
		unset($get["geo2"]);
		unset($get["mobile"]);
		
		//for adwords
		unset($get["utm_source"]);
		unset($get["utm_content"]);
		unset($get["utm_medium"]);
		unset($get["utm_campaign"]);

		
		
		foreach ($get as $key => $value)
		{
			if ( ! empty($value))
			{
				//check if the range filter submitted via query string exist in the configs
				//substr($key, 3, -3)) to grep "min", "max"
				$rf = strtolower(substr($key, 3, -3));
				${$rf."min"} = 0;
				//detect neighborhood parameter: eg: neighborhood-1=345&neighborhood-2=5677
				$key_arr = explode("-", $key);
				if (in_array($rf, $this->cfg['range_filters']))
				{
					$minmax = substr($key, -3);
					//${$rf."minmax"} = Arr::get($get, $key);
					$rf_arr[$rf][$minmax] = (int)Arr::get($get, $key);
				}
				elseif ($key_arr[0] == "neighborhood")
				{
					$neighborhood_filter[] = (int)Arr::get($get, $key);
				}
				else
				{
					$data_arr[$key_arr[0]][] = (int)Arr::get($get, $key);
					$data_filter[] = (int)Arr::get($get, $key);
				}
			}
		}
		


		

		if ( ! empty($l))
		{
			if (is_numeric($l))
			{
				$zip = sprintf("%05d", (int)$l);
				$results = DB::query(Database::SELECT, "SELECT city, state, zip FROM zip WHERE zip = '$zip' LIMIT 1")->execute();
				if ($results->count() < 1)
				{
					$error_location = I18n::get("invalid_zip");
					$this->template->meta_location = "";
				}
				else
				{
					$city = $results[0]["city"];
					$state = $results[0]["state"];
					if ($r > 0)
					{
						$centroid = DB::query(Database::SELECT, "SELECT latitude1, longitude1 FROM zip WHERE zip = '$zip' AND active = '1'")->execute();
						$this->latitude = $centroid[0]['latitude1'];
						$this->longitude = $centroid[0]['longitude1'];
						$neighborhood_filter = array();
						$filter_type += FILTER_ZIP_RADIUS;
					}
					else
					{
						$this->zip = ltrim($l, '0');
						$filter_type += FILTER_ZIP;
					}
					$this->template->meta_location = "city {$state_arr[$state]} $zip";
				}
			}
			else
			{
				
				$city_state = explode(",", $l);
				$city = $city_state[0];
				$state = $city_state[1];
				$this->template->meta_location = "$city {$state_arr[$state]}";
				$city = Database::instance()->escape($city);
				$state = Database::instance()->escape($state);
				$results = DB::query(Database::SELECT, "SELECT zip FROM zip WHERE city = $city AND state = $state LIMIT 1")->execute();
				if ($results->count() < 1)
				{

					$error_location = I18n::get("invalid_location");
					$this->template->meta_location = "";
				}
				else
				{
					if ($r > 0)
					{
						$centroid = DB::query(Database::SELECT, "SELECT DISTINCT latitude2, longitude2 FROM zip WHERE city = $city AND state = $state AND active = '1'")->execute();
						$this->latitude = $centroid[0]['latitude2'];
						$this->longitude = $centroid[0]['longitude2'];
						$neighborhood_filter = array();
						$filter_type += FILTER_CITY_RADIUS;
					}
					else
					{
						$zips = DB::query(Database::SELECT, "SELECT zip FROM zip WHERE city = $city AND state = $state AND active = '1'")->execute();
						foreach ($zips as $zip)
						{
							$this->zip_array[] = (int)$zip["zip"];
						}
						$filter_type += FILTER_CITY;
					}
				}
			}
		}
		*/
		
		
		
		
		/*
		
		$this->min = $min == "" ? 0 : (int)$min;
		$this->max = $max == "" ? 4294967295 : (int)$max;
		if ($nav[0]["id"] == 6 OR $nav[0]["id"] == 2)
		{
			if ($min != "" OR $max != "")
			{
				$filter_type += FILTER_PRICE;
			}
		}

		if (count($data_arr) > 0 AND $c == $cid)
		{
			$this->data_arr = $data_arr;
			$filter_type += FILTER_ATTRIBUTE;
		}

		if (count($rf_arr) > 0 AND $c == $cid)
		{
			$this->rf_arr = $rf_arr;
			$filter_type += FILTER_RANGE;
		}

		//No $c == $cid is needed for neighborhood as neighborhood is always the same across all the categories,
		if (count($neighborhood_filter) > 0)
		{
			$this->neighborhood_arr = $neighborhood_filter;
			$filter_type += FILTER_NEIGHBORHOOD;
		}
		$this->circle = (float) $r * 1609.344;
		
		if ($uid != "")
		{
			$escaped_uid = Database::instance()->escape($uid);
			$user_result = DB::query(Database::SELECT, "SELECT id FROM public.user WHERE username = $escaped_uid")->execute();
			$this->uid = (int)$user_result[0]["id"];
			$filter_type += FILTER_USERNAME;
		}
		
		if (isset($geo1) AND $geo1 != "")
		{
			$this->geo1 = $geo1;
			$filter_type += FILTER_GEO1;
		}
		
		if (isset($geo2) AND $geo2 != "")
		{
			$this->geo2 = $geo2;
			$filter_type += FILTER_GEO2;
		}
		
		if (isset($country))
		{
			$this->country = $country;
			$filter_type += FILTER_COUNTRY;
		}
		*/
		
		
		
		//$listing_result = DB::query(Database::SELECT, "SELECT l.listing->'$user_id'->>'price' as price, l.id, l.title, l.img_count, l.uid, l.cid, l.object_type_id, cr.iso4217 AS currency FROM listing l LEFT JOIN currency cr ON json_int(listing, '$user_id.currency_id') = cr.id WHERE cast(listing->'$user_id'->>'status' as integer) IN($status_arr) AND json_user_exist(listing, '$user_id') = '1' ORDER BY id DESC LIMIT $limit OFFSET $offset")->execute();
		
		/*
		
		$listing_result = DB::query(Database::SELECT, "SELECT l.listing->'$user_id'->>'price' as price, l.id, l.title, l.img_count, l.uid, l.cid, l.object_type_id, cr.iso4217 AS currency FROM listing l LEFT JOIN currency cr ON json_int(listing, '$user_id.currency_id') = cr.id WHERE cast(listing->'$user_id'->>'status' as integer) IN($status_arr) AND json_user_exist(listing, '$user_id') = '1' ORDER BY id DESC LIMIT $limit OFFSET $offset")->execute();
		*/
	
		
		/*
		if ($s == "b" && ! empty($q))
		{
			$cl->SetSortMode(SPH_SORT_RELEVANCE);
		}
		else if ($s == "l")
		{
			$cl->SetSortMode(SPH_SORT_ATTR_ASC, 'price');
		}
		elseif ($s == "h")
		{
			$cl->SetSortMode(SPH_SORT_ATTR_DESC, 'price');
		}
		else
		{
			$cl->SetSortMode(SPH_SORT_ATTR_DESC, 'created');
		}
		*/
		
		

		include( __DIR__ . '/../../views/fullsite/search.php');


	}
}
