<?php
class Controller_My_Listing extends Controller_System
{
	private $dreamobjects;
	private $bucket_name;
	
	public function before()
	{
		parent::before();
		
	
		
		$this->ua = $_SERVER['HTTP_USER_AGENT'];
		$this->session = Session::instance();
		$this->auth = Auth::instance();
		$this->auth->auto_login();
		$this->cfg = Kohana::$config->load('general.default');
		$this->cfg_currency = Kohana::$config->load('general.currency');
		if ($this->auth->logged_in())
		{
			$this->user = $this->auth->get_user();
		}
		else
		{
			if ($this->request->action() == "details")
            //if ($this->request->action == "details" AND preg_match('#facebookexternalhit#', $this->ua))
            {
            }
            else
            {
				Request::current()->redirect('https://' . $this->cfg['www_domain'] . '/account/auth');
            }
	
        }

		
        require_once  __DIR__ . "/../../../vendor/aws-sdk-php/sdk-1.6.2/sdk.class.php"; 
        require_once  __DIR__ . "/../../../vendor/aws-sdk-php/sdk-1.6.2/extensions/s3streamwrapper.class.php"; 

		$options = array("key" => $this->cfg["s3_key"], "secret" => $this->cfg["s3_secret_key"]);
		$this->s3_objects = new AmazonS3($options);
		$this->s3_objects->set_hostname($this->cfg["s3_hostname"]);
		$this->s3_objects->allow_hostname_override(false);
		$this->s3_objects->enable_path_style();
		S3StreamWrapper::register($this->s3_objects);
	}
	
	public function object_to_array($object)
    {
        if (! is_object($object) AND ! is_array($object))
        {
            return $object;
        }
        if (is_object($object))
        {
            $object = get_object_vars($object);
        }
        return array_map('self::object_to_array', $object);
    }
	
	function format_text($value)
	{
		$list = array("\"" => "&quot;", "'" => "&apos;", "\\" => "&bsol;");
		$array_search = array_keys($list);
		$array_search = array_map('utf8_encode', $array_search);
		$array_replace = array_values($list);
		/*
		$array_search =
		Array
		(
			[0] => "
			[1] => '
			[2] => \
		)

		$array_replace =
		Array
		(
			[0] => &quot;
			[1] => &apos;
			[2] => &bsol;
		)
		*/
		$array_br = array('<br>', '<br/>', '<br />');
		$array_nl = array('\n', '\n', '\n');

		//$br2nl = str_replace($array_br, $array_nl, trim($value));
		$br2nl = str_replace($array_br, $array_nl, $value);
		$nl2br = str_replace('\n', '<br>', strip_tags($br2nl));
		$replaced = str_replace($array_search, $array_replace, $nl2br);
		return $replaced;
	}
	
	function get_ip()
	{
		/*	
		if (!empty($_SERVER['HTTP_CLIENT_IP']))   //check ip from share internet
		{
		  $ip=$_SERVER['HTTP_CLIENT_IP'];
		}
		elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR']))   //to check ip is pass from proxy
		{
		  $ip=$_SERVER['HTTP_X_FORWARDED_FOR'];
		}
		else
		{
		  $ip=$_SERVER['REMOTE_ADDR'];
		}
		*/
		$ip=$_SERVER['REMOTE_ADDR'];
		return $ip;
	}
	
	
	function get_uuid()
	{
		$uuid = Session::instance()->get("uuid");
		if ( ! isset($uuid))
		{
			$uuid = uniqid("uuid_", true);
			Session::instance()->set("uuid", $uuid);
		}
		return $uuid;
	}
	
	
	//to prevent multiple form submission by clicking the refresh button, comparing $_POST['uid'] and $_SESSION['uid'], destroy session uid upon each genuine submission
	
	function is_duplicate(){
		$session_uuid = Session::instance()->get("uuid");
		$post_uuid = Arr::get($_POST, "uuid");
		if ( ! isset($session_uuid))
		{
			return true;
		}
		if ( ! isset($post_uuid))
		{
			return true;
		}
		//multiple submission detected!!
		if ($post_uuid !== $session_uuid)
		{
			return true;
		}
		return false;
	}
	
	function process_zip(Validation $post = NULL)
	{
		$zip = sprintf("%05d", (int)Arr::get($_POST, 'zip'));
		$result = DB::query(Database::SELECT, "SELECT COUNT(*) AS count FROM zip WHERE zip = :zip")
		->param(':zip', $zip)
		->execute();
		if ($post == NULL)
			return;
		if (array_key_exists('zip', $post->errors()))
			return;
		if ($result[0]['count'] < 1)
		{
			$post->error('zip', 'zip_not_found');
		}
	}
	
	
	function process_upload($listing_id, $uid, &$img_error, &$counter)
	{
		$user_id = $this->auth->get_user()->id;		
		
	
		$upload_path = "/tmp/plupload";
		$uploader_count = (int)Arr::get($_POST, "uploader_count");
		if ($uploader_count > 0)
		{
			$img_count = 1;
		}
		for ($i = 0; $i < $uploader_count; $i++)
		{
			if ($counter > 4)
			{
				break;
			}
			$upload_status = $_POST["uploader_" . $i . "_status"];
			$upload_filename = $_POST["uploader_" . $i . "_name"];
			if (isset($upload_status))
			{
				$counter++;

				$img_path = implode("/", str_split($uid, 2));
				$img_path = substr($img_path, 0, 8);

				$tmp_img = "$upload_path/$upload_filename";
				list($width, $height, $type, $attr) = getimagesize($tmp_img);
				if ($width > 0)
				{
					
					$img_large = "/tmp/{$listing_id}_0{$counter}_{$this->cfg['size_large']}_{$uid}.jpg";
					if ($width > $this->cfg['width_large'] OR $height > $this->cfg['height_large'])
					{
						Image::factory($tmp_img)->resize($this->cfg['width_large'], $this->cfg['height_large'])->save($img_large, $this->cfg['image_quality']);
						list($img_width, $height, $type, $attr) = getimagesize($img_large);
						if ($img_width < 1)
						{
							$img_error = 1;
							throw new Kohana_Exception("user_id: $user_id img_large: $img_large width < 1");
						}
					}
					else
					{
						Image::factory($tmp_img)->save($img_large, $this->cfg['image_quality']);
					}
					
					$img_medium = "/tmp/{$listing_id}_0{$counter}_{$this->cfg['size_medium']}_{$uid}.jpg";
					//if ($width > $this->cfg['width_medium'])
					if ($width > $this->cfg['width_medium'] OR $height > $this->cfg['height_medium'])
					{
						//Image::factory($tmp_img)->resize($this->cfg['width_medium'])->save($img_medium, $this->cfg['image_quality']);
						Image::factory($tmp_img)->resize($this->cfg['width_medium'], $this->cfg['height_medium'])->save($img_medium, $this->cfg['image_quality']);
						list($img_width, $height, $type, $attr) = getimagesize($img_medium);
						if ($img_width < 1)
						{
							$img_error = 1;
							throw new Kohana_Exception("user_id: $user_id img_medium: $img_medium width < 1");
						}
						
					}
					else
					{
						Image::factory($tmp_img)->save($img_medium, $this->cfg['image_quality']);
					}
					
					$img_small = "/tmp/{$listing_id}_0{$counter}_{$this->cfg['size_small']}_{$uid}.jpg";
					if ($width > $this->cfg['width_small'] OR $height > $this->cfg['height_small'])
					{
						Image::factory($tmp_img)->resize($this->cfg['width_small'], $this->cfg['height_small'])->save($img_small, $this->cfg['image_quality']);
						list($img_width, $height, $type, $attr) = getimagesize($img_small);
						if ($img_width < 1)
						{
							$img_error = 1;
							throw new Kohana_Exception("user_id: $user_id img_small: $img_small width < 1");
						}
					}
					else
					{
						Image::factory($tmp_img)->save($img_small, $this->cfg['image_quality']);
					}
				

					$dreamobjects_response = $this->s3_objects->create_object($this->cfg["bucket_name"], "{$this->cfg['size_large']}/$img_path/{$uid}_$counter.jpg", array(
						'fileUpload' => $img_large,
						'acl'         => AmazonS3::ACL_PUBLIC,
					));
					if( ! $dreamobjects_response->isOk())
					{
						//var_dump($response);
						//echo "error: create_object error.";
						$img_error = 1;
						throw new Kohana_Exception("user_id: $user_id error creating object img_large: $img_large");
					}
					
					
					$dreamobjects_response = $this->s3_objects->create_object($this->cfg["bucket_name"], "{$this->cfg['size_medium']}/$img_path/{$uid}_$counter.jpg", array(
						'fileUpload' => $img_medium,
						'acl'         => AmazonS3::ACL_PUBLIC,
					));
					if( ! $dreamobjects_response->isOk())
					{
						$img_error = 1;
						throw new Kohana_Exception("user_id: $user_id error creating object img_medium: $img_medium");
					}
				
				
					$dreamobjects_response = $this->s3_objects->create_object($this->cfg["bucket_name"], "{$this->cfg['size_small']}/$img_path/{$uid}_$counter.jpg", array(
						'fileUpload' => $img_small,
						'acl'         => AmazonS3::ACL_PUBLIC,
					));
					if( ! $dreamobjects_response->isOk())
					{
						$img_error = 1;
						throw new Kohana_Exception("user_id: $user_id error creating object img_small: $img_small");
					}
			
				

				}
				else
				{
					$img_error = 1;
					throw new Kohana_Exception("user_id: $user_id original image: $tmp_img width < 1'");
					
				}
			}
		}

	}
	
