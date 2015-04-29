<?php
class Controller_Json extends Controller_System
{
	public function before()
	{
		parent::before();
		$this->session = Session::instance();
		$this->auth = Auth::instance();
		$this->auth->auto_login();
		$this->cfg = Kohana::$config->load('general.default');

		if ($this->auth->logged_in())
		{
			$this->user = $this->auth->get_user();
		}
		else
		{
			$array_action = array(
			"load_geo",
			"suggestions",
			"suggestions_tags" ,
			"suggestions_vendors",
			"newsletter_subscribe",
			"report",
			"load_comment",
			"reply_ad",
			"report_ad",
			"get_estimate",
			"get_store_address",
			"edit_message",
			);
			
			if (in_array($this->request->action(), $array_action))
			{
			}
			else
			{
				$this->request->redirect('https://' . $this->cfg['www_domain'] . '/account/login');
			}
		}
		include Kohana::find_file('libraries', 'Sphinxapi');
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
	
	function action_message_username()
	{
		if ($this->request->is_ajax())
		{
			$username = Arr::get($_GET, 'recipient_username');
			$my_username = $this->auth->get_user()->username;
			//use "false" instead of 0 here, otherwise jquery validation will break (output shown as weird text like "1"
			$valid = "false";
			if ($username != $my_username)
			{
				$username_obj = DB::query(Database::SELECT, "SELECT COUNT(id) AS total FROM public.user WHERE username = :username")
				->param(':username', $username)
				->execute();
				$this->template->title = '';
				$valid = $username_obj[0]['total'] == 1 ? "true" : "false";
			}
			echo $valid;
		}
	}
	
	function action_delete_digital_content()
	{
		if ($this->request->is_ajax())
		{
			$user_id = $this->auth->get_user()->id;
			$digital_content_id = Arr::get($_GET, 'id', 0);
			$delete_result = DB::query(Database::DELETE, "DELETE FROM digital_content WHERE id = :digital_content_id AND user_id = :user_id")
			->param(':digital_content_id', $digital_content_id)
			->param(':user_id', $user_id)
			->execute();
			echo $delete_result;
		}
	}
	
	function action_reply_ad()
	{
		//if (1 + 1 == 2)
		if ($this->request->is_ajax())
		{
			$error = 0;
			
			//$result = i18n::get("email_unsent");
			$ad_id = (int)Arr::get($_POST, "ad_id");
			$user_id = (int)$this->auth->get_user()->id;
			if ($ad_id == 0)
			{
				$error = 1;
			}
			
			$post = Validation::factory($_POST)
				->rule('msg', 'not_empty');
			if ($user_id == 0)
			{
				$post->rule('name', 'not_empty')
					->rule('email', 'not_empty')
					->rule('email', 'email')
					->rule('msg', 'not_empty');
			}
			if ($post->check()) 
			{
				$result = DB::query(Database::SELECT, "SELECT u.email AS to_email, a.title FROM public.user u LEFT JOIN ads a ON u.id = a.user_id WHERE a.id = :ad_id")
				->param(':ad_id', $ad_id)
				->execute(); 
				if (count($result) > 0)
				{
					$to_email = $result[0]['to_email'];
					$title = $result[0]['title'];
					$msg = Arr::get($_POST, "msg");
					if ($user_id == 0)
					{
						$from_email = $post['email'];
						$from_name = Arr::get($_POST, "name");
					}
					else
					{
						$result2 = DB::query(Database::SELECT, "SELECT u.email AS from_email FROM public.user u WHERE id = :user_id")
						->param(':user_id', $user_id)
						->execute();	
						$from_email = $result2[0]['from_email'];
						$from_name = $username;
					}
					
					header('Content-type: application/json');
					try
					{
						list($msec, $timestamp) = explode(" ", microtime());
						$msec = substr($msec, 2) . "0";
						$message_id = "$timestamp.$msec";
						$transport = Swift_MailTransport::newInstance();
						$mailer = Swift_Mailer::newInstance($transport);
						$message = Swift_Message::newInstance($title)
						->setFrom(array($from_email => $from_name))
						->setTo($to_email)
						->setReplyTo($from_email)
						//->setId($message_id)
						->setBody($msg . i18n::get("disclaimer_msg"));
						$mailer->send($message);
						$result = i18n::get("email_sent");
					}
					catch (Swift_ConnectionException $e)
					{
						$result = i18n::get("email_unsent");
					}
					catch (Swift_Message_MimeException $e)
					{
						$result = i18n::get("email_unsent");
					}
					catch (Swift_RfcComplianceException $e)
					{
						//Kohana::$log->add(Kohana::ERROR, $e->getMessage() . " " . $e->getFile());
						//Kohana::$log->write();
						$result = i18n::get("email_unsent");
					}
					
					
				}
			}
			else
			{
				$error = 1;
			}	
			$this->template->title = '';
			$this->template->content = $result;
			//$this->template->content = json_encode($result);
		}
		else
		{
			$this->template->title = '';
			$this->template = View::factory(TEMPLATE . '/blank');
			$this->template->content = "";
		}
	}
	
	function action_report_ad()
	{
		//if (1+1 == 2)
		if ($this->request->is_ajax())
		{
			$ad_id = (int)Arr::get($_GET, "ad_id");
			$reported_by = $this->auth->get_user()->id;
			if ($reported_by == "")
			{
				$reported_by = 19;
			}
			$flag = (int)Arr::get($_GET, "flag");
			$ip = Arr::get($_GET, "ip");
			DB::query(Database::INSERT, "INSERT INTO ads_flag(ad_id, reported_by, flag_type, ip) VALUES(:ad_id, :reported_by, :flag, :ip)")
			->param(':ad_id', $ad_id)
			->param(':reported_by', $reported_by)
			->param(':flag', $flag)
			->param(':ip', $ip)
			->execute();
			//header('Content-type: application/json');
			//$this->template->title = '';
			if (TEMPLATE == "fullsite")
			{
				$this->template->content = json_encode("ok");
			}
			else
			{
				$this->template->content = "ok";
			}
		}
		else
		{
			$this->template->title = '';
			$this->template = View::factory(TEMPLATE . '/blank');
			$this->template->content = "";
		}
	}
	
	function action_deleteValue()
	{
		if ($this->auth->get_user()->id == 19)
		{
			if ($this->request->is_ajax())
			{
				$vid = (int)Arr::get($_GET, 'vid');
				$aid_result = DB::query(Database::SELECT, "SELECT attribute_id FROM categories_attribute_data WHERE id = :vid")
				->param(':vid', $vid)
				->execute();
				$aid = $aid_result[0]["attribute_id"];
				DB::query(Database::DELETE, "DELETE FROM categories_attribute_data WHERE id = :vid")
				->param(':vid', $vid)
				->execute();
				DB::query(Database::UPDATE, "UPDATE categories_attribute SET data_count = data_count - 1 WHERE id = :aid")
				->param(':aid', $aid)
				->execute();
				header('Content-type: application/json');
				$this->template->title = '';
				$this->template->content = json_encode('ok');
			}
			else
			{
				$this->template->title = '';
				$this->template = View::factory(TEMPLATE . '/blank');
				$this->template->content = "";
			}
		}
	}
	
	function action_editValue()
	{
		if ($this->auth->get_user()->id == 19)
		{
			if ($this->request->is_ajax())
			{
				$vid = Arr::get($_GET, 'vid');
				$data = Arr::get($_GET, 'data');
				DB::query(Database::UPDATE, "UPDATE categories_attribute_data SET value = :data WHERE id = :vid")
				->param(':data', $data)
				->param(':vid', $vid)
				->execute();
				header('Content-type: application/json');
				$this->template->title = '';
				$this->template->content = json_encode('ok');
			}
			else
			{
				$this->template->title = '';
				$this->template = View::factory(TEMPLATE . '/blank');
				$this->template->content = "";
			}
		}
	}
	
	function action_addValue()
	{
		if ($this->auth->get_user()->id == 19)
		{
			if ($this->request->is_ajax())
			{
				$cid = Arr::get($_GET, 'cid');
				$aid = Arr::get($_GET, 'aid');
				$data = Arr::get($_GET, 'data');
				DB::query(Database::INSERT, "INSERT INTO categories_attribute_data(value, attribute_id) VALUES(:data, :aid)")
				->param(':data', $data)
				->param(':aid', $aid)
				->execute();
				$result = DB::query(Database::SELECT, "SELECT currval('categories_attribute_data_id_seq')")->execute();
				DB::query(Database::UPDATE, "UPDATE categories_attribute SET data_count = data_count + 1 WHERE id = :aid")
				->param(':aid', $aid)
				->execute();
				header('Content-type: application/json');
				$this->template->title = '';
				$this->template->content = json_encode($result[0]['currval']);
			}
			else
			{
				$this->template->title = '';
				$this->template = View::factory(TEMPLATE . '/blank');
				$this->template->content = "";
			}
		}
	}
	
	function action_load_citystate()
	{
		//if (1+1 == 2)
		if ($this->request->is_ajax())
		{
			$zip = sprintf("%05d", (int)Arr::get($_GET, 'zip'));
			$result = DB::query(Database::SELECT, "select city, state from zip where zip = :zip LIMIT 1")
			->param(':zip', $zip)
			->execute();
			$data = array();
			if (count($result) == 1)
			{
				$data[$result[0]['state']] = $result[0]['city'];
			}
			header('Content-type: application/json');
			$this->template->title = '';
			$this->template->content = json_encode($data);
		}
	}
	
	function action_neighborhood()
	{
		//if (1+1 == 2)
		if ($this->request->is_ajax())
		{
			$zip = sprintf("%05d", (int)Arr::get($_GET, 'zip'));
			$result = DB::query(Database::SELECT, "SELECT n.id, n.name FROM 
neighborhood n INNER JOIN zip z ON n.city = z.city AND n.state = z.state WHERE z.zip = :zip ORDER BY n.name")
->param(':zip', $zip)
->execute();
			$data = array();
			foreach ($result as $category)
			{
				//$data[$category['id']] = $category['name']; 
				$data[$category['name']] = $category['id']; 
			}
			header('Content-type: application/json');
			$this->template->title = '';
			$this->template->content = json_encode($data);
		}
		else
		{
			$this->template->title = '';
			$this->template = View::factory(TEMPLATE . '/blank');
			$this->template->content = "";
		}
	}
	
	function action_edit_message()
	{
		if ($this->request->is_ajax() AND $_POST)
		{
			$user_id = $this->auth->get_user()->id;
			$order_message_id = (int) Arr::get($_POST, 'id');
			$message = Arr::get($_POST, 'message');
			$result = DB::query(Database::UPDATE, "UPDATE order_message SET message = :message WHERE id = :id AND user_id = :user_id")
			->param(':message', $message)
			->param(':id', $order_message_id)
			->param(':user_id', $user_id)
			->execute();
		}
	}
	
	function action_get_estimate()
	{
		$is_sub_request = (int) ! $this->request->is_initial();
		//if (1+1 == 2)
		if ($is_sub_request == 1 OR $this->request->is_ajax())
		{
			if ($this->request->param('param') != '')
			{
				$explode = explode('/', $this->request->param('param'));
				$user_shipping_method_data_id = $explode[0];
				$payment_method = $explode[1];
				$seller_id = $explode[2];
				$country_id = $explode[3];
				$region_id = $explode[4];
			}
			else
			{
				$user_shipping_method_data_id = 0;
				$seller_id = (int) Arr::get($_GET, 'seller_id');
				$country_id = (int) Arr::get($_GET, 'country_id');
				$region_id = (int) Arr::get($_GET, 'region_id');
				$payment_method = Arr::get($_GET, 'pm', '');
				//$hs = Arr::get($_GET, 'hs');
				
				//for class/controller/listing page
				$uid = Arr::get($_GET, 'uid');
			}

			$shipping_fee = '0.00';
			$tax = '0.00';
			$data = array();
			$array_data = array('invalid' => 1);
			
			
			//this will be triggered eg: original listing currency is USD, user-selected currency is MYR, and after user clicked FTC and then money order as payment method, the currency shall now be MYR
			$cryptocurrency = 0;
			$cookie_preference = Cookie::get("preference", 0);
			if ($cookie_preference !== 0)
			{
				$cookie_preference = json_decode($cookie_preference);
				$currency_code = $cookie_preference->currency_code;
				$convert_currency = $cookie_preference->convert_currency;
				$cryptocurrency = $cookie_preference->cryptocurrency;
			}
			else
			{
				$currency_code = 'usd';
				$convert_currency = 0;
			}
			
			
			$cc = 0;
			if ($uid != '')
			{
				$listing_obj = DB::query(Database::SELECT, "SELECT hstore_to_json(ld.listing) as listing FROM listing l LEFT JOIN listing_data ld ON l.id = ld.listing_id WHERE l.uid = :uid AND l.status = '1'")
				->param(':uid', $uid)
				->execute();
				if (count($listing_obj) > 0)
				{
					//as call to action_get_estimate via listing page doesn't have user_shipping_method_data_id, we need the following block
					if ($cryptocurrency !== 0)
					{
						$payment_method = $cryptocurrency;
						$cc = 1;
					}
					
					$listing = json_decode($listing_obj[0]['listing']);
					$seller_id = $listing->user_id;
					$subtotal = $listing->price;
					$subtotal_usd = $listing->price_usd;
					$weight = $listing->item_weight;
					$weight_unit = $listing->weight_unit;
					//$hs = $listing->shippable;
					$array_weight_unit = array();
					//convert weight unit(kg, lbs and oz) to g
					$array_weight_unit[2] = 1000;
					$array_weight_unit[3] = 453.592;
					$array_weight_unit[4] = 28.3495;
					if ($weight_unit > 1)
					{
						$weight = (($weight / 1) * $array_weight_unit[$weight_unit]);
					}
					//print"<br>seller_id: $seller_id === $weight === $subtotal";
				}
				else
				{
					//need to be fixed later
					return 1;
				}
				$array_cart = array();
			}
			else
			{
			
				$array_cart = self::object_to_array(json_decode(Cookie::get("cart")));
				$subtotal = $array_cart[$seller_id]['tg'];
				$weight = $array_cart[$seller_id]['tw'];
			}
			//$from_currency = $array_cart[$seller_id]['f'];
			//$to_currency = $array_cart[$seller_id]['t'];


	
	
			//cart/action_review/ will trigger hs = 0,
			/*
			if (isset($hs) AND $hs == 0)
			{
				print"<br>herehere";
				$array_data['cc'] = $cc;
				$array_data['currency'] = $to_currency;
				$array_data['currency_rate'] = $this->cfg_currency['usd_' . $to_currency];
				$array_data['invalid'] = 0;
			}
			*/
			
			
			$preference_obj = DB::query(Database::SELECT, "SELECT preference->'weight_unit' AS weight_unit, preference->'currency_code' AS currency_code FROM user_preference WHERE user_id = :seller_id")
			->param(':seller_id', $seller_id)
			->execute();
			$weight_unit = $preference_obj[0]['weight_unit'];
			//if the user wants to overwrite the default seller currency with something else, eg: myr instead of cny + cryptocurrency listing is enable, we need to know the original listing currency, as from_currency + to_currency + currency_code doesn't hold any info of the orignal currency
			$seller_currency_code = $preference_obj[0]['currency_code'];
			//convert gram to weight unit(kg, lbs and oz)
			$array_weight_unit[1] = 1; //1 gram to gram
			$array_weight_unit[2] = 0.001; //1 gram to kg
			$array_weight_unit[3] = 0.00220462; //1 gram to lbs
			$array_weight_unit[4] = 0.035274; //1 gram to oz
			$weight = $weight * $array_weight_unit[$weight_unit];
			
			
			if ($payment_method == '')
			{
			}
			else
			{
			
				$array_currency = array('cash_on_delivery', 'bank_deposit', 'money_order', 'cashier_check', 'personal_check');

				//crypto currency?
				
				if (array_key_exists($payment_method, $this->cfg_crypto))
				{
					$to_currency = $payment_method;
					$cc = 1;
				}
			}
			
			
			
			if ($cc == 1)
			{
				//MYR -> FTC
				if ($convert_currency == 1)
				{
					$from_currency = $currency_code;
					$to_currency = $payment_method;
				}
				//CNY -> FTC
				else		
				{
					$from_currency = $seller_currency_code;
					$to_currency = $payment_method;
				}
			}
			else
			{
				//convert currency: convert from one currency to another
				if ($convert_currency == 1)
				{
					//$seller_currency_code is really needed here because $shipping_fee is in the seller-specified currency, eg: cny which is not known by the system at the moment. Without price_usd, we need $seller_currency_code so that we convert $shipping_fee to usd, $shipping_fee * $this->cfg_currency['cny_usd']
					$from_currency = $currency_code;
					$to_currency = $seller_currency_code;
				}
				else
				{
					$from_currency = $seller_currency_code;
					$to_currency = $seller_currency_code;
				}
			}
		
			$array_data['cc'] = $cc;
			$array_data['orig'] = $from_currency;
			$array_data['converted'] = $to_currency;
			$array_data['currency_rate'] = $this->cfg_currency['usd_' . $to_currency];
			$array_data['invalid'] = 0;
			
			
			//else if ($country_id > 0)
			//buyer who purchases digital goods won't pass country_id
			if ($country_id > 0)
			{
						
				//if user has not selected a shipping method yet, eg usps/fedex/dhl
				if ($user_shipping_method_data_id == 0)
				{
					$estimate_obj = DB::query(Database::SELECT, "SELECT usmd.id, usm.name, usm.data AS usm_data, usmd.data AS usmd_data FROM shipping_zone sz LEFT JOIN user_shipping_method_data usmd ON sz.id = usmd.shipping_zone_id LEFT JOIN user_shipping_method usm ON usmd.user_shipping_method_id = usm.id WHERE :country_id = ANY(sz.info) AND usmd.user_id = :seller_id UNION ALL SELECT usmd.id, usm.name, usm.data AS usm_data, usmd.data AS usmd_data FROM user_shipping_method_data usmd LEFT JOIN user_shipping_method usm ON usmd.user_shipping_method_id = usm.id WHERE usmd.user_id = :seller_id AND usmd.shipping_zone_id ISNULL")					
					->param(':country_id', $country_id)
					->param(':seller_id', $seller_id)
					->execute();
				}
				else
				{
					$estimate_obj = DB::query(Database::SELECT, "SELECT usmd.id, usm.name, usm.data AS usm_data, usmd.data AS usmd_data FROM user_shipping_method_data usmd LEFT JOIN user_shipping_method usm ON usmd.user_shipping_method_id = usm.id LEFT JOIN shipping_zone sz ON usmd.shipping_zone_id = sz.id WHERE usmd.id = :user_shipping_method_data_id AND usmd.user_id = :seller_id AND :country_id = ANY(sz.info) UNION ALL SELECT usmd.id, usm.name, usm.data AS usm_data, usmd.data AS usmd_data FROM user_shipping_method_data usmd LEFT JOIN user_shipping_method usm ON usmd.user_shipping_method_id = usm.id WHERE usmd.id = :user_shipping_method_data_id AND usmd.user_id = :seller_id AND usmd.shipping_zone_id ISNULL")
					->param(':user_shipping_method_data_id', $user_shipping_method_data_id)
					->param(':seller_id', $seller_id)
					->param(':country_id', $country_id)
					->execute();
				}

				$tax_obj = DB::query(Database::SELECT, "SELECT data FROM tax WHERE user_id = :seller_id")
				->param(':seller_id', $seller_id)
				->execute();
				if (count($tax_obj) == 1)
				{
					$array_tax = self::object_to_array(json_decode($tax_obj[0]['data']));
					if ($region_id > 0)
					{
						//tax rate for individual state/province/region in a country
						$region_name = $array_tax[$country_id]['name'];
						if ($array_tax[$country_id][$region_name][$region_id])
						{
							$tax_type = $array_tax[$country_id][$region_name][$region_id]['type'];
							$tax_rate = $array_tax[$country_id][$region_name][$region_id]['rate'];
						}
					}
					else
					{
						//tax rate for the whole country
						$tax_type = $array_tax[$country_id]['type'];
						$tax_rate = $array_tax[$country_id]['rate'];
					}
				}
				

				

				
				
				//after extracting info we want, remove it from array_cart
				if (count($array_cart) > 0)
				{
					unset($array_cart[$seller_id]['f']);
					unset($array_cart[$seller_id]['t']);
					unset($array_cart[$seller_id]['tg']);
					unset($array_cart[$seller_id]['tw']);
					unset($array_cart[$seller_id]['c']);
					unset($array_cart[$seller_id]['s']);
				}


				$array_estimate = array();
				$array_dayweek = array(1 => I18n::get('days'), 2 => I18n::get('weeks'));
				if (count($estimate_obj) > 0)
				{
					foreach ($estimate_obj as $result)
					{
						$array_estimate[$result['id']] = array();
						$array_estimate[$result['id']]['name'] = $result['name'];
						$array_estimate[$result['id']]['template'] = self::object_to_array(json_decode($result['usm_data']));
						$array_estimate[$result['id']]['data'] = self::object_to_array(json_decode($result['usmd_data']));
					
						$shipping_method_id = $result['id'];
						$shipping_method_name = $result['name'];
						$estimated_from = $array_estimate[$shipping_method_id]['data']['estimated_from'];
						$estimated_to = $array_estimate[$shipping_method_id]['data']['estimated_to'];
						$estimated_dayweek = $array_estimate[$shipping_method_id]['data']['estimated_dayweek'];
						//$shipping_method_id = key($array_estimate);
						$shipping_calculation = $array_estimate[$shipping_method_id]['template']['shipping_calculation'];
						$cst_type = $array_estimate[$shipping_method_id]['template']['cst_type'];
					
						/*
						print"<pre>";
						print_r($array_estimate);
						print"</pre>";
						
						$subtotal = 90;
						$shipping_method_id = 17;
						$shipping_calculation = 3;
						$cst_type = 2;
						$weight = 150;
						$tax_type = 1;
						*/
						
						//flat rate per item
						if ($shipping_calculation == 1)
						{
							$item_quantity = 0;
							if ($uid != '')
							{
								$item_quantity = 1;
							}
							else
							{
								if (count($array_cart) > 0)
								{
									foreach ($array_cart[$seller_id] as $ld_id => $result)
									{
										if ($result['s'] == 1)
										{
											$item_quantity += $result['q'];
										}
									}
								}
							}
							//flat rate fee type: flat rate or percentage. Unsed for flat rate per item because each item purchased by customer might have different prices, so percentage rate is not applicable (Fixed rate only)
							$fee = $array_estimate[$shipping_method_id]['data']['fee'];
							$shipping_fee = $fee * $item_quantity;
						}
						//flat rate per order
						else if ($shipping_calculation == 2)
						{
							$fee_type = $array_estimate[$shipping_method_id]['data']['fee_type'];
							$fee = $array_estimate[$shipping_method_id]['data']['fee'];
							if ($fee_type == 1)
							{
								$shipping_fee = $fee;
							}
							else
							{
								$shipping_fee = $subtotal * ($fee / 100);
							}
						}
						//custom shipping rate
						else if ($shipping_calculation == 3)
						{
							//weight-based
		
							if ($cst_type == 1)
							{
								foreach ($array_estimate[$shipping_method_id]['template'] as $key => $value)
								{
									if (is_int($key))
									{
										$min = $value['min'];
										$max = $value['max'];
										$fee_type = $array_estimate[$shipping_method_id]['data'][$key]['fee_type'];
										$fee = $array_estimate[$shipping_method_id]['data'][$key]['fee'];
										//weight based: flat rate
										if ($fee_type == 1)
										{
											if ($weight >= $min AND $weight <= $max)
											{
												$shipping_fee = $fee;
												break;
											}
											else
											{
												$shipping_fee = "0.00";
											}
										}
										//weight based: percentage rate
										else
										{
											if ($weight >= $min AND $weight <= $max)
											{
												$shipping_fee = $subtotal * ($fee / 100);
												break;
											}
											else
											{
												$shipping_fee = "0.00";
											}
										}
									}
								}
							}
							
							//price based
							else if ($cst_type == 2)
							{
								foreach ($array_estimate[$shipping_method_id]['template'] as $key => $value)
								{
									if (is_int($key))
									{
										$min = $value['min'];
										$max = $value['max'];
										$fee_type = $array_estimate[$shipping_method_id]['data'][$key]['fee_type'];
										$fee = $array_estimate[$shipping_method_id]['data'][$key]['fee'];
										//subtotal-based: flat rate
										if ($fee_type == 1)
										{
											
											if ($subtotal >= $min AND $subtotal <= $max)
											{
												$shipping_fee = $fee;
												break;
											}
											else
											{
												$shipping_fee = "0.00";
											}
										}
										//subtotal-based: percentage rate
										else
										{
											if ($subtotal >= $min AND $subtotal <= $max)
											{
												$shipping_fee = $subtotal * ($fee / 100);
												break;
											}
											else
											{
												$shipping_fee = "0.00";
											}
										}
									}
								}
							}
						}

						if ($tax_type == 1)
						{
							$tax = $subtotal * ($tax_rate / 100);
						}
						else
						{
							$tax = ($subtotal + $shipping_fee) * ($tax_rate / 100);
						}

						if ($cc == 1)
						{
							//MYR -> FTC
							if ($convert_currency == 1)
							{
								$array_data[$shipping_method_id]['shipping_orig'] = sprintf("%0.2f", $shipping_fee * $this->cfg_currency[$seller_currency_code . '_usd'] * $this->cfg_currency['usd_' . $from_currency]);
								$array_data[$shipping_method_id]['tax_orig'] = sprintf("%0.2f", $tax * $this->cfg_currency[$seller_currency_code . '_usd'] * $this->cfg_currency['usd_' . $from_currency]);
								$array_data[$shipping_method_id]['shipping_converted'] = sprintf("%0.5f", $shipping_fee * $this->cfg_currency[$seller_currency_code . '_usd'] * $this->cfg_currency['usd_' . $to_currency]);
								$array_data[$shipping_method_id]['tax_converted'] = sprintf("%0.5f", $tax * $this->cfg_currency[$seller_currency_code . '_usd'] * $this->cfg_currency['usd_' . $to_currency]);
							}
							//CNY -> FTC
							else		
							{
								$array_data[$shipping_method_id]['shipping_orig'] = sprintf("%0.2f", $shipping_fee);
								$array_data[$shipping_method_id]['tax_orig'] = sprintf("%0.2f", $tax);
								$array_data[$shipping_method_id]['shipping_converted'] = sprintf("%0.5f", $shipping_fee * $this->cfg_currency[$seller_currency_code . '_usd'] * $this->cfg_currency['usd_' . $to_currency]);
								$array_data[$shipping_method_id]['tax_converted'] = sprintf("%0.5f", $tax * $this->cfg_currency[$seller_currency_code . '_usd'] * $this->cfg_currency['usd_' . $to_currency]);
							}
						}
						else
						{
							//convert currency: convert from one currency to another
							if ($convert_currency == 1)
							{
								$array_data[$shipping_method_id]['shipping_orig'] = sprintf("%0.2f", $shipping_fee * $this->cfg_currency[$seller_currency_code . '_usd'] * $this->cfg_currency['usd_' . $from_currency]) ;
								$array_data[$shipping_method_id]['tax_orig'] = sprintf("%0.2f", $tax * $this->cfg_currency[$seller_currency_code . '_usd'] * $this->cfg_currency['usd_' . $from_currency]);
								$array_data[$shipping_method_id]['shipping_converted'] = sprintf("%0.2f", $shipping_fee * $this->cfg_currency[$seller_currency_code . '_usd'] * $this->cfg_currency['usd_' . $to_currency]);
								$array_data[$shipping_method_id]['tax_converted'] = sprintf("%0.2f", $tax * $this->cfg_currency[$seller_currency_code . '_usd'] * $this->cfg_currency['usd_' . $to_currency]);
							}
							else
							{
								$array_data[$shipping_method_id]['shipping_orig'] = sprintf("%0.2f", $shipping_fee);
								$array_data[$shipping_method_id]['tax_orig'] = sprintf("%0.2f", $tax);
								$array_data[$shipping_method_id]['shipping_converted'] = sprintf("%0.2f", $shipping_fee);
								$array_data[$shipping_method_id]['tax_converted'] = sprintf("%0.2f", $tax);
							}
						}
						
						//print "<br>after: F: $from_currency T: $to_currency C: $currency_code cc: $cc convert_currency: $convert_currency";

						$array_data[$shipping_method_id]['name'] = HTML::chars($shipping_method_name);
						$array_data[$shipping_method_id]['from'] = $estimated_from;
						$array_data[$shipping_method_id]['to'] = $estimated_to;
						$array_data[$shipping_method_id]['dayweek'] = $array_dayweek[$estimated_dayweek];
					}
					$array_data['cc'] = $cc;
					$array_data['orig'] = $from_currency;
					$array_data['converted'] = $to_currency;
					$array_data['currency_rate'] = $this->cfg_currency['usd_' . $to_currency];
					$array_data['invalid'] = 0;
				}
			}
			if ($is_sub_request == 1)
			{
				$this->auto_render = FALSE;
				$this->response->body(View::factory(TEMPLATE . '/blank')->set('content', json_encode($array_data)));
			}

			$this->template->title = '';
			$this->template->content = json_encode($array_data);
		}
		else
		{
			$this->template->title = '';
			$this->template = View::factory(TEMPLATE . '/blank');
			$this->template->content = "";
		}
	}
	
	function action_resend_tx_confirmation_mail()
	{
		$username = $this->user->username;
		$email = $this->user->email;
		//if (1 + 1 == 2)
		if ($this->request->is_ajax())
		{
			$id = (int)Arr::get($_GET, 'id');
            $transaction_obj = DB::query(Database::SELECT, "SELECT crypto, amount, txid, address FROM crypto_transaction WHERE account = :account AND id = :id AND status = '00'")
			->param(':account', $username)
			->param(':id', $id)
			->execute();
			if (count($transaction_obj) == 1)
			{
				$currency = $transaction_obj[0]['crypto'];
				$txid = trim($transaction_obj[0]['txid']);
				$net_withdrawal = 0 - $transaction_obj[0]['amount'];
				$address = $transaction_obj[0]['address'];
				$hash = $transaction_obj[0]['txid'];
				if (strlen($txid) == 50)
				{
					$amount_to_withdraw =  $net_withdrawal / 1e8 . ' ' . strtoupper($currency);;
					$email_message = sprintf(I18n::get('confirmation_of_withdrawal_message'), $username, $amount_to_withdraw, $address, $hash);
					$transport = Swift_MailTransport::newInstance();
					$mailer = Swift_Mailer::newInstance($transport);
					$message = Swift_Message::newInstance(I18n::get('confirmation_of_withdrawal_subject'))
					->setFrom(array($this->cfg["from_email"] => $this->cfg["site_name"] . ' Support'))
					->setTo($email)
					->setBody($email_message, 'text/plain');
					$mailer->send($message);
					
					$this->template->title = '';
					$this->template->content = json_encode(1);
				}
			}
		}
		else
		{
			$this->template->title = '';
			$this->template = View::factory(TEMPLATE . '/blank');
			$this->template->content = "";
		}
	}

	
	function action_get_store_address()
	{
		//if (1 + 1 == 2)
		if ($this->request->is_ajax())
		{
			$user_id = (int)Arr::get($_GET, 'seller_id');
            $store_obj = DB::query(Database::SELECT, "SELECT address->'address' AS store_address FROM user_store WHERE user_id = :user_id")
			->param(':user_id', $user_id)
			->execute();
			if (count($store_obj) > 0)
			{
				$address = HTML::chars($store_obj[0]['store_address']);
				$address_old = array('&lt;address&gt;', '&lt;/address&gt;', '&lt;b&gt;', '&lt;/b&gt;', '&lt;br&gt;');
				$address_new = array('<address>', '</address>', '<b>', '</b>', '<br>');
				$address = str_replace($address_old, $address_new, $address);
			}
			else
			{
				$address = '';
			}
			$this->template->title = '';
			$this->template->content = json_encode($address);
		}
		else
		{
			$this->template->title = '';
			$this->template = View::factory(TEMPLATE . '/blank');
			$this->template->content = "";
		}
	}

	function action_check_zip()
	{
		if ($this->request->is_ajax())
		{
			$zip = sprintf("%05d", (int)Arr::get($_GET, 'zip'));
            $result = DB::query(Database::SELECT, "SELECT COUNT(*) AS count FROM zip WHERE zip = :zip")
			->param(':zip', $zip)
			->execute();
			$this->template->title = '';
			$this->template->content = $result[0]['count'] > 0 ? 1 : 0;
		}
		else
		{
			$this->template->title = '';
			$this->template = View::factory(TEMPLATE . '/blank');
			$this->template->content = "";
		}
	}
	
	function action_loadcat()
	{

		if ($this->request->is_ajax())
		{
			

			$cat_id = Arr::get($_GET, 'cat_id');
			$data = array();

			$cl = new SphinxClient();
			$cl->SetServer($this->cfg['sphinx_host'], $this->cfg['sphinx_port']);
			$cl->SetArrayResult(true);
			$cl->SetLimits(0, 100);
			$cl->SetSelect("id, name, has_child, parent_id");
			$cl->SetFilter("parent_id", array($cat_id));
			$cl->SetSortMode(SPH_SORT_ATTR_ASC, 'name');
			$cl->AddQuery("", 'categories_entity');
			$results = $cl->RunQueries();
			if ($results === FALSE)
			{
				throw new Kohana_Exception('site_error, :error', array(':error' => $cl->GetLastError()));
			}
			else
			{
				if ($cl->GetLastWarning())
				{
					throw new Kohana_Exception('site_error, :error', array(':error' => $cl->GetLastWarning()));
				}
				if (isset($results[0]["matches"]) AND $results[0]["total_found"] > 0)
				{
					$suggestions = array();
					foreach ($results[0]["matches"] AS $doc)
					{
						$id = $doc["attrs"]["id"];
						$name = HTML::chars($doc["attrs"]["name"]);
						$has_child = $doc["attrs"]["has_child"];
						
						//if (TEMPLATE == "fullsite")
						//{
						//	$data[] = array($id, $name, $has_child);
						//}
						//else
						//{
							//forjquerymobile
							$data[] = array("id" => $id, "name" => $name, "has_child" => $has_child);
						//}
					}	
				}
			}
			$this->template->title = '';
			header('Content-type: application/json');
			$this->template = View::factory(TEMPLATE . '/blank');
			$this->template->content = json_encode($data);
		}

		else
		{
			$this->template->title = '';
			$this->template = View::factory(TEMPLATE . '/blank');
			$this->template->content = "";
		}

	}
	
	function action_loadcat_admin()
	{
		if ($this->request->is_ajax())
		{
			$cat_id = Arr::get($_GET, 'cat_id');
			$result = DB::query(Database::SELECT, "SELECT ce.id, ce.name, ce.has_child, COUNT(cea.odr) AS count FROM categories_entity ce LEFT JOIN categories_entity_attribute cea ON ce.id = cea.entity_id WHERE parent_id = :cat_id GROUP BY ce.id, ce.name, ce.has_child ORDER BY ce.name")
			->param(':cat_id', $cat_id)
			->execute();
			$data = array();
			foreach ($result as $category)
			{
				$data[] = array($category['id'], $category['name'], $category['has_child'], $category['count']); 
			}
			$this->template->title = '';
			header('Content-type: application/json');
			$this->template->content = json_encode($data);
		}
		else
		{
			$this->template->title = '';
			$this->template = View::factory(TEMPLATE . '/blank');
			$this->template->content = "";
		}
	}
	
	function action_newsletter_subscribe()
	{
		header('Content-type: application/json');
		$this->template = View::factory(TEMPLATE . '/blank');
		$this->template->title = '';
		if ($this->request->is_ajax())
		{
			$email = Arr::get($_GET, 'email');
			if (strlen($email) > 0)
			{
				$result = DB::query(Database::SELECT, "SELECT email FROM newsletters WHERE email = :email")
				->param(':email', $email)
				->execute();
				if (count($result) > 0)
				{
					$data = '<span class="error">' . I18n::get('email_in_use') . '</span>';
				}
				else
				{
					$ip = ( isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '127.0.0.1' );
					$ua = ( isset($_SERVER['HTTP_USER_AGENT']) AND $_SERVER['HTTP_USER_AGENT'] ) ? $_SERVER['HTTP_USER_AGENT'] : '';
					DB::query(Database::INSERT, "INSERT INTO newsletters (email, ip, ua) VALUES (:email, :ip, :ua)")
					->param(':email', $email)
					->param(':ip', $ip)
					->param(':ua', $ua)
					->execute();
					$data = '<span class="info">' . I18n::get('subscription_success') . '</span>';
				
					// temporary solution to track new members
					$transport = Swift_MailTransport::newInstance();
					$mailer = Swift_Mailer::newInstance($transport);
					$message = Swift_Message::newInstance('Newsletter Signup')
					->setFrom(array($this->cfg["noreply_email"] => $this->cfg["site_name"] . ' Mailer'))
					->setTo($this->cfg["from_email"])
					->setBody($email, 'text/plain');
					$mailer->send($message);
				}
			}
			$this->template->content = $data;
		}
		
		else
		{
			$this->template->content = "";
		}
		
	}
	
	function action_check_merchant_id()
	{
		header('Content-type: application/json');
		$this->template = View::factory(TEMPLATE . '/blank');
		$this->template->title = '';
		if ($this->request->is_ajax())
		{
			$merchant_id = (int) Arr::get($_GET, 'merchant_id');
			if (strlen($merchant_id) > 0)
			{
				$result = DB::query(Database::SELECT, "SELECT merchant_id FROM vendors WHERE merchant_id = :merchant_id AND merchant_id <> '0'")
				->param(':merchant_id', $merchant_id)
				->execute();
				if (count($result) > 0)
				{
					$data = 1;
				}
				else
				{
					$data = 0;
				}
			}
			$this->template->content = $data;
		}
		
		else
		{
			$this->template->content = "";
		}
	}	
	
	function action_suggestions()
	{
		if ($this->request->is_ajax())
		{
			$keyword = Arr::get($_REQUEST, 'l', '');
			/*
			$pos = strpos($keyword, ',');
			if ($pos === false)
			{
				$result = DB::query(Database::SELECT, "SELECT DISTINCT city, state FROM zip WHERE city ILIKE '$keyword%' ORDER BY city, state LIMIT 10")->execute();
			}
			else
			{
				$arr = explode(',', $keyword);
				$city = $arr[0];
				$state = $arr[1];
				$result = DB::query(Database::SELECT, "SELECT DISTINCT city, state FROM zip WHERE city ILIKE '$city%' AND state ILIKE '$state%' ORDER BY city, state LIMIT 10")->execute();
			}
			$suggestions = array();
			foreach ($result as $location)
			{
				$suggestion = $location['city'] . ', ' . $location['state'];
				if (false !== stripos($suggestion, $keyword))
				{
					$match = preg_replace('/' . preg_quote($keyword) . '/i',
			"<strong>$0</strong>", $suggestion, 1);	
				}
				$suggestions[] = "<li>$match</li>";
			}
			echo "<ul>\n" . join("", $suggestions) . "</ul>\n";
			*/
			$cl = new SphinxClient();
			$cl->SetServer($this->cfg['sphinx_host'], $this->cfg['sphinx_port']);
			$cl->SetArrayResult(true);
			$cl->SetLimits(0, 10);
			$cl->SetSelect("city, state");
			$cl->AddQuery("$keyword*", 'zip');
			$results = $cl->RunQueries();
			if ($results === FALSE)
			{
				throw new Kohana_Exception('site_error, :error', array(':error' => $cl->GetLastError()));
			}
			else
			{
				if ($cl->GetLastWarning())
				{
					throw new Kohana_Exception('site_error, :error', array(':error' => $cl->GetLastWarning()));
				}
				if (isset($results[0]["matches"]) AND $results[0]["total_found"] > 0)
				{
					$suggestions = array();
					foreach ($results[0]["matches"] AS $doc)
					{
						$city = $doc["attrs"]["city"];
						$state = $doc["attrs"]["state"];
						$suggestion = $city . ', ' . $state;
						if (false !== stripos($suggestion, $keyword))
						{
							$match = preg_replace('/' . preg_quote($keyword) . '/i',
					"<strong>$0</strong>", $suggestion, 1);	
						}
						else
						{
							$match = "error";
						}
						$suggestions[] = "<li>$match</li>";
					}
					$this->template->title = '';
					echo "<ul>\n" . join("", $suggestions) . "</ul>\n";
				}
			}
		}
		else
		{
			$this->template->title = '';
			$this->template = View::factory(TEMPLATE . '/blank');
			$this->template->content = "";
		}
	}
	
	function action_suggestions_vendors()
	{
		if ($this->request->is_ajax())
		{
			$keyword = Arr::get($_REQUEST, 'q', '');
			$cl = new SphinxClient();
			$cl->SetServer($this->cfg['sphinx_host'], $this->cfg['sphinx_port']);
			$cl->SetArrayResult(true);
			$cl->SetLimits(0, 10);
			$cl->SetSelect("name, domain");
			$cl->SetSortMode(SPH_SORT_ATTR_ASC, 'name');
			$cl->setFilter("active", array(1));
			$cl->AddQuery("$keyword*", 'vendors');
			$results = $cl->RunQueries();
			if ($results === FALSE)
			{
				throw new Kohana_Exception('site_error, :error', array(':error' => $cl->GetLastError()));
			}
			else
			{
				if ($cl->GetLastWarning())
				{
					throw new Kohana_Exception('site_error, :error', array(':error' => $cl->GetLastWarning()));
				}
				if (isset($results[0]["matches"]) AND $results[0]["total_found"] > 0)
				{
					$suggestions = array();
					foreach ($results[0]["matches"] AS $doc)
					{
						$suggestion = $name = $doc["attrs"]["name"];
						$domain = $doc["attrs"]["domain"];
						/*
						if (false !== stripos($suggestion, $keyword))
						{
							$match = preg_replace('/' . preg_quote($keyword) . '/i',
					"<strong>$0</strong>", $suggestion, 1);	
						}
						else
						{
							$match = "error";
							$match = "";
						}
						$suggestions[] = "<li>$match</li>";
						*/
						$suggestions[] = "<li><a href=\"http://{$this->cfg['www_domain']}/hub/$domain\">$suggestion</a></li>";
					}
					$this->template->title = '';
					echo "<ul>\n" . join("", $suggestions) . "</ul>\n";
				}
			}
		}
		else
		{
			$this->template->title = '';
			$this->template = View::factory(TEMPLATE . '/blank');
			$this->template->content = "";
		}
	}
	
	function action_suggestions_tags()
	{
		if ($this->request->is_ajax())
		{
			$keyword = Arr::get($_REQUEST, 'name', '');
			$cl = new SphinxClient();
			$cl->SetServer($this->cfg['sphinx_host'], $this->cfg['sphinx_port']);
			$cl->SetArrayResult(true);
			$cl->SetLimits(0, 10);
			$cl->SetSelect("name");
			$cl->SetSortMode(SPH_SORT_ATTR_ASC, 'name');
			$cl->AddQuery("$keyword*", 'tags');
			$results = $cl->RunQueries();
			if ($results === FALSE)
			{
				throw new Kohana_Exception('site_error, :error', array(':error' => $cl->GetLastError()));
			}
			else
			{
				if ($cl->GetLastWarning())
				{
					throw new Kohana_Exception('site_error, :error', array(':error' => $cl->GetLastWarning()));
				}
				if (isset($results[0]["matches"]) AND $results[0]["total_found"] > 0)
				{
					$suggestions = array();
					foreach ($results[0]["matches"] AS $doc)
					{
						$id = $doc["id"];
						$suggestion = $name = $doc["attrs"]["name"] . " | $id";
						if (false !== stripos($suggestion, $keyword))
						{
							$match = preg_replace('/' . preg_quote($keyword) . '/i',
					"<strong>$0</strong>", $suggestion, 1);	
						}
						else
						{
							$match = "error";
						}
						$suggestions[] = "<li>$match</li>";
					}
					$this->template->title = '';
					echo "<ul>\n" . join("", $suggestions) . "</ul>\n";
				}
			}
		}
		else
		{
			$this->template->title = '';
			$this->template = View::factory(TEMPLATE . '/blank');
			$this->template->content = "";
		}
	}
	
	

	
	
	
	function find_subarray_key($array, $needle)
	{
		if (count($data) > 0)
		{
			foreach($array as $haystack)
			{
				if(array_key_exists($needle, $key)) 
				{
					return true;
				}
			}
		}
		return false;
	}


	
	function action_load_geo()
	{
		$country = Arr::get($_GET, "country");
		//if (1 + 1 == 2)
		if ($this->request->is_ajax())
		{
			
			$geo1_result = DB::query(Database::SELECT, "SELECT g1.id AS g1_id, g1.name FROM geo g LEFT JOIN country c ON g.country_id = c.id LEFT JOIN geo1 g1 ON g.geo1_id = g1.id WHERE c.id = :country and geo2_id IS NULL ORDER BY name")
			->param(':country', $country)
			->execute();
			
			$geo2_result = DB::query(Database::SELECT, "SELECT g.geo1_id AS g1_id, g2.id AS g2_id, g2.name FROM geo g LEFT JOIN country c ON g.country_id = c.id LEFT JOIN geo2 g2 ON g.geo2_id = g2.id WHERE c.id = :country AND geo2_id IS NOT NULL ORDER BY g1_id, g2.name")
			->param(':country', $country)
			->execute();
			
			$data = array();
			$index_array = array();
			foreach ($geo1_result as $result)
			{
				$data[][0] = HTML::chars($result["name"]) . ":" . $result["g1_id"];
				$index_array[$result["g1_id"]] = count($data) - 1;
			}
			
			foreach ($geo2_result as $result)
			{
				$data[$index_array[$result["g1_id"]]][] = array("i" => HTML::chars($result["name"]) . ":" .  $result["g2_id"]);
			}

			/*
			by default jquery json sorts the json data by id, eg:
			1 - kuala lumpur
			2 - johor
			3 - serawak
			4 - terengganu
			so this will break our sql sorting by state name,
			to solve the problem move the state name and id into the second-level of multidimensional array
			
			output: note that i is simply a dummy value to fit with the key:value rule
			Array
			(
				[0] => Array
					(
						[0] => Johor:7
						[1] => Array
							(
								[i] => Ayer Baloi:64
							)

						[2] => Array
							(
								[i] => Ayer Hitam:65
							)

						[3] => Array
							(
								[i] => Bakri:66
							)
			*/
			header('Content-type: application/json');
			$this->template->content = json_encode($data);
		}
		else
		{
			$domain = "http://" . $this->cfg['static_domain'];
			$this->request->redirect($domain);
		}
	}
	
	function action_load_geostore()
	{
		$country_id = (int)Arr::get($_GET, 'id');
		//if (1+1 == 2)
		if ($this->request->is_ajax())
		{
			
			$geo1_result = DB::query(Database::SELECT, "SELECT g1.id AS g1_id, g1.name FROM geo g LEFT JOIN country c ON g.country_id = c.id LEFT JOIN geo1 g1 ON g.geo1_id = g1.id WHERE c.id = :country_id and geo2_id IS NULL ORDER BY name")
			->param(':country_id', $country_id)
			->execute();
			$geo2_result = DB::query(Database::SELECT, "SELECT g.geo1_id AS g1_id, g2.id AS g2_id, g2.name FROM geo g LEFT JOIN country c ON g.country_id = c.id LEFT JOIN geo2 g2 ON g.geo2_id = g2.id WHERE c.id = :country_id AND geo2_id IS NOT NULL ORDER BY g1_id, g2.name")
			->param(':country_id', $country_id)
			->execute();
			$data_geo = array();
			$index_array = array();
			foreach ($geo1_result as $result)
			{
				//$data[$result["g1_id"]][0] = $result["name"];
				$data_geo[][0] = $result["name"] . ":" . $result["g1_id"];
				
				//$data["g1"][0] = $result["name"] . ":" . $result["g1_id"];
				$index_array[$result["g1_id"]] = count($data_geo) - 1;
			}
			
			foreach ($geo2_result as $result)
			{
				//$data[$result["g1_id"]][] = array("i" => $result["name"] . ":" .  $result["g2_id"]);
				$data_geo[$index_array[$result["g1_id"]]][] = array("i" => $result["name"] . ":" .  $result["g2_id"]);
			}
			$data[0] = $data_geo;
			
			/*
			$tax_region_obj = DB::query(Database::SELECT, "SELECT data, geo_info_id FROM tax_region WHERE country_id = '$country_id'")->execute();
			if (count($tax_region_obj) > 0)
			{
				$geo_info_id = strtolower($tax_region_obj[0]['geo_info_id']);
				$array_tax_region_data = self::object_to_array(json_decode($tax_region_obj[0]['data']));
			}
			*/
						
			$country_geo_info_obj = DB::query(Database::SELECT, "SELECT cgi.geo_info_id, cgi.odr, cgi.compulsory_field, cgi.has_geo, gi.name, gi.short_name FROM country_geo_info cgi LEFT JOIN geo_info gi ON cgi.geo_info_id = gi.id WHERE country_id = :country_id")
			->param(':country_id', $country_id)
			->execute();
			foreach ($country_geo_info_obj as $result)
			{
				/*
				if ($geo_info_id == $result['geo_info_id'])
				{
					$data[] = array($result['odr'], $result['geo_info_id'], $result['name'], $result['short_name'], $result['compulsory_field'], $array_tax_region_data);
				}
				else
				{
				*/
					$data[] = array($result['odr'], $result['geo_info_id'], HTML::chars($result['name']), $result['short_name'], $result['compulsory_field'], $result['has_geo']);
				//}
			}
			header('Content-type: application/json');
			$this->template->content = json_encode($data);
		}
		else
		{
			$domain = "http://" . $this->cfg['static_domain'];
			$this->request->redirect($domain);
		}
	}
	

	function action_index()
	{
		$this->template->title = '';
		$this->template = View::factory(TEMPLATE . '/blank');
		$this->template->content = "";
	}

}
