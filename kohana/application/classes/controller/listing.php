<?php
class Controller_Listing extends Controller_System
{
	public function before()
	{
		parent::before();
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

	
	function action_index()
	{
		$view = View::factory(TEMPLATE . '/listing');
		$this->template->content = $view;

		$uid = Request::current()->param('uid');
		$listing_type = Request::current()->param('listing_type');
		//for facebook user agent
	    if ($this->request->action == "details" AND preg_match('#facebookexternalhit#', $this->ua))	
        {
			$ad_id = (int) Arr::get($_GET, "id");
		    $ad_result = DB::query(Database::SELECT, "SELECT l.has_img, l.img_path, l.title, l.description, l.price, ce.has_price, ce.name, ai.data AS tn, c.currency_sign FROM listing a LEFT JOIN categories_entity ce ON l.cid = ce.id LEFT JOIN listing_img ai ON l.id = ai.ad_id LEFT JOIN country c ON l.country_id = c.id WHERE l.id = :ad_id AND l.id > '0' AND l.status = '1' ORDER BY ai.id LIMIT 1")
			->param(':ad_id', $ad_id)
			->execute();	
            if (count($ad_result) > 0)
			{
		        $this->template = View::factory(TEMPLATE . '/fb');
                $has_img = $ad_result[0]["has_img"];
                $img_path = $ad_result[0]["img_path"];
                $has_price = $ad_result[0]["has_price"];
                $price = $ad_result[0]["price"];
                $title = $ad_result[0]["title"];
				$this->template->type = $name = $ad_result[0]["name"];
                $this->template->title = $has_price == 1 ? "$name :: $title for $" . $price : "$name :: $title";
                $this->template->description = substr(str_replace("\n", " ", $ad_result[0]["description"]), 0, 297) . "...";
                $this->template->image = $has_img == 1 ? "http://" . $this->cfg['static_domain'] . "/files/$img_path/" . substr($ad_result[0]["tn"], 0, -1) . "1.jpg" : "";
                $this->template->has_img = $has_img;	
            }
		}
		else
		{
			$error = 0;
			$cid = 0;
			
			if ($listing_type == "st")
			{
			
				$listing_obj = DB::query(Database::SELECT, "SELECT l.id, object_type_id FROM listing l LEFT JOIN listing_data ld ON l.id = ld.listing_id WHERE l.uid = :uid AND ld.status = '1'")
				->param(':uid', $uid)
				->execute();
				if (count($listing_obj) > 0)
				{
					$object_type_id = $listing_obj[0]['object_type_id'];
					if ($object_type_id == 1)
					{
						$listing_obj = DB::query(Database::SELECT, "SELECT l.*, upo.option, hstore_to_json(ld.listing) as listing, u.id as user_id, u.phone, u.info->'rating' AS rating, c.name as country, up.preference, u.username, u.shipping_country, ic.name as item_condition, n.name AS neighborhood_name, z.city, z.state, z.zip, ce.has_img, ce.has_price, ce.has_quantity, ce.has_item_condition, ce.has_selling_option, g1.name AS geo1_name, g2.name AS geo2_name, cr.iso4217 AS currency FROM listing l LEFT JOIN listing_data ld ON l.id = ld.listing_id LEFT JOIN zip z ON (ld.listing->'zip_id')::integer = z.id LEFT JOIN neighborhood n ON (ld.listing->'neighborhood_id')::integer = n.id LEFT JOIN public.user u ON (ld.listing->'user_id')::integer = u.id LEFT JOIN categories_entity ce ON l.cid = ce.id LEFT JOIN country c ON (ld.listing->'country_id')::integer = c.id LEFT JOIN geo1 g1 ON (ld.listing->'geo1_id')::integer = g1.id LEFT JOIN geo2 g2 ON (ld.listing->'geo2_id')::integer = g2.id LEFT JOIN user_preference up ON u.id = up.user_id LEFT JOIN currency cr ON (ld.listing->'currency_id')::integer = cr.id LEFT JOIN item_condition ic ON (ld.listing->'item_condition_id')::integer = ic.id LEFT JOIN user_payment_option upo ON u.id = upo.user_id WHERE l.uid = :uid AND l.object_type_id = '1' AND l.status = '1'")
						->param(':uid', $uid)
						->execute();
						
						
						$listing = json_decode($listing_obj[0]['listing']);
						$view->condition_description = $listing->condition_description;
						$view->price = $listing->price;
						$view->price_usd = $listing->price_usd;
						$view->country_id = $listing->country_id;
						$view->location = $listing->location;
						$view->user_id = $listing->user_id;
						$view->currency_code = $listing->currency_code;
						$view->selling_option = $listing->selling_option;
						$view->shippable = $listing->shippable;
						$view->created = $listing->created;
						$view->quantity = $listing->quantity;
						$view->payment_option = $this->object_to_array(json_decode($listing_obj[0]['option']));
						$view->cfg_crypto = $this->cfg_crypto;
						$view->idd = $listing->idd;
						
						
						

					
						if ($listing->selling_option & 4 > 0)
						{

						}
						
						$view->item_condition = $listing_obj[0]['item_condition'];
						$view->has_price = $listing_obj[0]['has_price'];
						$view->has_quantity = $listing_obj[0]['has_quantity'];
						$view->has_selling_option = $listing_obj[0]['has_selling_option'];
						$view->title = $title = $listing_obj[0]['title'];
						$view->description = $listing_obj[0]['description'];
						$view->img_count = $listing_obj[0]['img_count'];
						$view->country = $listing_obj[0]['country'];
						$view->zip = $listing_obj[0]['zip'];
						$view->city = $listing_obj[0]['city'];
						$view->state = $listing_obj[0]['state'];
						$view->geo1_name = $listing_obj[0]['geo1_name'];
						$view->geo2_name = $listing_obj[0]['geo2_name'];
						$view->neighborhood_name = $listing_obj[0]['neighborhood_name'];
						$view->cid = $cid = $listing_obj[0]['cid'];
						$view->listing_id = $listing_id = $listing_obj[0]['id'];
						$view->rating = json_decode($listing_obj[0]['rating']);

					
						$data_result = DB::query(Database::SELECT, "SELECT ca1.name, cea.odr, ldu.ca_id, ldu.ca_value, ca1.id AS sequence FROM listing_data_user ldu LEFT JOIN categories_attribute ca1 ON ldu.ca_id = ca1.id LEFT JOIN categories_entity_attribute cea ON ldu.ca_id = cea.attribute_id WHERE ldu.listing_id = :listing_id AND cea.entity_id = :cid AND ca1.parent_id = 0 UNION SELECT ca2.name, cea.odr, ldu.ca_id, ldu.ca_value, ca2.id AS sequence FROM listing_data_user ldu LEFT JOIN categories_attribute ca2 ON ldu.ca_id = ca2.id LEFT JOIN categories_entity_attribute cea ON ldu.ca_id = cea.attribute_id WHERE ldu.listing_id = :listing_id AND cea.entity_id = :cid AND ca2.parent_id <> 0 UNION ALL SELECT ca1.name, cea.odr, cad.attribute_id, cad.value, ca1.id AS sequence FROM listing_data_system lds LEFT JOIN categories_attribute_data cad ON lds.ca_id = cad.attribute_id AND lds.ca_value = cad.id LEFT JOIN categories_attribute ca1 ON cad.attribute_id = ca1.id LEFT JOIN categories_entity_attribute cea ON ca1.id = cea.attribute_id WHERE lds.listing_id = :listing_id AND cea.entity_id = :cid AND ca1.parent_id = 0 UNION SELECT ca2.name, cea.odr, cad.attribute_id, cad.value, ca2.id AS sequence FROM listing_data_system lds LEFT JOIN categories_attribute_data cad ON lds.ca_id = cad.attribute_id AND lds.ca_value = cad.id LEFT JOIN categories_attribute ca2 ON cad.attribute_id = ca2.id LEFT JOIN categories_entity_attribute cea ON ca2.id = cea.attribute_id WHERE lds.listing_id = :listing_id AND cea.entity_id = :cid AND ca2.parent_id <> 0 ORDER BY sequence, odr")
						->param(':listing_id', $listing_id)
						->param(':cid', $cid)
						->execute();
						
						if ($view->img_count > 0)
						{
							$img_path = implode("/", str_split($uid, 2));
							$img_path = substr($img_path, 0, 8);
							$this->template->og_img = $this->cfg["www_domain"] . "/{$cfg["bucket_name"]}/{$this->cfg['size_small']}/$img_path/{$uid}_1.jpg";
							
						}
						$view->data_result = $data_result;
					}
					else
					{
						
						$listing_obj = DB::query(Database::SELECT, "SELECT id, title, description, cid, img_count, attribute FROM listing l where l.uid = :uid AND l.object_type_id = '2' AND l.status = '1'")
						->param(':uid', $uid)
						->execute();
						if (count($listing_obj) > 0)
						{
							$listing_id = $listing_obj[0]['id'];
							$view->has_price = 1;
							$view->title = $title = $listing->title;
							$view->description = $listing_obj[0]['description'];
							$view->cid = $cid = $listing_obj[0]['cid'];
							$view->img_count = $listing_obj[0]['img_count'];
							$view->listing_id = $listing_obj['0']['id'];
							$feature_obj = $view->feature_obj = json_decode($listing_obj[0]['attribute']);
							
							$view->listing_data_obj = DB::query(Database::SELECT, "SELECT u.username, u.phone AS phone, up.preference->>'show_phone_number' AS show_phone_number, u.id AS user_id, ld.listing->'offer_type_id' AS offer_type_id, ld.listing->'price' AS price_usd, ld.listing->'price' AS price, cr.iso4217 AS currency, ic.name as item_condition, ld.listing->'condition_description' AS condition_description, c.name AS country, ld.listing->'country_id' AS country_id, g1.name AS geo1_name, g2.name AS geo2_name, n.name AS neighborhood_name, z.zip AS zip, z.city AS city, z.state AS state, ld.listing->'location' AS location, ld.listing->'buy_url' AS buy_url FROM listing_data ld LEFT JOIN country c ON (ld.listing->'country_id')::integer = c.id LEFT JOIN geo1 g1 ON (ld.listing->'geo1_id')::integer = g1.id LEFT JOIN geo2 g2 ON (ld.listing->'geo2_id')::integer = g2.id LEFT JOIN neighborhood n ON (ld.listing->'neighborhood_id')::integer = n.id LEFT JOIN zip z ON (ld.listing->'zip_id')::integer = z.id LEFT JOIN item_condition ic ON (ld.listing->'item_condition_id')::integer = ic.id LEFT JOIN currency cr ON (ld.listing->'currency_id')::integer = cr.id LEFT JOIN public.user u ON (ld.listing->'user_id')::integer = u.id LEFT JOIN user_preference up ON u.id = up.user_id WHERE ld.listing_id = :listing_id AND ld.status = '1'")
							->param(':listing_id', $listing_id)
							->execute();
						
						}
					}
				}
				else
				{
					$error = 1;
				}
			}
			else
			{
				$error = 1;
			}
		
			if ($error == 0)
			{
				$view->nav = DB::query(Database::SELECT, "WITH RECURSIVE subcategories AS (SELECT id, parent_id FROM categories_entity WHERE id = :cid UNION ALL SELECT c.id, c.parent_id FROM categories_entity c JOIN subcategories sc ON c.id = sc.parent_id) SELECT id, name FROM categories_entity c WHERE c.id IN (SELECT id FROM subcategories GROUP BY id) ORDER BY parent_id")
				->param(':cid', $cid)
				->execute();
								
								
								
								
				$view->object_type_id = $object_type_id;
				$view->listing_obj = $listing_obj;
				
				
				
				$view->preference_obj = json_decode($listing_obj[0]["preference"]);
				
				$view->uid = $uid;
				
				$view->id = $listing_obj[0]["id"];
				$view->ip = self::get_ip();
				$view->username = $username;
				
				
				
				$view->country_obj = DB::query(Database::SELECT, "SELECT id, name FROM country WHERE status = '1' ORDER BY id")->execute();
				
				
				$view->cfg = $this->cfg;
				$view->cfg_currency = $this->cfg_currency;
				$this->template->title = $title;
				$this->template->description = substr($view->description, 0, 100) . '...';
				$this->template->content = $view;

			}
			else
			{
				$view = View::factory(TEMPLATE . '/special_info', array('header' => I18n::get('listing_not_found')));
				//used by setImg() in flex to detect current number of uploaded images
				$view->img_result = array();
				$view->msg = I18n::get('listing_expired_not_exist');
				
				$this->template->title = I18n::get('listing_not_found');
				$this->template->content = $view;
			}
		}
	
	
	}
}