	function browse_categories()
	{
		$user_id = $this->auth->get_user()->id;
		
		$action = $this->request->action;
		$listing_id = (int)Arr::get($_GET, "listing_id");
		//previously selected url:
		$sid = (int)Arr::get($_GET, "sid", 0);
		$barcode = Arr::get($_GET, "barcode");
		
		/*
		id = main category id: user must select one of them:
		For Sale | Community | Housing | Jobs | Pets | Services
		default to 6 for sale
		*/
		$id = (int)Arr::get($_GET, 'id');

		$view = View::factory(TEMPLATE . '/my/new_listing1');
		
		
		$view->user_shipping_method_obj = DB::query(Database::SELECT, "SELECT name FROM user_shipping_method WHERE user_id = :user_id ORDER BY name")
		->param(':user_id', $user_id)
		->execute();
		
		$user_payment_option_obj = DB::query(Database::SELECT, "SELECT option FROM user_payment_option WHERE user_id = :user_id")
		->param(':user_id', $user_id)
		->execute();
		$view->array_payment_option = self::object_to_array(json_decode($user_payment_option_obj[0]['option']));
		
		
		if ($barcode != "")
		{
			$product_obj = DB::query(Database::SELECT, "SELECT l.id, l.uid, l.title, l.object_type_id from listing l WHERE info->>'gtin' = :barcode AND l.status = '1' LIMIT 1")
			->param(':barcode', $barcode)
			->execute();
			if (count($product_obj) > 0)
			{
				$product_id = $product_obj[0]['id'];
				/*
				select * from listing where id = 380583 and json_user_exist(listing, 'listing.u91') = 1;
				//user record exists in listing, return 1
				
				select * from listing where id = 380583 and json_user_exist(listing, 'listing.u92') = 1;
				//user record doesn't exist in listing, return 0
				*/
				$listing_obj = DB::query(Database::SELECT, "SELECT ld.id FROM listing_data ld LEFT JOIN listing l ON ld.listing_id = l.id WHERE l.id = :product_id AND (ld.listing->'user_id')::integer = :user_id")
				->param(':product_id', $product_id)
				->param(':user_id', $user_id)
				->execute();
			}
			$view->product_obj = $product_obj;
			$view->listing_obj = $listing_obj;
		}
		$view->cfg = $this->cfg;
				
		//new ad
		if ($listing_id == 0)
		{

			$view->categories = DB::query(Database::SELECT, "SELECT id, name, has_child FROM categories_entity WHERE parent_id = :id AND active = '1' ORDER BY name")
			->param(':id', $id)
			->execute();

			$cats_result = array();
			$cats_result[] = DB::query(Database::SELECT, "SELECT id, name, has_child FROM categories_entity WHERE parent_id = '1' AND active = '1' ORDER BY name")->execute();
			if ($sid != 0)	
			{
				//user changed the categories eg: from Housing to For Sales -> Computer, show all subcategories
				$nav = DB::query(Database::SELECT, "WITH RECURSIVE subcategories AS (
		SELECT id, parent_id, has_child, has_price, has_img, node_path FROM categories_entity WHERE id = :sid AND has_child = '0' AND active = '1' UNION ALL SELECT c.id, c.parent_id, c.has_child, c.has_price, c.has_img, c.node_path FROM categories_entity c JOIN subcategories sc ON c.id = sc.parent_id) SELECT id, name, has_child, has_price, has_img, node_path FROM categories_entity c WHERE c.id IN (SELECT id FROM subcategories GROUP BY id) ORDER BY parent_id")
		->param(':sid', $sid)
		->execute();

				if (count($nav) > 0)
				{
					$node_path = $nav[count($nav) - 1]["node_path"];
					$cat_arr = explode(".", $node_path);
					
					for ($i = 0; $i < count($cat_arr) - 1; $i++)
					{
						$cats_result[] = DB::query(Database::SELECT, "SELECT id, name, has_child FROM categories_entity WHERE parent_id = :cat_arr AND active = '1' ORDER BY name")
						->param(':cat_arr', $cat_arr[$i])
						->execute();
					}
					$view->cat_arr = $cat_arr;
					
				}
				else
				{
					Request::current()->redirect('/');
				}
			}
			$view->cats_result = $cats_result;
			$view->action = 'new';
			$view->url = "";
			$view->header = I18n::get('new_listing');
			$view->id = $id;
		}
		
		
		//edit ad
		else
		{

			if ($id == "")
			{
				
				$nav = DB::query(Database::SELECT, "WITH RECURSIVE subcategories AS (
		SELECT id, parent_id, has_child, has_price, has_img, node_path FROM categories_entity WHERE id = :sid AND has_child = '0' AND active = '1' UNION ALL SELECT c.id, c.parent_id, c.has_child, c.has_price, c.has_img, c.node_path FROM categories_entity c JOIN subcategories sc ON c.id = sc.parent_id) SELECT id, name, has_child, has_price, has_img, node_path FROM categories_entity c WHERE c.id IN (SELECT id FROM subcategories GROUP BY id) ORDER BY parent_id")
		->param(':sid', $sid)
		->execute();
		
				if (count($nav) > 0)
				{
					$node_path = $nav[count($nav) - 1]["node_path"];
					$cat_arr = explode(".", $node_path);
					$cats_result = array();
					for ($i = 0; $i < count($cat_arr) - 1; $i++)
					{
						$cats_result[] = DB::query(Database::SELECT, "SELECT id, name, has_child FROM categories_entity WHERE parent_id = :cat_arr AND active = '1' ORDER BY name")
						->param(':cat_arr', $cat_arr[$i])
						->execute();
					}
					$view->cats_result = $cats_result;
					$view->cat_arr = $cat_arr;
					$view->id = $cat_arr[0];
				}
				else
				{
					Request::current()->redirect('/');
				}
			}
			else
			{
				
				//user changed parent category: eg: from house to for sale
				if ($id != 1 AND $id != 2 AND $id != 4 AND $id != 6 AND $id != 7 AND $id != 7 AND $id != 10)
				{
					$id = 6;
				}
				$view->categories = DB::query(Database::SELECT, "SELECT id, name, has_child FROM categories_entity WHERE parent_id = :id AND active = '1' ORDER BY name")
				->param(':id', $id)
				->execute();
			}

			$view->action = 'edit';
			$view->cfg = $this->cfg;
			$view->url = "&s=1&sid=$sid&listing_id=$listing_id";
			$view->header = I18n::get('edit_ad');
		}
		
		$view->listing_id = $listing_id;
		$view->sid = $sid;
		$this->template->title = i18n::get('new_listing') . " - " . i18n::get('select_category');
		$this->template->content = $view;
	}
	
	
	function load_form($type, $id)
	{
		$_SESSION["u"] = $user_id = $this->auth->get_user()->id;
		$_SESSION["h"] = md5($this->auth->get_user()->id . $this->cfg["key"]);
		$view = View::factory(TEMPLATE . '/my/new_listing2');
		

		//$id here refering to id in listing_data table not listing table
		/*
		$type 1: new
		$type 2: edit
		*/

		if ($_POST['p3'])
		{
			$selling_option1 = $_POST['p3'] == 'on' ? 1 : 0;
			$selling_option2 = $_POST['p4'] == 'on' ? 1 : 0;
			$selling_option3 = $_POST['p5'] == 'on' ? 1 : 0;
		}
		
		$gtin_result = array();
		if ($type == 1)
		{
			//for some reason, jquery mobile doesn't process $id parameter correctly, sometimes it returns 0, so we directly fetch user-selected $catX value from $_POST
			for ($i=1; $i<10; $i++)
			{
				$current_cat = Arr::get($_POST, "cat$i");
				if (isset($current_cat))
				{
					$id = $current_cat;
				}
			}
			$list_id = 0;
			$cid = (int)$id;
			//print "cid: ".$cid;
			//check if category is valid
			$code = Arr::get($_GET, "code");

			
			if ($code == '')
			{

				$cid_result = DB::query(Database::SELECT, "SELECT has_img, has_price, has_item_condition, has_shipping, has_selling_option, has_quantity, node_path FROM categories_entity WHERE id = :cid AND has_child = '0' AND active = '1'")
				->param(':cid', $cid)
				->execute();
				if (count($cid_result) > 0)
				{
					$listing_result = array();
					$listing_result[0] = array("zip" => "", "neighborhood" => "", "title" => "", "price" => "", "description" => "", "country" => "", "geo1" => 0, "geo2" => 0, "quantity" => 1);
				}
				else
				{
					throw new Kohana_Exception('site_error');
					$ok = 0;
				}
				$object_type_id = 1;


				
			}
			else
			{
				$view->gtin_result = $gtin_result = DB::query(Database::SELECT, "SELECT l.title, l.cid FROM listing l WHERE l.uid = :code AND l.status = '1'")
				->param(':code', $code)
				->execute();
				if (count($gtin_result) < 1)
				{
					throw new Kohana_Exception('site_error');
				}
				else
				{
					$cid = $gtin_result[0]["cid"];
				}
				$object_type_id = 2;
			}
			$view->header = I18n::get('new_listing');

			
			$preference_obj = DB::query(Database::SELECT, "SELECT (each(preference)).key, (each(preference)).value FROM user_preference WHERE user_id = :user_id")
			->param(':user_id', $user_id)
			->execute();
			$db = array();
			foreach($preference_obj as $record)
			{
				//$view->set($record['key'], $record['value']);
				$db[$record['key']] = $record['value'];
			}

			$view->currency_code = $db['currency_code'];
			$view->weight_unit = $db['weight_unit'];
			$view->third_party_url = Arr::get($_POST, 'third_party_url');
			$view->selling_option1 = isset($selling_option1) ? $selling_option1 : (int)(($db['selling_option'] & 1) > 0);
			$view->selling_option2 = isset($selling_option2) ? $selling_option2 : (int)(($db['selling_option'] & 2) > 0);
			$view->selling_option3 = isset($selling_option3) ? $selling_option3 : (int)(($db['selling_option'] & 4) > 0);
			$view->country_id = Arr::get($_POST, 'country', $db['country_id']);
			$view->geo1_id = Arr::get($_POST, 'geo1', $db['geo1_id']);
			$view->geo2_id = Arr::get($_POST, 'geo2', $db['geo2_id']);
			$view->neighborhood_id = Arr::get($_POST, 'neighborhood', $db['neighborhood_id']);
			$view->zip = Arr::get($_POST, 'zip', $db['zip']);
			$view->location = Arr::get($_POST, 'location', $db['location']);
			$view->idd = Arr::get($_POST, 'idd');
		
			$view->action = 'new';
			$view->object_type_id = $object_type_id;

		}
		elseif ($type == 2)
		{
			
			$listing_data_id = (int)$id;
			//check whether listing type is user posted (non-gtin) or system generated (gtin)
			$view->gtin_result = $gtin_result = DB::query(Database::SELECT, "SELECT l.uid, l.title, l.cid FROM listing_data ld LEFT JOIN listing l ON ld.listing_id = l.id WHERE ld.id = :listing_data_id AND object_type_id = '2'")
			->param(':listing_data_id', $listing_data_id)
			->execute();
			if (count($gtin_result) == 0)
			{
				$code = '';
			}
			else
			{
				$code = $gtin_result[0]['uid'];
				
			}
			
			$listing_result = DB::query(Database::SELECT, "SELECT l.id, l.img_count, l.uid, l.title, l.description, l.cid, z.zip, ld.listing->'selling_option' as selling_option, ld.listing->'third_party_url' as third_party_url, ld.listing->'country_id' as country_id, l.object_type_id, ld.listing->'item_condition_id' as item_condition_id, ld.listing->'condition_description' as condition_description, ld.listing->'price' as price,  ld.listing->'quantity' AS quantity, ld.listing->'currency_code' as currency_code, ld.listing->'item_weight' AS item_weight, ld.listing->'weight_unit' as weight_unit, ld.listing->'neighborhood_id' as neighborhood_id, ld.listing->'geo1_id' as geo1_id, ld.listing->'geo2_id' as geo2_id, ld.listing->'location' as location, ld.listing->'idd' as idd FROM listing_data ld LEFT JOIN listing l ON ld.listing_id = l.id LEFT JOIN zip z ON (ld.listing->'zip_id')::integer = z.id WHERE ld.status = '1' AND ld.id = :listing_data_id AND (ld.listing->'user_id')::integer = :user_id")
			->param(':listing_data_id', $listing_data_id)
			->param(':user_id', $user_id)
			->execute();
					
			if (count($listing_result) > 0)
			{
				//for some reason, jquery mobile doesn't process $id parameter correctly, sometimes it returns 0, so we directly fetch user-selected $catX value from $_POST
				for ($i=1; $i<10; $i++)
				{
					$current_cat = Arr::get($_POST, "cat$i");
					if (isset($current_cat))
					{
						$post_cid = $current_cat;
					}
				}
				
				
				//triggered if users change the existing category: eg: from Real estate to for sale -> antique, need to hide all the existing listing_data/string
				if ($post_cid > 0)
				{
					$cid_result = DB::query(Database::SELECT, "SELECT has_img, has_price, has_item_condition, has_shipping, has_selling_option, has_quantity, node_path FROM categories_entity WHERE id = :post_cid AND has_child = '0' AND active = '1'")
					->param(':post_cid', $post_cid)
					->execute();
					if (count($cid_result) > 0)
					{
						$cid = (int)$post_cid;
					}
					else
					{
						$cid = $listing_result[0]["cid"];
					}
				}
				else
				{
					$cid = $listing_result[0]["cid"];
					$listing_id = $listing_result[0]['id'];
					$listing_data_arr = array();
					$listing_data_result = DB::query(Database::SELECT, "SELECT cea.attribute_id AS ca_id, CAST(lds.ca_value AS varchar) FROM categories_attribute ca LEFT JOIN categories_entity_attribute cea ON ca.id = cea.attribute_id LEFT JOIN listing l ON cea.entity_id = l.cid LEFT JOIN listing_data_system lds ON l.id = lds.listing_id AND cea.attribute_id = lds.ca_id WHERE l.id = :listing_id AND cea.entity_id = :cid AND ca.data_type = 0 UNION ALL SELECT cea.attribute_id AS ca_id, ldu.ca_value FROM categories_attribute ca LEFT JOIN categories_entity_attribute cea ON ca.id = cea.attribute_id LEFT JOIN listing l ON cea.entity_id = l.cid LEFT JOIN listing_data_user ldu ON l.id = ldu.listing_id AND cea.attribute_id = ldu.ca_id WHERE l.id = :listing_id AND cea.entity_id = :cid AND ca.data_type > 0")
					->param(':listing_id', $listing_id)
					->param(':cid', $cid)
					->execute();
					foreach ($listing_data_result as $listing_data)
					{
						$listing_data_arr[$listing_data["ca_id"]][] = $listing_data["ca_value"]; 
					}
					$view->listing_data_arr = $listing_data_arr;
				}
				
				
				$cid_result = DB::query(Database::SELECT, "SELECT has_img, has_price, has_item_condition, has_shipping, has_selling_option, has_quantity, node_path FROM categories_entity WHERE id = :cid AND has_child = '0' AND active = '1'")
				->param(':cid', $cid)
				->execute();
				
			}
			else
			{
				throw new Kohana_Exception('site_error');
			}
			
			$view->digital_content_obj = DB::query(Database::SELECT, "SELECT id, content, used, order_id FROM digital_content WHERE listing_data_id = :listing_data_id")
			->param(':listing_data_id', $listing_data_id)
			->execute();
			$view->header = I18n::get('edit_listing');
			$view->third_party_url = Arr::get($_POST, 'third_party_url', $listing_result[0]['third_party_url']);
			$view->selling_option1 = isset($selling_option1) ? $selling_option1 : (int)(($listing_result[0]['selling_option'] & 1) > 0);
			$view->selling_option2 = isset($selling_option2) ? $selling_option2 : (int)(($listing_result[0]['selling_option'] & 2) > 0);
			$view->selling_option3 = isset($selling_option3) ? $selling_option3 : (int)(($listing_result[0]['selling_option'] & 4) > 0);
			$view->country_id = Arr::get($_POST, 'country', $listing_result[0]['country_id']);
			$view->geo1_id = Arr::get($_POST, 'geo1', $listing_result[0]['geo1_id']);
			$view->geo2_id = Arr::get($_POST, 'geo2', $listing_result[0]['geo2_id']);
			$view->neighborhood_id = Arr::get($_POST, 'neighborhood', $listing_result[0]['neighborhood_id']);
			$view->zip = $zip = sprintf("%05d", (int)Arr::get($_POST, 'zip', $listing_result[0]['zip']));
			$view->location = Arr::get($_POST, 'location', $listing_result[0]['location']);
			$view->idd = Arr::get($_POST, 'idd', $listing_result[0]['idd']);
			$view->currency_code = $listing_result[0]['currency_code'];
			$view->weight_unit = $listing_result[0]['weight_unit'];
			$view->action = 'edit';
			$view->object_type_id = $listing_result[0]['object_type_id'];
			$view->condition_description = $listing_result[0]['condition_description'];
			$view->item_condition_id = $listing_result[0]['item_condition_id'];
		}
		$view->user_shipping_method_obj = DB::query(Database::SELECT, "SELECT name FROM user_shipping_method WHERE user_id = :user_id ORDER BY name")
		->param(':user_id', $user_id)
		->execute();
		
		$user_payment_option_obj = DB::query(Database::SELECT, "SELECT option FROM user_payment_option WHERE user_id = :user_id")
		->param(':user_id', $user_id)
		->execute();
		$view->array_payment_option = self::object_to_array(json_decode($user_payment_option_obj[0]['option']));

	
		
		
		/*
		sequence so that hidden text field 'other' from the select menu
		can be displayed right after the select menu, see 'gift certificate' category
		print "<br>cid: ".$cid;
		*/

	
	
		
		$view->country_obj = DB::query(Database::SELECT, "SELECT id, name FROM country ORDER BY name")->execute();
	
		if ($view->zip == "00000") $view->zip = "";
		$view->cas = DB::query(Database::SELECT, "SELECT ca1.*, ca1.id AS sequence FROM categories_entity_attribute cea, categories_attribute ca1 WHERE cea.entity_id = :cid AND cea.attribute_id = ca1.id AND ca1.parent_id = 0 union select ca2.*, ca2.parent_id AS sequence FROM categories_entity_attribute cea, categories_attribute ca2 WHERE cea.entity_id = :cid AND cea.attribute_id = ca2.id AND ca2.parent_id <> 0 ORDER BY sequence, parent_id, name")
		->param(':cid', $cid)
		->execute();
		$view->ess = DB::query(Database::SELECT, "SELECT cea.attribute_id, cad.id, cad.value FROM categories_entity_attribute cea, categories_attribute_data cad WHERE cea.entity_id = :cid AND cea.attribute_id = cad.attribute_id ORDER BY attribute_id, CASE WHEN is_numeric(cad.value) THEN lpad(cad.value, 255 - Length(cad.value), '0') ELSE cad.value END")
		->param(':cid', $cid)
		->execute();

		if ($view->zip != "")
		{
			$view->neighborhoods = DB::query(Database::SELECT, "SELECT n.id, n.name FROM 
neighborhood n INNER JOIN zip z ON n.city = z.city AND n.state = z.state WHERE z.zip = :zip ORDER BY n.name")
->param(':zip', $zip)
->execute();
		}
		
		$view->nav = DB::query(Database::SELECT, "WITH RECURSIVE subcategories AS ( SELECT id, parent_id FROM categories_entity WHERE id = :cid UNION ALL SELECT c.id, c.parent_id FROM categories_entity c JOIN subcategories sc ON c.id = sc.parent_id) SELECT name FROM categories_entity c WHERE c.id IN (SELECT id FROM subcategories GROUP BY id) ORDER BY parent_id")
		->param(':cid', $cid)
		->execute();
		

		if (count($gtin_result) > 0)
		{
			$view->has_price = 1;
			$view->has_item_condition = 1;
			$view->has_quantity = 1;
		}
		else
		{
			$view->has_price = $cid_result[0]['has_price'];
			$view->has_item_condition = $cid_result[0]['has_item_condition'];
			$view->has_quantity = $cid_result[0]['has_quantity'];
		}
		$view->has_shipping = $cid_result[0]['has_shipping'];
		$view->has_selling_option = $cid_result[0]['has_selling_option'];
		
		$array_node_path = explode('.', $cid_result[0]['node_path']);
		$view->is_digital = in_array(7511, $array_node_path) ? 1 : 0;
		//$view->item_condition_obj = DB::query(Database::SELECT, "SELECT ic.* from category_item_condition cic LEFT JOIN item_condition ic ON cic.item_condition_id = ic.id WHERE cic.category_id = '$cid'")->execute();

		

		$view->code = $code;


		$view->item_weight = Arr::get($_POST, 'item_weight', $listing_result[0]['item_weight']); 
		$view->weight_unit = (int) Arr::get($_POST, 'weight_unit', $listing_result[0]['weight_unit']); 
		$view->price = Arr::get($_POST, 'price', $listing_result[0]['price']);
		$view->quantity = Arr::get($_POST, 'quantity', $listing_result[0]['quantity']);
		$view->title = Arr::get($_POST, "title", $listing_result[0]["title"]);
		$view->description = Arr::get($_POST, 'description', $listing_result[0]['description']);

		$view->uid = isset($listing_result[0]["uid"]) ? $listing_result[0]["uid"] : "";
		$view->img_count = isset($listing_result[0]["img_count"]) ? $listing_result[0]["img_count"] : 0;
		$view->has_img = $cid_result[0]['has_img'];
		$view->uuid = self::get_uuid();
		$view->t = $type;
		$view->id = $listing_data_id;
		$view->cid = $cid;
		$view->cfg = $this->cfg;

		
		$array_payment_method_name = array();
		$array_payment_method_name['cash_on_delivery'] = I18n::get('cash_on_delivery');
		$array_payment_method_name['bank_deposit'] = I18n::get('bank_deposit');
		$array_payment_method_name['money_order'] = I18n::get('money_order');
		$array_payment_method_name['cashier_check'] = I18n::get('cashier_check');
		$array_payment_method_name['personal_check'] = I18n::get('personal_check');
		foreach ($this->cfg_crypto as $symbol => $record)
		{
			$array_payment_method_name[$symbol] = ucfirst($record['name']);
		}
		$view->array_payment_method_name = $array_payment_method_name;
		return $view;
		//$this->template->content = $view;
	}
	
	
	
	
	function action_block()
	{
		$cb = Arr::get($_POST, "cb");
		$listing_id = (int)Arr::get($_GET, "listing_id");
		$user_id = (int)$this->auth->get_user()->id;
		$count = 0;
		$msg = "";
		$t = Arr::get($_GET, "t", 1);
		if ((is_array($cb) AND count($cb) > 0) OR $listing_id > 0)
		{
			if ($listing_id > 0)
			{
				$cb = array($listing_id);
			}
			
			foreach ($cb as $listing_id)
			{
				if ($user_id == 19)
				{
					$listing_obj = DB::query(Database::SELECT, "SELECT id, object_type_id FROM listing WHERE id = :listing_id")
					->param(':listing_id', $listing_id)
					->execute();
					if (count($listing_obj) > 0)
					{
						$listing_id = $listing_obj[0]['id'];
						$object_type_id = $listing_obj[0]['object_type_id'];
						$array_status = array();
						$array_status[$user_id]['status'] = 4;
						$json_status = json_encode($array_status);
						$count++;
						$result = DB::query(Database::UPDATE, "UPDATE listing SET listing = json_add_update(listing, :json_status) WHERE id = :listing_id")
						->param(':json_status', $json_status)
						->param(':listing_id', $listing_id)
						->execute();
						if ( ! $result)
						{
							throw new Kohana_Exception('site_error');
						}
					}
					$result = DB::query(Database::UPDATE, "UPDATE listing SET listing_status = 4 WHERE object_id = :listing_id AND user_id = :user_id")
					->param(':listing_id', $listing_id)
					->param(':user_id', $user_id)
					->execute('sphinx');
					if ( ! $result)
					{
						throw new Kohana_Exception('site_error');
					}
					
				}
			}
			if ($count > 0)
			{
				if ($count == 1)
				{
					$msg = I18n::get("listing.listing_deleted_single");
				}
				else
				{
					$msg = sprintf(I18n::get("listing.listing_deleted_multiple"), $count);
				}
			}
		}
		if ($t == 1)
		{
			self::action_index(1, $msg);
		}
		else
		{
			self::action_repost($msg, 1);
		}
	}
	
	function action_unblock()
	{
		$cb = Arr::get($_POST, "cb");
		$listing_id = (int)Arr::get($_GET, "listing_id");
		$user_id = (int)$this->auth->get_user()->id;
		$count = 0;
		$msg = "";
		$t = Arr::get($_GET, "t", 1);
		if ((is_array($cb) AND count($cb) > 0) OR $listing_id > 0)
		{
			if ($listing_id > 0)
			{
				$cb = array($listing_id);
			}
			
			foreach ($cb as $listing_id)
			{
				if ($user_id == 19)
				{
					$listing_obj = DB::query(Database::SELECT, "SELECT id, object_type_id FROM listing WHERE id = :listing_id")
					->param(':listing_id', $listing_id)
					->execute();
					if (count($listing_obj) > 0)
					{
						$listing_id = $listing_obj[0]['id'];
						$object_type_id = $listing_obj[0]['object_type_id'];
						$array_status = array();
						$array_status[$user_id]['status'] = 1;
						$json_status = json_encode($array_status);
						$count++;
						$result = DB::query(Database::UPDATE, "UPDATE listing SET listing = json_add_update(listing, :json_status) WHERE id = :listing_id")
						->param(':json_status', $json_status)
						->param(':listing_id', $listing_id)
						->execute();
						if ( ! $result)
						{
							throw new Kohana_Exception('site_error');
						}
					
						$result = DB::query(Database::UPDATE, "UPDATE listing SET listing_status = 2 WHERE object_id = :listing_id AND user_id = :user_id")
						->param(':listing_id', $listing_id)
						->param(':user_id', $user_id)
						->execute('sphinx');
						if ( ! $result)
						{
							throw new Kohana_Exception('site_error');
						}
						
					}
				}
			}
			if ($count > 0)
			{
				if ($count == 1)
				{
					$msg = I18n::get("listing.listing_deleted_single");
				}
				else
				{
					$msg = sprintf(I18n::get("listing.listing_deleted_multiple"), $count);
				}
			}
		}
		if ($t == 1)
		{
			self::action_index(1, $msg);
		}
		else
		{
			self::action_repost($msg, 1);
		}
	}
	
	function action_delete()
	{
		//we don't want to delete anything for now, just set ad status to 3 (deleted) for now.
		$cb = Arr::get($_POST, "cb");
		$listing_id = (int)Arr::get($_GET, "listing_id");
		$user_id = (int)$this->auth->get_user()->id;
		$count = 0;
		$msg = "";
		$t = Arr::get($_GET, "t", 1);
		if ((is_array($cb) AND count($cb) > 0) OR $listing_id > 0)
		{
			if ($listing_id > 0)
			{
				$cb = array($listing_id);
			}
			foreach ($cb as $listing_id)
			{
				
				$result = DB::query(Database::UPDATE, "UPDATE listing_data SET status = '3' WHERE id = :listing_id AND (listing->'user_id')::integer = :user_id")
				->param(':listing_id', $listing_id)
				->param(':user_id', $user_id)
				->execute();
				if ( ! $result)
				{
					throw new Kohana_Exception('site_error');
				}
				else
				{
					$result = DB::query(Database::SELECT, "SELECT node_path FROM listing_data ld LEFT JOIN listing l ON ld.listing_id = l.id LEFT JOIN categories_entity ce ON l.cid = ce.id WHERE ld.id = :listing_id AND (ld.listing->'user_id')::integer = :user_id")
					->param(':listing_id', $listing_id)
					->param(':user_id', $user_id)
					->execute();
					if ( ! $result)
					{
						throw new Kohana_Exception('site_error ');
					}
					$node_path = $result[0]['node_path'];
					$result = DB::query(Database::UPDATE, "UPDATE categories_entity SET count = count - 1 WHERE node_path @> :node_path")
					->param(':node_path', $node_path)
					->execute();
					if ( ! $result)
					{
						throw new Kohana_Exception('site_error ');
					}
					$count++;
				}
				
				//$result = DB::query(Database::UPDATE, "UPDATE listing SET listing_status = 3 WHERE object_id = $listing_id AND user_id = $user_id")->execute('sphinx');

				/*
				$img_count = $listing_result[0]["img_count"];
				$uid = $listing_result[0]["uid"];
				if ($img_count > 0)
				{
					$uid_path = '/' . implode(DIRECTORY_SEPARATOR, str_split($uid, 4));
					$full_path = $this->cfg["upload_path"] . $uid_path;
					for ($i = 1; $i < ($img_count + 1); $i++)
					{
						$full_filename = $full_path . "_$i.jpg";
						$full_thumbnail_filename = $full_path . "_t$i.jpg";
						if (file_exists($full_filename))
						{
							unlink($full_filename);
						}
						if (file_exists($full_thumbnail_filename))
						{
							unlink($full_thumbnail_filename);
						}
					}
				}
				
				
				DB::query(Database::DELETE, "DELETE FROM ads where id = $listing_id")->execute('sphinx');
				//no need to delete ads_data* or ads_img manually, it has cascading delete enabled
				if ($user_id == 19)
				{
					DB::query(Database::DELETE, "DELETE FROM ads WHERE id = '$listing_id'")->execute();
				}
				else
				{
					DB::query(Database::DELETE, "DELETE FROM ads WHERE id = '$listing_id' AND user_id = '$user_id'")->execute();
				}
				*/
			
			}
			if ($count > 0)
			{
				if ($count == 1)
				{
					$msg = I18n::get("listing.listing_deleted_single");
				}
				else
				{
					$msg = sprintf(I18n::get("listing.listing_deleted_multiple"), $count);
				}
			}
		}
		if ($t == 1)
		{
			self::action_index(1, $msg);
		}
		else
		{
			self::action_repost($msg, 1);
		}
	}

	function action_repost()
	{
		$cb = Arr::get($_POST, "cb");
		$listing_id = (int)Arr::get($_GET, "listing_id");
		$user_id = (int)$this->auth->get_user()->id;
		$count = 0;
		$msg = "";
		
		if ((is_array($cb) AND count($cb) > 0) OR $listing_id > 0)
		{
			if ($listing_id > 0)
			{
				$cb = array($listing_id);
			}
			
			foreach ($cb as $listing_id)
			{
				$now = time();
				$result = DB::query(Database::UPDATE, "UPDATE listing_data SET status = '1', listing = listing || '\"modified\"=>\":now\"'::hstore WHERE id = :listing_id AND (listing->'user_id')::integer = :user_id AND status = '2'")
				->param(':now', $now)
				->param(':listing_id', $listing_id)
				->param(':user_id', $user_id)
				->execute();
				if ( ! $result)
				{
					throw new Kohana_Exception('site_error');
				}
				else
				{
					$result = DB::query(Database::SELECT, "SELECT node_path FROM listing_data ld LEFT JOIN listing l ON ld.listing_id = l.id LEFT JOIN categories_entity ce ON l.cid = ce.id WHERE ld.id = :listing_id AND (ld.listing->'user_id')::integer = :user_id")
					->param(':listing_id', $listing_id)
					->param(':user_id', $user_id)
					->execute();

					
					if ( ! $result)
					{
						throw new Kohana_Exception('site_error ');
					}
					$node_path = $result[0]['node_path'];
					
					$result = DB::query(Database::UPDATE, "UPDATE categories_entity SET count = count + 1 WHERE node_path @> '$node_path'")->execute();
					if ( ! $result)
					{
						throw new Kohana_Exception('site_error ');
					}
					$count++;
				}

				//the ad really expired, eg: more than 14day, note that if the ad was closed manually the diff of modified - created is <= 14day, so we won't update the created date again to promote the ad
				/*
				if ($duration / 86400 > $this->cfg["listing_period"])
				{
					$time = time();
					$result = DB::query(Database::UPDATE, "UPDATE listing SET status = 1, created = now(), modified = now() WHERE id = '$listing_id' AND user_id = $user_id")->execute();
					DB::query(Database::UPDATE, "UPDATE listing SET listing_status = 1, created = $time WHERE id = $id")->execute('sphinx');
				}
				else
				{
					$result = DB::query(Database::UPDATE, "UPDATE listing SET status = 1, modified = now() WHERE id = '$listing_id' AND user_id = $user_id")->execute();
					DB::query(Database::UPDATE, "UPDATE listing SET listing_status = 1 WHERE id = $id")->execute('sphinx');
				}
				*/
			
			}
			
			if ($count > 0)
			{
				if ($count == 1)
				{
					$msg = I18n::get("listing.listing_reposted_single");
				}
				else
				{
					$msg = sprintf(I18n::get("listing.listing_reposted_multiple"), $count);
				}
			}
		}
		self::action_index(2, $msg);
	}
	
	function action_close()
	{
		$cb = Arr::get($_POST, "cb");
		$listing_id = (int)Arr::get($_GET, "listing_id");
		$user_id = (int)$this->auth->get_user()->id;
		$count = 0;
		$msg = "";
		$t = Arr::get($_GET, "t", 1);
		if ((is_array($cb) AND count($cb) > 0) OR $listing_id > 0)
		{
			if ($listing_id > 0)
			{
				$cb = array($listing_id);
			}
			foreach ($cb as $listing_id)
			{
				$result = DB::query(Database::UPDATE, "UPDATE listing_data SET status = '2' WHERE id = :listing_id AND (listing->'user_id')::integer = :user_id")
				->param(':listing_id', $listing_id)
				->param(':user_id', $user_id)
				->execute();
				if ( ! $result)
				{
					throw new Kohana_Exception('site_error');
				}
				else
				{
					$result = DB::query(Database::SELECT, "SELECT node_path FROM listing_data ld LEFT JOIN listing l ON ld.listing_id = l.id LEFT JOIN categories_entity ce ON l.cid = ce.id WHERE ld.id = :listing_id AND (ld.listing->'user_id')::integer = :user_id")
					->param(':listing_id', $listing_id)
					->param(':user_id', $user_id)
					->execute();
					if ( ! $result)
					{
						throw new Kohana_Exception('site_error ');
					}
					$node_path = $result[0]['node_path'];
					$result = DB::query(Database::UPDATE, "UPDATE categories_entity SET count = count - 1 WHERE node_path @> :node_path")
					->param(':node_path', $node_path)
					->execute();
					if ( ! $result)
					{
						throw new Kohana_Exception('site_error ');
					}
					$count++;
				}
			}
			if ($count > 0)
			{
				if ($count == 1)
				{
					$msg = I18n::get("listing.listing_closed_single");
				}
				else
				{
					$msg = sprintf(I18n::get("listing.listing_closed_multiple"), $count);
				}
			}
		}
		if ($t == 1)
		{
			self::action_index(1, $msg);
		}
		else
		{
			self::action_repost($msg);
		}
	}

	
	function action_edit()
	{
		//s = step
		$s = Arr::get($_GET, "s", 2);
		if ($s == 1)
		{
			self::browse_categories();
		}
		else if ($s == 2)
		{
			$cb = Arr::get($_POST, "cb", 0);
			if ($cb == 0)
			{
				//$_POST listing_id for hidden listing_id field in browse_category form, $_GET listing_id for clicking edit details link on detials.php page
				$listing_id = (int)Arr::get($_POST, "listing_id", Arr::get($_GET, "listing_id"));
			}
			else
			{
				$listing_id = (int)$cb[0];
			}
			$this->template->title = i18n::get('edit_listing');
			$view = self::load_form(2, $listing_id);
			$this->template->content = $view;
		}
	}

	
	function action_new()
	{
		/*
		$s = 1: step1 browse cat
		$s = 2: step2 load form
		$sid = selected id
		*/
		$s = (int)Arr::get($_POST, "s", Arr::get($_GET, "s", 1));
		
		//temporary test
		//$s = 2;
		//$_POST['cid'] = 14;
		
		if ($s == 1)
		{
			self::browse_categories();
		}
		else if ($s == 2)
		{
			$cid = (int)Arr::get($_POST, 'cid');
			$view = self::load_form(1, $cid);
			$view->cfg = $this->cfg;
			$this->template->title = I18n::get('new_listing');
			$this->template->content = $view;
		}
		
	}
	
	function check_store_address(Validation $post = NULL, $address1)
	{
		//check whether there's valid store address saved in preference table, if the user already chosed to sell offline via physical store, the user_preference record shall have at least selling_option = 4 (which will also contain store address
		//if (($selling_option & 4) < 1)
		if ($address1 == '')
		{
			$post->error('store_address', 'default');
		}
	}

	function check_selling_options(Validation $post = NULL)
	{
		$p3 = $_POST['p3'];
		$p4 = $_POST['p4'];
		$p5 = $_POST['p5'];
		//user must specify at least one selling option
		if ($p3 != 'on' AND $p4 != 'on' AND $p5 != 'on')
		{
			$post->error('selling_options', 'default');
		}	
	}

	function action_post()
	{
		//$t = type
		
		$idd = Arr::get($_POST, 'idd', 0);
		$t = Arr::get($_POST, "t");
		$user_id  = $this->auth->get_user()->id;
		$listing = array();
		/*
		elm_type = 0: dropdown menu, system-defined, stored in listing_data_system
		elm_type = 1: checkbox, stored in listing_data_user
		elm_type = 2: textbox, user-defined, stored in listing_data_user
		elm_type = 3: dropdown menu, birth year, stored in listing_data_user
		elm_type = 4: dropdown menu, expiry year, stored in listing_data_user
		*/
		$data_type = array('_system', '_user', '_user', '_user', '_user');
		

		if ($t == 1)
		{
			$arr['header'] = I18n::get('new_listing');
		}
		else
		{
			$arr['header'] = I18n::get('edit_listing');
		}

		if ($_POST > 0)
		{
			$session_uuid = Session::instance()->get("uuid");
			$post_uuid = Arr::get($_POST, "uuid");
			$code = Arr::get($_POST, "code");
			

			if (self::is_duplicate())
			{
				$view = View::factory(TEMPLATE . '/special_info', $arr);
				$listing_id = Session::instance()->get("listing_id");
				if ($listing_id == "")
				{
					$view->msg = sprintf(I18n::get('listing_posted'));
				}
			}
			else
			{
				$error = 0;
				$errors = array();;


				$post = Validation::factory($_POST)
						->rule('country', 'not_empty')
						->rule('country', 'digit');


				if ($code == "")
				{
					$post->rule('title', 'not_empty')
						->rule('title', 'max_length', array(':value', 80))
						->rule('description', 'not_empty')
						->rule('description', 'max_length', array(':value', 16384));
						
						

					
					$object_type_id = 1;
					$object_id = '';
					$title = substr($post['title'], 0, 80);
					$description = $post['description'];
					//condition description only needed for system listing
					$condition_description = '';
				}
				else
				{
					$product_obj = DB::query(Database::SELECT, "SELECT l.id, l.title, l.description, l.cid FROM listing l WHERE l.uid = :pg_code AND l.status = '1'")
					->param(':code', $code)
					->execute();
					if (count($product_obj) < 1)
					{
						throw new Kohana_Exception('site_error');
					}
					$object_type_id = 2;
					$object_id = $product_obj[0]["id"];
					$cid = $product_obj[0]['cid'];
					
					if ($post["condition_description"] == '')
					{
						$condition_description = '';
					}
					else
					{
						$condition_description = self::format_text($post["condition_description"]);
					}
				}

			
				$country_id = (int)$post['country'];
				$geo1_id = (int)$post['geo1'];
				$geo2_id = (int)$post['geo2'];
				$neighborhood_id = (int)$post['neighborhood'];
				$zip = sprintf("%05d", (int)$post['zip']);
				$location = $post['location'];

				
				$currency_obj = DB::query(Database::SELECT, "SELECT preference->'currency_code' AS currency_code, preference->'cryptocurrency' AS cryptocurrency FROM user_preference WHERE user_id = :user_id")
				->param(':user_id', $user_id)
				->execute();
				if (count($currency_obj) > 0)
				{
					$currency_code = $currency_obj[0]['currency_code'];
					$cryptocurrency = $currency_obj[0]['cryptocurrency'];
				}
				else
				{
					DB::query('NULL', 'ROLLBACK')->execute();
					throw new Kohana_Exception('site_error ');
				}

				//user selected to sell offline via physical store, we need to check for valid address in user_preference
				if (isset($post['p5']))
				{
					$user_store_obj = DB::query(Database::SELECT, "SELECT address->'street1' AS street1 FROM user_store WHERE user_id = :user_id")
					->param(':user_id', $user_id)
					->execute();
					$street1 = $user_store_obj[0]['street1'];
					
					$post->rule('store_address', array($this, 'check_store_address'), array(':validation', $street1));
				}
				
				$preference_array = array();
				$preference_obj = DB::query(Database::SELECT, "SELECT (each(preference)).key, (each(preference)).value FROM user_preference WHERE user_id = :user_id")
				->param(':user_id', $user_id)
				->execute();
				if (count($preference_obj) > 0)
				{
					foreach($preference_obj as $record)
					{
						$preference_array[$record['key']] = $record['value'];
					}
				}
				$preference_array["currency_code"] = $currency_code;
				$preference_array["country_id"] = $country_id;
				$preference_array["geo1_id"] = $geo1_id;
				$preference_array["geo2_id"] = $geo2_id;
				$preference_array["neighborhood_id"] = $neighborhood_id;
				$preference_array["zip"] = $zip;
				$preference_array["location"] = $location;
				
				/*
				Validate rules mapping
				eg: Validate::factory($_POST)->rule('title', $rules[0]);
				data_type field in categories_attribute is used only for validation
				0 = number, by default dropdown box option is mapped automaticaly to number, otherwise, the value is considered 'exploited', stored in ads_data
				1 = filtering off	user-defined value can be anything, value 1 won't be processed by the validation rule, stored in ads_data_string
				2 = alpha / string   user-defined value shall be alpha/string only,
				3 = number		user-defined value shall be number only, eg: square footage, stored in ads_data_string
				4 = alphanemric   user-defined value shall be alpha/string only, eg: car VIN, stored in ads_data_string
				not empty ????????????????? FTW
				*/

				$price = Arr::get($_POST, "price", '');

				$item_weight = Arr::get($_POST, "item_weight");
				$weight_unit = (int)Arr::get($_POST, "weight_unit");
				$rules = array(0 => 'digit', 1 => 'dummy', 2 => 'alpha', 3 => 'digit', 4 => 'alpha_numeric');
				$cid = (int)Arr::get($_POST, "cid");
				//id = ad id
				$id = (int)Arr::get($_POST, "id");
				
				
				$result = DB::query(Database::SELECT, "SELECT has_img, has_price, has_item_condition, has_shipping, has_selling_option, has_quantity, node_path FROM categories_entity WHERE id = :cid AND has_child = '0' AND active = '1'")
				->param(':cid', $cid)
				->execute();
				
				$has_price = 0;
				$has_img = 0;
				$has_shipping = 0;
				$has_selling_option = 0;
				if (count($result) > 0)
				{
					$has_price = $result[0]["has_price"];
					$has_img = $result[0]["has_img"];
					$has_shipping = $result[0]["has_shipping"];
					$has_selling_option = $result[0]['has_selling_option'];
					$has_quantity = $result[0]['has_quantity'];
					$node_path = $result[0]['node_path'];
					$array_node_path = explode('.', $node_path);
				}
				else
				{
					throw new Kohana_Exception('site_error ');
				}
				
				if ($has_quantity == 1)
				{
					$post->rule('quantity', 'range', array(':value', 1, 99999));
					$listing['quantity'] = (int) $post['quantity'];
				}
				else
				{
					//set it to 1 so that the main query on view/search.php can fetch listing of category charity/volunteer where quantity > 0
					$listing['quantity'] = 1;
				}
				

				if (bindec($has_selling_option) & 1 > 0)
				{
					
					$selling_option = 1;
					$post->rule('payment_method', 'in_array', array(':value', array(1)))
					->rule('price', 'numeric')
					->rule('price', 'not_empty');
					//->rule('selling_options', array($this, 'check_selling_options'), array(':validation', ':field', 'selling_options'));
						
					if ($post['p4'] == 'on')
					{
						$post->rule('third_party_url', 'url');
					}
				}
				if ($post['item_condition'] AND $post['item_condition'] != '')
				{
					$item_condition_id = $post['item_condition'];
				}
				else
				{
					$item_condition_id = 'null';
				}
				
				
				if ($has_shipping == 1)
				{
					$post->rule('shipping_service', 'in_array', array(':value', array(1)));
					if ($item_weight != '')
					{
						$post->rule('item_weight', 'numeric');
					}
				}

				$attr_result = DB::query(Database::SELECT, "SELECT ca.id, ca.elm_type, ca.data_type FROM categories_entity_attribute cea LEFT JOIN categories_attribute ca ON cea.attribute_id = ca.id WHERE cea.entity_id = :cid")
				->param(':cid', $cid)
				->execute();

				$attr_arr = array();
				foreach ($attr_result as $attr)
				{
					$attr_arr[$attr["id"]]["elm_type"] = $attr["elm_type"];
					$attr_arr[$attr["id"]]["data_type"] = $attr["data_type"];
				}

				$rf = -1; //range filter idx (categories_attribute id)
				if (count($attr_arr) > 0)
				{
					foreach ($attr_arr as $key => $value)
					{
						//ignore data_type = 1, field that doesn't require validation, arbitrary data type eg: address (data_type != 1 dummy + skip authentication for checkboxes (is_array type)
						if ( ! is_array(Arr::get($_POST, "t".$key)) AND $value["data_type"] != 1)
						{
							$post->rule("t".$key, $rules[$value["data_type"]]);
						}
						
						//if categories_attribute's data_type is 3, range filter
						if ($value['data_type'] == 3)
						{
							$rf = $key;
						}
					}
				}
				

				if ($post->check())
				{
					$neighborhood_id = (int)Arr::get($_POST, "neighborhood");
					
					//if country = usa
					if ($country_id == "244")
					{
						$geo1_id = 'null';
						$geo2_id = 'null';
						$zip = sprintf("%05d", (int)$post['zip']);

						$city_results = DB::query(Database::SELECT, "SELECT id, city, state, RADIANS(latitude1) AS latitude1, RADIANS(longitude1) AS longitude1, RADIANS(latitude2) AS latitude2, RADIANS(longitude2) AS longitude2 FROM zip WHERE zip = :zip")
						->param(':zip', $zip)
						->execute();
						$zip_id = $city_results[0]['id'];
						$geo1_name = $city_results[0]['state'];
						$geo2_name = $city_results[0]['city'];

						
						if ($neighborhood_id == 0)
						{
							$neighborhood_id = 'null';
						}
						else
						{
							$neighborhood_result = DB::query(Database::SELECT, "SELECT name FROM neighborhood WHERE id = :neighborhood_id")
							->param(':neighborhood_id', $neighborhood_id)
							->execute();
							if ($neighborhood_result[0]['name'] == "")
							{
								$neighborhood_id = 'null';
							}
						}
					}
					else
					{
						$neighborhood_id = 'null';
						$zip = '';
						$zip_id = 'null';
						if ($country_id != 244)
						{
							if ($geo1_id == '')
							{
								$geo1_id = 'null';
							}
							if ($geo2_id == '')
							{
								$geo2_id = 'null';
							}
						}
						else
						{
							$geo1_id = 'null';
							$geo2_id = 'null';
						}
					}

					if ($post["location"] == '')
					{
						$location = '';
					}
	
					$node_path_results = DB::query(Database::SELECT, "SELECT node_path FROM categories_entity WHERE id = :cid")
					->param(':cid', $cid)
					->execute();
					$node_path = $node_path_results[0]["node_path"];
					$node_path = str_replace('.', ',', $node_path);
					
					
					$img_count = 0;
					$img_error = 0;
					
					
					
					/*
					$p3 = isset($post['p3']) ? 1 : 0;
					$p4 = isset($post['p4']) ? 1 : 0;
					$p5 = isset($post['p5']) ? 1 : 0;
					
					if ($p3 == 1)
					{
						$selling_option += 1;
					}
					if ($p4 == 1)
					{
						$selling_option += 2;
						$listing['third_party_url'] = $post['third_party_url'];
					}
					if ($p5 == 1)
					{
						$selling_option += 4;
					}
					$preference_array['selling_option'] = $selling_option;
					*/
	
					
					$array_old = array(':', '"null"');
					$array_new = array('=>', 'null');
					$hstore_preference = substr(str_replace($array_old, $array_new, json_encode($preference_array)), 1, -1);
					
					
					$listing['shippable'] = $has_shipping;
					$listing['user_id'] = $user_id;
					$listing['selling_option'] = $selling_option;
					

					if ($has_price == 1)
					{
						if (array_key_exists($currency_code, $this->cfg_crypto))
						{
							$listing['price'] = sprintf("%0.5f", $price);
						}
						else
						{
							$listing['price'] = sprintf("%0.2f", $price);
						}
					}
					else
					{
						$listing['price'] = '';
					}
					$listing['price_usd'] = sprintf("%0.2f", $this->cfg_currency[$currency_code . '_usd'] * $price);
					$listing['item_weight'] = sprintf("%0.2f", $item_weight);
					$listing['weight_unit'] = $weight_unit;
					//$listing['price_usd'] = number_format($this->cfg_currency[$currency_code . '_usd'] * $price, 2, '.', '');
					$listing['cryptocurrency'] = $cryptocurrency;
					
					//let the user selects the desired currency
					$listing['currency_code'] = $currency_code;
					$listing['item_condition_id'] = $item_condition_id; 
					$listing['condition_description'] = $condition_description; 
					$listing['country_id'] = $country_id; 
					$listing['geo1_id'] = $geo1_id; 
					$listing['geo2_id'] = $geo2_id; 
					$listing['neighborhood_id'] = $neighborhood_id; 
					$listing['zip_id'] = $zip_id; 
					$listing['location'] = self::format_text($location);
					//idd = instant digital delivery enabled
					$listing['idd'] = $idd;
					
					if ($t == 1)
					{
						$listing['created'] = time();
					}
					else
					{
						$listing['modified'] = time(); 
					}

					$array_old = array(':', '"null"');
					$array_new = array('=>', 'null');
	
					

					DB::query('NULL', 'BEGIN')->execute();

				
					$result = DB::query(Database::UPDATE, "UPDATE user_preference SET preference = :hstore_preference WHERE user_id = :user_id")
					->param(':hstore_preference', $hstore_preference)
					->param(':user_id', $user_id)
					->execute();
					if ( ! $result)
					{
						DB::query('NULL', 'ROLLBACK')->execute();
						throw new Kohana_Exception('site_error ');
					}
					
					if ($t == 1)
					{
						$uid = 0;
						
						if ($object_type_id == 1)
						{
							$result = DB::query(Database::INSERT, "INSERT INTO listing(title, description, cid, object_type_id) VALUES(:title, :description, :cid, '1')")
							->param(':title', $title)
							->param(':description', $description)
							->param(':cid', $cid)
							->execute();
							if ( ! $result)
							{
								DB::query('NULL', 'ROLLBACK')->execute();
								throw new Kohana_Exception('site_error ');
							}
							
							$result = DB::query(Database::SELECT, "SELECT currval('listing_id_seq')")->execute();
							if ( ! $result)
							{
								DB::query('NULL', 'ROLLBACK')->execute();
								throw new Kohana_Exception('site_error ');
							}
							$listing_id = $result[0]['currval'];
							
							$result = DB::query(Database::SELECT, "SELECT stringify_bigint(pseudo_encrypt(:listing_id)) AS uid")
							->param(':listing_id', $listing_id)
							->execute();
							if ( ! $result)
							{
								DB::query('NULL', 'ROLLBACK')->execute();
								throw new Kohana_Exception('site_error ');
							}
							$uid = $result[0]["uid"];
							
							//temporarily only allow image upload for user-type listing
							self::process_upload($listing_id, $uid, $img_error, $img_count);
							if ($img_error == 1)
							{
								DB::query('NULL', 'ROLLBACK')->execute();
								throw new Kohana_Exception('site_error: img_error');
							}
							
					
							if (count($attr_arr) > 0)
							{
								foreach ($attr_arr as $ca_id => $attr_value)
								{
									$table = 'listing_data' . $data_type[$attr_value["data_type"]];
									$ce = Arr::get($_POST, "t".$ca_id);
									if ($ce != "")
									{
										//if class attribute is of type checkbox
										if (is_array($ce))
										{
											foreach ($ce as $item)
											{
												$item = (int)$item;
												$result = DB::query(Database::INSERT, "INSERT INTO :table(ca_id, ca_value, listing_id) VALUES(:ca_id, :item, :listing_id)")
												->param(':table', $table)
												->param(':ca_id', $ca_id)
												->param(':item', $item)
												->param(':listing_id', $listing_id)
												->execute();
												if ( ! $result)
												{
													DB::query('NULL', 'ROLLBACK')->execute();
													throw new Kohana_Exception('site_error');
												}
											}
										}
										else
										{
											if ($attr_value["data_type"] == 0)
											{
												$data = (int)$ce;
											}
											else
											{
												$data = $ce;
											}
											$result = DB::query(Database::INSERT, "INSERT INTO :table(ca_id, ca_value, listing_id) VALUES(:ca_id, :data, :listing_id)")
											->param(':table', $table)
											->param(':ca_id', $ca_id)
											->param(':data', $data)
											->param(':listing_id', $listing_id)
											->execute();
											if ( ! $result)
											{
												DB::query('NULL', 'ROLLBACK')->execute();
												throw new Kohana_Exception('site_error');
											}
										}
									}
								}
							}
						
							$object_id = $listing_id;
						
							//only allow changes to user-posted listing
							if ($object_type_id == 1)
							{
								$result = DB::query(Database::UPDATE, "UPDATE listing SET uid = :uid, img_count = :img_count WHERE id = :object_id AND object_type_id = '1'")
								->param(':uid', $uid)
								->param(':img_count', $img_count)
								->param(':object_id', $object_id)
								->execute();
								if ( ! $result)
								{
									DB::query('NULL', 'ROLLBACK')->execute();
									throw new Kohana_Exception('site_error ');
								}
							}
						
						}
						else
						{
							$uid = $code;
							$listing_id = $id;
						}
						//the two lines below appear in new/edit section because in the edit section, listing_data.listing->'created' is fetched seperately, so we need to include $listing['created'] before building the hstore
						$hstore_listing = substr(str_replace($array_old, $array_new, json_encode($listing)), 1, -1);
						$hstore_listing = str_replace('http=>', 'http:', $hstore_listing);
						
						$ship_to = $has_shipping == 1 ? $this->auth->get_user()->ship_to : NULL;
					
						$result = DB::query(Database::INSERT, "INSERT INTO listing_data(listing_id, listing, ship_to) VALUES(:object_id, :hstore_listing, :ship_to)")
						->param(':object_id', $object_id)
						->param(':hstore_listing', $hstore_listing)
						->param(':ship_to', $ship_to)
						->execute();
						if ( ! $result)
						{
							DB::query('NULL', 'ROLLBACK')->execute();
							throw new Kohana_Exception('site_error ');
						}
						
						$result = DB::query(Database::SELECT, "SELECT currval('listing_data_id_seq')")->execute();
						if ( ! $result)
						{
							DB::query('NULL', 'ROLLBACK')->execute();
							throw new Kohana_Exception('site_error ');
						}
						$listing_data_id = $result[0]['currval'];
						
						//for instant digital delivery
						$content_new = Arr::get($_POST, 'content_new');
						
						if (count($content_new) > 0)
						{
							foreach ($content_new as $index => $value)
							{
								if (trim($value) != '')
								{
									$result = DB::query(Database::INSERT, "INSERT INTO digital_content(listing_data_id, content, user_id, active) VALUES(:listing_data_id, :content, :user_id, '$idd')")
									->param(':listing_data_id', $listing_data_id)
									->param(':content', $value)
									->param(':user_id', $user_id)
									->execute();
								}
							}
						}
						
						$result = DB::query(Database::SELECT, "SELECT node_path FROM categories_entity WHERE id = :cid")
						->param(':cid', $cid)
						->execute();
						if ( ! $result)
						{
							DB::query('NULL', 'ROLLBACK')->execute();
							throw new Kohana_Exception('site_error ');
						}
						$node_path = $result[0]['node_path'];
						$result = DB::query(Database::UPDATE, "UPDATE categories_entity SET count = count + 1 WHERE node_path @> :node_path")
						->param(':node_path', $node_path)
						->execute();
					
						if ( ! $result)
						{
							DB::query('NULL', 'ROLLBACK')->execute();
							throw new Kohana_Exception('site_error ');
						}

					//update/edit existing ad
					}
					else
					{
						$iq = substr(Arr::get($_POST, "iq"), 1);
						$iq_arr = array();
						if ($iq != "")
						{
							$iq_arr = explode("_", $iq);
							sort($iq_arr);
						}
						
						// make sure if the result submitted is not *hacked*, db_cid_result will also return different cid if user changed the category
						$db_listing_result = DB::query(Database::SELECT, "SELECT l.id, object_type_id, cid, img_count, uid, ld.listing->'created' AS created, ld.listing->'idd' AS idd FROM listing_data ld LEFT JOIN listing l ON ld.listing_id = l.id WHERE ld.id = :id AND (ld.listing->'user_id')::integer = :user_id")
						->param(':id', $id)
						->param(':user_id', $user_id)
						->execute();
						
						if (count($db_listing_result) == 1)
						{
							$object_type_id = $db_listing_result[0]['object_type_id'];
						
							$db_cid = $db_listing_result[0]["cid"];
							$db_img_count = $db_listing_result[0]["img_count"];
							$uid = $db_listing_result[0]["uid"];
							$listing['created'] = $db_listing_result[0]["created"];
							$db_idd = $db_listing_result[0]["idd"];

							$uid_path = '/' . implode(DIRECTORY_SEPARATOR, str_split($uid, 4));
							$full_path = $this->cfg["upload_path"] . $uid_path;
							
							//if user changed category, remove all the existing listing_data_user and listing_data_system
							if ($db_cid != $cid)
							{
								DB::query(Database::DELETE, "DELETE FROM listing_data_system WHERE listing_id = :id")
								->param(':id', $id)
								->execute();
								DB::query(Database::DELETE, "DELETE FROM listing_data_user WHERE listing_id = :id")
								->param(':id', $id)
								->execute();
								
								if ($db_img_count > 0)
								{
									
									
									for ($i = 1; $i < ($db_img_count + 1); $i++)
									{
										
										/*
										todo
										$full_filename = $full_path . "_$i.jpg";
										$full_thumbnail_filename = $full_path . "_t$i.jpg";
										

										if (file_exists($full_filename))
										{
											unlink($full_filename);
										}
										if (file_exists($full_thumbnail_filename))
										{
											unlink($full_thumbnail_filename);
										}
										
										if ($i == 1)
										{
											$full_mobile_filename = $full_path . "_m1.jpg";
											if (file_exists($full_mobile_filename))
											{
												unlink($full_mobile_filename);
											}
										}
										*/
										
										$db_img_count--;
									}
									
								}
								$result = DB::query(Database::SELECT, "SELECT node_path FROM categories_entity WHERE id = :db_cid")
								->param(':db_cid', $db_cid)
								->execute();
								if ( ! $result)
								{
									DB::query('NULL', 'ROLLBACK')->execute();
									throw new Kohana_Exception('site_error ');
								}
								$node_path = $result[0]['node_path'];
								
								/*
								$result = DB::query(Database::UPDATE, "UPDATE categories_entity SET count = count - 1 WHERE node_path @> :node_path")
								->param(':node_path', $node_path)
								->execute();
								if ( ! $result)
								{
									DB::query('NULL', 'ROLLBACK')->execute();
									throw new Kohana_Exception('site_error ');
								}
								*/
								
								$result = DB::query(Database::SELECT, "SELECT node_path FROM categories_entity WHERE id = :cid")
								->param(':cid', $cid)
								->execute();
								if ( ! $result)
								{
									DB::query('NULL', 'ROLLBACK')->execute();
									throw new Kohana_Exception('site_error ');
								}
								
								/*
								$node_path = $result[0]['node_path'];
								$result = DB::query(Database::UPDATE, "UPDATE categories_entity SET count = count + 1 WHERE node_path @> :node_path")
								->param(':node_path', $node_path)
								->execute();
								if ( ! $result)
								{
									DB::query('NULL', 'ROLLBACK')->execute();
									throw new Kohana_Exception('site_error ');
								}
								*/
							}
													
							if ($db_img_count > 0)
							{
								$db_img_arr = array();
								for ($i = 1; $i < ($db_img_count + 1); $i++)
								{
									$db_img_arr[$i] = 1;
								}
							}
								

							if (count($attr_arr) > 0)
							{
								//note that data_type = 0 in categories_attribute refers to drop down
								
								
								$data_type = array('_system', '_user', '_user', '_user', '_user');
								
								//saved listing data
								$data_result = DB::query(Database::SELECT, "SELECT l.id AS listing_id, ca_id, CAST(ca_value AS varchar) FROM listing_data_system WHERE listing_id = :id UNION ALL SELECT id, ca_id, ca_value FROM listing_data_user WHERE listing_id = :id")
								->param(':id', $id)
								->execute();
								$data_arr = array();
								foreach ($data_result as $data)
								{
									$data_arr[$data["ca_id"]][$data["id"]] = $data["ca_value"];
								}
								foreach ($attr_arr as $ca_id => $attr_value)
								{
									$table = 'listing_data' . $data_type[$attr_value["data_type"]];
									$value = Arr::get($_POST, "t".$ca_id);
									//checkboxes
									if ($attr_arr[$ca_id]["elm_type"] == 2)
									{
										//store user-submitted checkboxes to array
										$cb_arr = array();
										if (is_array($value) AND count($value) > 0)
										{
											foreach ($value as $idx => $cb_value)
											{
												$cb_arr[] = $cb_value;
											}
										}
										//if user-submitted dropdown/checkbox itmes found in table/saved previously, delete them
										if (isset($data_arr[$ca_id]))
										{
											foreach ($data_arr[$ca_id] as $listing_id => $listing_value)
											{
												if (! in_array($listing_value, $cb_arr))
												{
													$result = DB::query(Database::DELETE, "DELETE FROM :table WHERE id = :listing_id")
													->param(':table', $table)
													->param(':listing_id', $listing_id)
													->execute();
													if ( ! $result)
													{
														DB::query('NULL', 'ROLLBACK')->execute();
														throw new Kohana_Exception('site_error');
													}
												}
												else
												{
													$cb_idx = array_search($listing_value, $cb_arr);
													unset($cb_arr[$cb_idx]);
												}
											}
										}
										foreach ($cb_arr as $cb)
										{
											$cb = (int)$cb;
											$result = DB::query(Database::INSERT, "INSERT INTO :table (ca_id, ca_value, listing_id) VALUES (:ca_id, :cb, :id)")
											->param(':table', $table)
											->param(':ca_id', $ca_id)
											->param(':cb', $cb)
											->param(':id', $id)
											->execute();
											if ( ! $result)
											{
												DB::query('NULL', 'ROLLBACK')->execute();
												throw new Kohana_Exception('site_error');
											}
										}
									}
									else
									{
										if ($attr_value["data_type"] == 0)
										{
											$data = (int)$value;
										}
										else if ($attr_value["data_type"] == 3)
										{
											//data_type == 3 is range filter,int type data: eg: 1000 - 5000
											$data = (int)$value;
										}
										else
										{
											$data = $value;
										}

										//if the attribute is empty, we need to delete it
										//update or delete
										if (array_key_exists($ca_id, $data_arr))
										{
											list($listing_id, $listing_value) = each($data_arr[$ca_id]);
											//delete, $listing_id  = ad data
											if ($value == "")
											{
												$result = DB::query(Database::DELETE, "DELETE FROM :table WHERE id = :listing_id")
												->param(':table', $table)
												->param(':listing_id', $listing_id)
												->execute();
												if ( ! $result)
												{
													DB::query('NULL', 'ROLLBACK')->execute();
													throw new Kohana_Exception('site_error');
												}
											}
											//update
											else
											{
												if ($value != $listing_value)
												{
													$result = DB::query(Database::UPDATE, "UPDATE :table SET ca_value = :data WHERE id = :listing_id")
													->param(':table', $table)
													->param(':data', $data)
													->param(':listing_id', $listing_id)
													->execute();
													if ( ! $result)
													{
														DB::query('NULL', 'ROLLBACK')->execute();
														throw new Kohana_Exception('site_error');
													}
												}
											}	
										}
										else
										{
											if ($value != "")
											{
												//insert
												$result = DB::query(Database::INSERT, "INSERT INTO :table (ca_id, ca_value, listing_id) VALUES (:ca_id, :data, :id)")
												->param(':table', $table)
												->param(':ca_id', $ca_id)
												->param(':data', $data)
												->param(':id', $id)
												->execute();
												if ( ! $result)
												{
													DB::query('NULL', 'ROLLBACK')->execute();
													throw new Kohana_Exception('site_error');
												}
											}
										}
									}
								}
							}
				
							
							//$uid_path = '/' . implode(DIRECTORY_SEPARATOR, str_split($uid, 4));
							//iq_arr = images to be removed
							if (count($iq_arr) > 0)
							{
								sort($iq_arr);
								$deleted_iq_arr = array();
								foreach ($iq_arr as $iq)
								{
									unset($db_img_arr[$iq]);
									$deleted_iq_arr[$iq] = 0;

									/*
									todo
									$full_filename = $full_path . "_$iq.jpg";
									$full_thumbnail_filename = $full_path . "_t$iq.jpg";
									if (file_exists($full_filename))
									{
										unlink($full_filename);
									}
									if (file_exists($full_thumbnail_filename))
									{
										unlink($full_thumbnail_filename);
									}
									if ($iq == 1)
									{
										$full_mobile_filename = $full_path . "_m1.jpg";
										if (file_exists($full_mobile_filename))
										{
											unlink($full_mobile_filename);
										}
									}
									*/
									
								}
							}

							//print"<br>uid: $uid";
							//print"<br>comparison: $db_img_count === " . count($db_img_arr)."<br>===================<br><br>";
							
							
							$img_path = implode('/', str_split($uid, 2));
							$img_path = substr($img_path, 0, 8);
						
						
							$img_count = count($db_img_arr);

							if ($db_img_count != count($db_img_arr))
							{
								//print"<br>db_img_count: $db_img_count";
								foreach ($db_img_arr as $key => $value)
								{
									$first_index = key($deleted_iq_arr);
									if ($first_index < $key)
									{
										//print "<br>before: $key === after: $first_index";
										
										
										/*
										rename($full_path . "_$key.jpg", $full_path . "_$first_index.jpg");
										rename($full_path . "_t$key.jpg", $full_path . "_t$first_index.jpg");
										*/
										
										$result = rename("s3://{$this->cfg["bucket_name"]}/{$this->cfg['size_large']}/$img_path/{$uid}_{$key}.jpg", "s3://{$this->cfg["bucket_name"]}/{$this->cfg['size_large']}/$img_path/{$uid}_{$first_index}.jpg");
										if ($result == 0)
										{
											DB::query('NULL', 'ROLLBACK')->execute();
											throw new Kohana_Exception('site_error');
										}
										
										$result = rename("s3://{$this->cfg["bucket_name"]}/{$this->cfg['size_medium']}/$img_path/{$uid}_{$key}.jpg", "s3://{$this->cfg["bucket_name"]}/{$this->cfg['size_medium']}/$img_path/{$uid}_{$first_index}.jpg");
										if ($result == 0)
										{
											DB::query('NULL', 'ROLLBACK')->execute();
											throw new Kohana_Exception('site_error');
										}
										
										$result = rename("s3://{$this->cfg["bucket_name"]}/{$this->cfg['size_small']}/$img_path/{$uid}_{$key}.jpg", "s3://{$this->cfg["bucket_name"]}/{$this->cfg['size_small']}/$img_path/{$uid}_{$first_index}.jpg");
										if ($result == 0)
										{
											DB::query('NULL', 'ROLLBACK')->execute();
											throw new Kohana_Exception('site_error');
										}
										
										
										unset($deleted_iq_arr[$first_index]);
										$deleted_iq_arr[$key] = 1;
										ksort($deleted_iq_arr);
									}
								}
							}
							if ($object_type_id == 1)
							{
								self::process_upload($id, $uid, $img_error, $img_count);
							}
							if ($img_error == 1)
							{
								DB::query('NULL', 'ROLLBACK')->execute();
								throw new Kohana_Exception('site_error: img_error');
							}
							
							
							//at the moment we only allow user to switch from non-gtin listing to non-gtin listing, not available for non-gtin listing to gtin-listing, see: object_type_id = '1' <- don't take this away as we do not want user to manipulate the system data.
							if ($object_type_id == 1)
							{
								$result = DB::query(Database::UPDATE, "UPDATE listing SET img_count = :img_count, title = :title, description = :description, cid = :cid WHERE id = :id AND object_type_id = '1'")
								->param(':img_count', $img_count)
								->param(':title', $title)
								->param(':description', $description)
								->param(':cid', $cid)
								->param(':id', $db_listing_result[0]['id'])
								->execute();
								if ( ! $result)
								{
									DB::query('NULL', 'ROLLBACK')->execute();
									throw new Kohana_Exception('site_error');
								}
							}
							
					
							//for instant digital delivery
							$content_new = Arr::get($_POST, 'content_new');
							if (count($content_new) > 0)
							{
								foreach ($content_new as $index => $value)
								{
									if (trim($value) != '')
									{
										$result = DB::query(Database::INSERT, "INSERT INTO digital_content(listing_data_id, content, user_id, active) VALUES(:listing_data_id, :content, :user_id, '$idd')")
										->param(':listing_data_id', $id)
										->param(':content', $value)
										->param(':user_id', $user_id)
										->execute();
										if ( ! $result)
										{
											DB::query('NULL', 'ROLLBACK')->execute();
											throw new Kohana_Exception('site_error');
										}
									}
								}
							}
							
							
							
							$digital_content_obj = DB::query(Database::SELECT, "SELECT id, content FROM digital_content WHERE user_id = :user_id AND used = '0' AND listing_data_id = :listing_data_id")
							->param(':user_id', $user_id)
							->param(':listing_data_id', $id)
							->execute();

							if (count($digital_content_obj) > 0)
							{
								foreach ($digital_content_obj as $record)
								{
									$digital_content_id = $record['id'];
									$content = Arr::get($_POST, "content_$digital_content_id");
	
									//if user uncheck 'enable instant digital delivery, we want to update all records in digital_content so that they are set to 'inactive', block_notify in Crypto class will only query active record
									if ($db_idd != $idd)
									{
										DB::query(Database::UPDATE, "UPDATE digital_content SET content = :content, active = '$idd' WHERE id = :digital_content_id")
										->param(':content', $content)
										->param(':digital_content_id', $digital_content_id)
										->execute();
									}
									
									else if (isset($_POST["content_$digital_content_id"]) AND $record['content'] != $content)
									{
										//print"<br>huha $content $digital_content_id " . $record['content'];
										DB::query(Database::UPDATE, "UPDATE digital_content SET content = :content WHERE id = :digital_content_id")
										->param(':content', $content)
										->param(':digital_content_id', $digital_content_id)
										->execute();
									}
								}
							}


							$hstore_listing = substr(str_replace($array_old, $array_new, json_encode($listing)), 1, -1);
							$hstore_listing = str_replace('http=>', 'http:', $hstore_listing);
							$result = DB::query(Database::UPDATE, "UPDATE listing_data SET listing = :hstore_listing WHERE id = :id")
							->param(':hstore_listing', $hstore_listing)
							->param(':id', $id)
							->execute();
							if ( ! $result)
							{
								DB::query('NULL', 'ROLLBACK')->execute();
								throw new Kohana_Exception('site_error');
							}
							
							

						}
						else
						{
							//fool the hacker that the info has been updated.
							$view = View::factory(TEMPLATE . '/special_info', $arr);
							$view->msg = I18n::get('listing_posted');
						}
					}
					
				}
				else
				{
					//throw new Kohana_Exception('site_error');
					$error = 1;
				}

				

				if ($error == 0)
				{
					DB::query('NULL', 'COMMIT')->execute();
					Session::instance()->delete("uuid");
					$uuid = uniqid("uuid_", true);
					Session::instance()->set("uuid", $uuid);
					$view = View::factory(TEMPLATE . '/special_info', $arr);
					$view->url = "/st/$uid";
					$view->msg = sprintf(I18n::get('listing_posted'), $view->url);
					if ($t == 1)
					{
						
						$view->msg = sprintf(I18n::get('listing_posted'), $view->url);
						Session::instance()->set("listing_id", $listing_id);
					}
					else
					{
						$view->msg = sprintf(I18n::get('listing_updated'), $view->url);
						Session::instance()->set("listing_id", $id);
					}
				}
				else
				{
					
					$t = Arr::get($_POST, "t");
					$id = Arr::get($_POST, "id");
					if ($t == 1)
					{
						$view = self::load_form(1, $cid);
					}
					else
					{
						$view = self::load_form(2, $id);
					}
					$view->errors = $post->errors('/my/new_listing2');
				}
			}
			$this->template->title = I18n::get('new_listing');
			$this->template->content = $view;
		}
		else
		{
			Request::current()->redirect('/');
		}
	}
	
	function action_past()
	{
		self::action_index(2);
	}
	
	function action_blocked()
	{
		self::action_index(4);
	}
	
	function action_index($type = 1, $msg = "")
	{
		/*
		$type = listing status (post_status table)
		$type = 1: active listings
		$type = 2: closed listings
		$type = 3: deleted listings
		$type = 4: blocked listings (changed from 1 -> 4 by admin, ad is still in db but invisible)
		*/
		$array_status = array();
		if ($type == 1)
		{
			$status = '1';
		}
		else if ($type == 2)
		{
			$status = '2';
		}
		else
		{
			$status = '4,5,6,7,8';
		}
		
		$user_id = (int)$this->auth->get_user()->id;
		$limit = $this->cfg['item_per_page'];
		$offset = ((int)Arr::get($_GET, 'page', 1) - 1) * $limit;
		
		$listing_result = DB::query(Database::SELECT, "SELECT ld.listing->'price' as price, ld.listing->'quantity' as quantity, ld.id, l.title, l.img_count, l.uid, l.cid, l.object_type_id, ld.listing->'currency_code' AS currency FROM listing_data ld LEFT JOIN listing l ON ld.listing_id = l.id WHERE ld.status IN($status) AND (ld.listing->'user_id')::integer = :user_id ORDER BY id DESC LIMIT :limit OFFSET :offset")
		->param(':user_id', $user_id)
		->param(':limit', $limit)
		->param(':offset', $offset)
		->execute();

		$listing_count = DB::query(Database::SELECT, "SELECT COUNT(*) AS total_items FROM listing_data ld LEFT JOIN listing l ON ld.listing_id = l.id WHERE ld.status IN($status) AND (ld.listing->'user_id')::integer = :user_id")
		->param(':user_id', $user_id)
		->execute();
		$pagination = Pagination::factory(array(
			'query_string'   => 'page',
			'total_items'    => $listing_count[0]["total_items"],
			'items_per_page' => $limit,
			'style'          => 'classic',
			'auto_hide'      => TRUE
		));
		$view = View::factory(TEMPLATE . '/my/my_listing');
		$view->msg = $msg;
		$view->pagination = $pagination->render();
		$view->listing_result = $listing_result;
		$view->t = $type;
		$view->listing_period = $this->cfg["listing_period"];
		$view->cfg = $this->cfg;
		$this->template->title = I18n::get('my_listings');
		$this->template->content = $view;
	}	
}
