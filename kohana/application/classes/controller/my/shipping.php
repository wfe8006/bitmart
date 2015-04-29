<?php
class Controller_My_Shipping extends Controller_System
{
	public function before()
	{
		parent::before();
		$this->session = Session::instance();
		$this->auth = Auth::instance();
		if ($this->auth->logged_in())
		{
			$this->user = $this->auth->get_user();
		}
		else
		{
			Request::current()->redirect('https://' . $this->cfg['www_domain'] . '/account/auth');
		}
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
	
	function check_shipping_zone(Validation $post = NULL)
	{
		$user_id = $this->auth->get_user()->id;	
		$shipping_zone_obj = DB::query(Database::SELECT, "SELECT id FROM shipping_zone WHERE user_id = :user_id AND name = :name")
		->param(':user_id', $user_id)
		->param(':name', Arr::get($_POST, 'name'))
		->execute();
		if (count($shipping_zone_obj) > 0)
		{
			$post->error('name', 'default');
		}
	}
	
	function check_shipping_zone_edit(Validation $post = NULL)
	{
		$shipping_zone_id = (int)Arr::get($_POST, 'id');
		$user_id = $this->auth->get_user()->id;	
		$shipping_zone_obj = DB::query(Database::SELECT, "SELECT id FROM shipping_zone WHERE user_id = :user_id AND name = :name AND id != :shipping_zone_id")
		->param(':user_id', $user_id)
		->param(':name', Arr::get($_POST, 'name'))
		->param(':shipping_zone_id', $shipping_zone_id)
		->execute();
		if (count($shipping_zone_obj) > 0)
		{
			$post->error('name', 'default');
		}
	}
	
	function check_shipping_zone_edit_cb(Validation $post = NULL)
	{
		if (count(Arr::get($_POST, 'cb')) == 0)
        {
            $post->error('cb', 'default');
        }
	}
	
	function check_shipping_zone_count(Validation $post = NULL)
	{
		if (count(Arr::get($_POST, 'sz')) == 0)
        {
            $post->error('shipping_zone', 'default');
        }
	}
	
	public function action_shipping_zone()
	{
		$view = View::factory(TEMPLATE . '/my/shipping_zone');
		$view->cfg = $this->cfg;
		$this->template->content = $view;
	}
	
	public function action_shipping_zone_add($shipping_zone_id = 0)
	{
		$user_id = $this->auth->get_user()->id;	
		$view = View::factory(TEMPLATE . '/my/shipping_zone_add');
		$view->array_country = array();
		if ($shipping_zone_id == 0)
		{	
			$action = 'add';
			$view->title = I18n::get('add_shipping_zone');
		}
		else
		{
			$action = 'edit';
			$view->title = I18n::get('edit_shipping_zone');
			$view->id = $shipping_zone_id;
		}
		if ($_POST)
		{
			$cb = $_POST['cb'];
			$array_country = array();
			if (count($cb) > 0)
			{
				foreach ($cb as $index => $value)
				{
					$array_country[] = (int)$value;
				}
			}
			$list_country = '{' . implode(',', $array_country) . '}';
			$post = Validation::factory($_POST)
			->rule('name', 'not_empty')
			->rule('cb', array($this, 'check_shipping_zone_edit_cb'), array(':validation', ':field', 'cb'));

			if ($action == 'add')
			{
				$post->rule('name', array($this, 'check_shipping_zone'), array(':validation', ':field', 'name'));
			}
			else
			{
				$post->rule('name', array($this, 'check_shipping_zone_edit'), array(':validation', ':field', 'name'));

			}

			if ($post->check())
			{
				DB::query('NULL', 'BEGIN')->execute();
				if ($action == 'add')
				{
					$result = DB::query(Database::SELECT, "INSERT INTO shipping_zone(user_id, name, info) VALUES(:user_id, :name, :list_country)")
					->param(':user_id', $user_id)
					->param(':name', Arr::get($_POST, 'name'))
					->param(':list_country', $list_country)
					->execute();
					if ( ! $result)
					{
						DB::query('NULL', 'ROLLBACK')->execute();
						throw new Kohana_Exception('site_error ');
					}
					$s = 'a';
				}
				else
				{
					$result = DB::query(Database::SELECT, "UPDATE shipping_zone SET name = :name, info = :list_country WHERE user_id = :user_id AND id = :shipping_zone_id")
					->param(':name', Arr::get($_POST, 'name'))
					->param(':list_country', $list_country)
					->param(':user_id', $user_id)
					->param(':shipping_zone_id', $shipping_zone_id)
					->execute();
					if ( ! $result)
					{
						DB::query('NULL', 'ROLLBACK')->execute();
						throw new Kohana_Exception('site_error ');
					}
					$this->update_shipping_country($user_id);
					$s = 'u';
				}
				DB::query('NULL', 'COMMIT')->execute();
				Request::current()->redirect("/my/shipping?s=$s");
			}
			
			$array_country = array();
			if (count($array_country) > 0)
			{
				foreach ($cb as $index => $value)
				{
					array_push($array_country, $value);
				}
			}
			$view->name = Arr::get($_POST, 'name');
			$view->array_country = $array_country;

			$view->post = $post;
			$view->errors = $post->errors('my/shipping_zone_add');
			
		}
		else
		{
			if ($action == 'edit')
			{
				$shipping_zone_obj = DB::query(Database::SELECT, "SELECT name, array_to_json(info) AS info FROM shipping_zone WHERE user_id = :user_id AND id = :shipping_zone_id ORDER BY name")
				->param(':user_id', $user_id)
				->param(':shipping_zone_id', $shipping_zone_id)
				->execute();
				if (count($shipping_zone_obj) > 0)
				{
					$info = $shipping_zone_obj[0]['info'];
					$array_country = json_decode($info);
				}
				
				$view->name = $shipping_zone_obj[0]['name'];
				$view->array_country = $array_country;
			}
		}
		
		$country_region_obj = DB::query(Database::SELECT, "SELECT * FROM country_region ORDER BY id")->execute();
		$array_region = array();
		foreach ($country_region_obj as $result)
		{
			$array_region[$result['id']] = $result['name'];
		}
		$country_obj = DB::query(Database::SELECT, "SELECT id, name, country_region_id FROM country WHERE status = '1' ORDER BY country_region_id, name")->execute();
		$view->action = $action;
		$view->array_region = $array_region;
		$view->country_obj = $country_obj;	
		$view->cfg = $this->cfg;		
		$this->template->content = $view;
		
		

		
	}
	
	public function action_shipping_zone_edit()
	{
		$shipping_zone_id = (int)Arr::get($_GET, 'id', Arr::get($_POST, 'id'));
		self::action_shipping_zone_add($shipping_zone_id);
	}
	
	public function action_shipping_zone_edit1()
	{
		$user_id = $this->auth->get_user()->id;
		$view = View::factory(TEMPLATE . '/my/shipping_zone_edit');
		$shipping_zone_id = (int)Arr::get($_GET, 'id', Arr::get($_POST, 'id'));
		
		if ($_POST)
		{
			$cb = $_POST['cb'];
			$array_country = array();

			if (count($cb) > 0)
			{
				foreach ($cb as $index => $value)
				{
					$array_country[] = (int)$value;
				}
			}
			$list_country = '{' . implode(',', $array_country) . '}';
			$post = Validation::factory($_POST)
				->rule('name', 'not_empty')
				->rule('name', array($this, 'check_shipping_zone_edit'), array(':validation', ':field', 'name'))
				->rule('cb', array($this, 'check_shipping_zone_edit_cb'), array(':validation', ':field', 'cb'));
			if ($post->check())
			{
				DB::query('NULL', 'BEGIN')->execute();
				
	
				$result = DB::query(Database::SELECT, "UPDATE shipping_zone SET name = :name, info = :list_country WHERE user_id = :user_id AND id = :shipping_zone_id")
				->param(':name', Arr::get($_POST, 'name'))
				->param(':list_country', $list_country)
				->param(':user_id', $user_id)
				->param(':shipping_zone_id', $shipping_zone_id)
				->execute();
				if ( ! $result)
				{
					DB::query('NULL', 'ROLLBACK')->execute();
					throw new Kohana_Exception('site_error ');
				}
				$this->update_shipping_country($user_id);

				DB::query('NULL', 'COMMIT')->execute();
				Request::current()->redirect("/my/shipping/shipping_zone_edit?id=$shipping_zone_id&s=1");
			}
			else
			{
				$view->post = $post;
				$view->errors = $post->errors('my/shipping_zone_add');
			}
			
			
			$array_country = array();
			foreach ($cb as $index => $value)
			{
				array_push($array_country, $value);
			}
			$view->name = Arr::get($_POST, 'name');
			$view->array_country = $array_country;
		}
		else
		{
			if ($shipping_zone_id != 0)
			{
				$shipping_zone_obj = DB::query(Database::SELECT, "SELECT name, array_to_json(info) AS info FROM shipping_zone WHERE user_id = :user_id AND id = :shipping_zone_id ORDER BY name")
				->param(':user_id', $user_id)
				->param(':shipping_zone_id', $shipping_zone_id)
				->execute();
				if (count($shipping_zone_obj) > 0)
				{
					$info = $shipping_zone_obj[0]['info'];
					$array_country = json_decode($info);
				}
				
				$view->name = $shipping_zone_obj[0]['name'];
				$view->array_country = $array_country;
			}
		}
		
		$success = Arr::get($_GET, 's', 0);
		if ($success == 1)
		{
			$view->msg = I18n::get('record_updated');
		}
		
		$country_region_obj = DB::query(Database::SELECT, "SELECT * FROM country_region ORDER BY id")->execute();
		$array_region = array();
		foreach ($country_region_obj as $result)
		{
			$array_region[$result['id']] = $result['name'];
		}
		$view->array_region = $array_region;
		$country_obj = DB::query(Database::SELECT, "SELECT id, name, country_region_id FROM country WHERE status = '1' ORDER BY country_region_id, name")->execute();
		$view->country_obj = $country_obj;
		$view->id = $shipping_zone_id;	
		$view->cfg = $this->cfg;
		$this->template->content = $view;
		
	}
	
	private function dec2bin_i($decimal_i)
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
	
	private function update_shipping_country($user_id)
	{
		
		$shipping_obj = DB::query(Database::SELECT, "SELECT usmd.shipping_zone_id, sz.info FROM user_shipping_method_data usmd LEFT JOIN shipping_zone sz ON usmd.shipping_zone_id = sz.id WHERE usmd.user_id = :user_id GROUP BY usmd.shipping_zone_id, sz.info ORDER BY usmd.shipping_zone_id")
		->param(':user_id', $user_id)
		->execute();
		if ( ! $shipping_obj)
		{
			DB::query('NULL', 'ROLLBACK')->execute();
			throw new Kohana_Exception('site_error ');
		}
		
		$shipping_count = count($shipping_obj);
		if ($shipping_count > 0)
		{
			//if seller offers shipping to worldwide, shipping_zone_id will be ''
			$empty = 0;
			$total = 0;
			if ($shipping_obj[$shipping_count - 1]['shipping_zone_id'] == '')
			{
				$shipping_country = 'worldwide';
				include  __DIR__ . "/../../classes/controller/country_list.php"; 

				ksort($country_list);
				
				foreach ($country_list as $index => $name)
				{
					$current = bcpow(2, $index);
					$total = bcadd($total, $current);
				}
				$country_binary = self::dec2bin_i($total);
			}
			else
			{
				$array_country = array();
				foreach ($shipping_obj as $record)
				{
					$info = substr($record['info'], 1, -1);
					if ($info == '')
					{
						$empty = 1;
					}
					else
					{
						$array_info = explode(',', $info);
						foreach ($array_info as $key => $country_id)
						{
							if( ! in_array($country_id, $array_country, true))
							{
								array_push($array_country, $country_id);
								$current = bcpow(2, $country_id);
								$total = bcadd($total, $current);
							}
						}
						
					}
				}
				if ($empty == 1)
				{
					$shipping_country = NULL;
					$country_binary = NULL;
				}
				else
				{
					$shipping_country = implode(',', $array_country);
					$country_binary = self::dec2bin_i($total);
				}
			}
			
		}
		else
		{
			$shipping_country = NULL;
		}
	
		$country_binary = str_pad($country_binary, 256, "0", STR_PAD_LEFT);
		DB::query(Database::UPDATE, "UPDATE listing_data SET ship_to = :ship_to WHERE (listing->'user_id')::integer = :user_id AND (listing->'shippable')::integer = '1'")
		->param(':ship_to', $country_binary)
		->param(':user_id', $user_id)
		->execute();
		
		DB::query(Database::UPDATE, "UPDATE public.user SET shipping_country = :shipping_country, ship_to = :ship_to WHERE id = :user_id")
		->param(':shipping_country', $shipping_country)
		->param(':ship_to', $country_binary)
		->param(':user_id', $user_id)
		->execute();

	}
	
	
	
	public function action_shipping_zone_delete()
	{
		$user_id = $this->auth->get_user()->id;	
		if ($_POST)
		{
			$cb = $_POST['cb'];
			DB::query('NULL', 'BEGIN')->execute();
			foreach ($cb as $index => $value)
			{
				$shipping_zone_id =(int)$value;
				$result = DB::query(Database::SELECT, "DELETE FROM shipping_zone WHERE user_id = :user_id AND id = :shipping_zone_id")
				->param(':user_id', $user_id)
				->param(':shipping_zone_id', $shipping_zone_id)
				->execute();
				if ( ! $result)
				{
					DB::query('NULL', 'ROLLBACK')->execute();
					throw new Kohana_Exception('site_error ');
				}
			}
			
			
			$this->update_shipping_country($user_id);
			

			
			DB::query('NULL', 'COMMIT')->execute();
			Request::current()->redirect("/my/shipping?s=d");
		}
	}
	
	public function action_shipping_method()
	{
		$user_id = $this->auth->get_user()->id;
		$view = View::factory(TEMPLATE . '/my/shipping_method');
		
		$success = Arr::get($_GET, 's', '');
		if ($success != '')
		{
			if ($success == 'a')
			{
				$view->msg = I18n::get('record_added');
			}
			else if($success == 'u')
			{
				$view->msg = I18n::get('record_updated');
			}
			else if ($success == 'd')
			{
				$view->msg = I18n::get('record_deleted');
			}
		}
		
		$user_shipping_method_obj = DB::query(Database::SELECT, "SELECT id, name FROM user_shipping_method WHERE user_id = :user_id ORDER BY name")
		->param(':user_id', $user_id)
		->execute();
		$view->user_shipping_method_obj = $user_shipping_method_obj;
		$view->cfg = $this->cfg;
		$this->template->content = $view;	
	}
	
	public function action_shipping_method_edit()
	{
		self::action_shipping_method_add();
	}
	
	public function action_shipping_method_add()
	{
		$action = Request::current()->action();
		$user_id = $this->auth->get_user()->id;
		
		$user_shipping_method_id = (int)Arr::get($_GET, 'id', Arr::get($_POST, 'id'));
		$view = View::factory(TEMPLATE . '/my/shipping_method_add');
		if ($_POST)
		{
			$post = Validation::factory($_POST)
				->rule('shipping_method_name', 'not_empty')
				->rule('sz', array($this, 'check_shipping_zone_count'), array(':validation', ':field', 'sz'));
				
			if ($post->check())
			{
				$minmax = Arr::get($_POST, 'minmax');

				$shipping_calculation = (int) Arr::get($_POST, 'shipping_calculation');
				//custom shipping table type: based on item weight or subtotal
				$cst_type = (int) Arr::get($_POST, 'cst_type');
				$sz = Arr::get($_POST, 'sz');
				
		
				//just make sure users don't spam the system by clicking the add button without filling the min/max values, we don't need those
				$array_data = array();
				$array_data['shipping_calculation'] = $shipping_calculation;
				$array_data['cst_type'] = $cst_type;
				foreach ($minmax as $index => $value)
				{
					$min = sprintf("%0.2f", Arr::get($_POST, "cst_min_$value"));
					$max = sprintf("%0.2f", Arr::get($_POST, "cst_max_$value"));
					if (strlen($min) < 1 AND strlen($max) < 1)
					{
						unset($_POST['minmax'][$index]);
					}
					else
					{
						$array_data[$value] = array();
						$array_data[$value]['min'] = $min;
						$array_data[$value]['max'] = $max;
					}
				}
				$json_data = json_encode($array_data);
				
				if ($user_shipping_method_id == 0)
				{
					$user_shipping_method_obj = DB::query(Database::SELECT, "SELECT id FROM user_shipping_method WHERE user_id = :user_id AND name = :shipping_method_name")
					->param(':user_id', $user_id)
					->param(':shipping_method_name', Arr::get($_POST, 'shipping_method_name'))
					->execute();
					
					if (count($user_shipping_method_obj) == 0)
					{
						DB::query(Database::INSERT, "INSERT INTO user_shipping_method(user_id, name, data) VALUES(:user_id, :shipping_method_name, :json_data)")
						->param(':user_id', $user_id)
						->param(':shipping_method_name', Arr::get($_POST, 'shipping_method_name'))
						->param(':json_data', $json_data)
						->execute();
							
						$id_obj = DB::query(Database::SELECT, "SELECT currval('user_shipping_method_id_seq') AS id")->execute();
						$user_shipping_method_id = $id_obj[0]['id'];
					}
					else
					{
						$user_shipping_method_id = $user_shipping_method_obj[0]['id'];
						DB::query(Database::UPDATE, "UPDATE user_shipping_method SET name = :shipping_method_name, data = :json_data WHERE id = :user_shipping_method_id")
						->param(':shipping_method_name', Arr::get($_POST, 'shipping_method_name'))
						->param(':json_data', $json_data)
						->param(':user_shipping_method_id', $user_shipping_method_id)
						->execute();
						
					}
				}
				else
				{
					$user_shipping_method_obj = DB::query(Database::SELECT, "SELECT id FROM user_shipping_method WHERE user_id = :user_id AND id = :user_shipping_method_id")
					->param(':user_id', $user_id)
					->param(':user_shipping_method_id', $user_shipping_method_id)
					->execute();
					if (count($user_shipping_method_obj) == 0)
					{
						throw new Kohana_Exception('site_error ');
					}
					else
					{
						DB::query(Database::UPDATE, "UPDATE user_shipping_method SET name = :shipping_method_name, data = :json_data WHERE id = :user_shipping_method_id")
						->param(':shipping_method_name', Arr::get($_POST, 'shipping_method_name'))
						->param(':json_data', $json_data)
						->param(':user_shipping_method_id', $user_shipping_method_id)
						->execute();
					}
				}
				DB::query('NULL', 'BEGIN')->execute();

				
				//array to store db record
				$db_shipping_zone_id = array();
				$db_table_id = array();
				$delete_list = '';
				$user_shipping_method_data_obj = DB::query(Database::SELECT, "SELECT id, shipping_zone_id FROM user_shipping_method_data WHERE user_shipping_method_id = :user_shipping_method_id")
				->param(':user_shipping_method_id', $user_shipping_method_id)
				->execute();
				$param_id = array();
				
				//in case user tries to submit multiple shipping zone, eg: worldwide + europe via script, we make sure that user can only select worldwide alone
				if (in_array(0, $sz))
				{
					$sz = array();
					$sz[0] = 0;
				}
				foreach ($user_shipping_method_data_obj as $result)
				{
					if ($result['shipping_zone_id'] == '')
					{
						$result['shipping_zone_id'] = 0;
					}
					if (in_array($result['shipping_zone_id'], $sz))
					{
						$db_shipping_zone_id[] = $result['shipping_zone_id'];
						$db_table_id[] = $result['id'];
					}
					else
					{
						$id = $result['id'];
						//$delete_list .= $result['id'] . ',';
						$delete_list .= ":$id,";
						
						$param_id[":$id"] = $id;
					}
				}
				if (strlen($delete_list) > 0)
				{
					$delete_list = '(' . substr($delete_list, 0, strlen($delete_list) - 1) . ')';
					$result = DB::query(Database::DELETE, "DELETE FROM user_shipping_method_data WHERE id IN $delete_list")
					//->param(':delete_list', $delete_list)
					->parameters($param_id)
					->execute();
					if ( ! $result)
					{
						DB::query('NULL', 'ROLLBACK')->execute();
					}
				}

				foreach ($sz as $index => $shipping_zone_id)
				{
					$from = (int) Arr::get($_POST, "from_$shipping_zone_id");
					$to = (int) Arr::get($_POST, "to_$shipping_zone_id");
					//business days/business weeks
					$dayweek = (int) Arr::get($_POST, "day_week_$shipping_zone_id");
					$array_data = array();
					$array_data['estimated_from'] = $from;
					$array_data['estimated_to'] = $to;
					$array_data['estimated_dayweek'] = $dayweek;
					
					//flat rate
					if ($shipping_calculation == 1 OR $shipping_calculation == 2)
					{
						//flat rate fee type: flat rate or percentage. Unsed for flat rate per item because each item purchased by customer might have different prices, so percentage rate is not applicable (Fixed rate only)
						$fr_fee_type = (int) Arr::get($_POST, "fr_fee_type_$shipping_zone_id");
						$fr_fee = sprintf("%0.2f", Arr::get($_POST, "fr_fee_$shipping_zone_id"));
						$array_data['fee_type'] = $fr_fee_type;
						$array_data['fee'] = $fr_fee;
						$json_data = json_encode($array_data);
					
						if ($shipping_zone_id == 0)
						{
							$shipping_zone_id = null;
						}
						$key = array_search($shipping_zone_id, $db_shipping_zone_id);
						if ($key === false)
						{
							DB::query(Database::INSERT, "INSERT INTO user_shipping_method_data(user_id, shipping_zone_id, user_shipping_method_id, data) VALUES(:user_id, :shipping_zone_id, :user_shipping_method_id, :json_data)")
							->param(':user_id', $user_id)
							->param(':shipping_zone_id', $shipping_zone_id)
							->param(':user_shipping_method_id', $user_shipping_method_id)
							->param(':json_data', $json_data)
							->execute();
						}
						else
						{
							$key = array_search($shipping_zone_id, $db_shipping_zone_id);
							DB::query(Database::UPDATE, "UPDATE user_shipping_method_data SET data = :json_data WHERE id = :id")
							->param(':json_data', $json_data)
							->param(':id', $db_table_id[$key])
							->execute();
						}
					}
					//custom shipping rate
					else if ($shipping_calculation == 3)
					{
						foreach ($minmax as $index => $sub_value)
						{
							$cst_fee = sprintf("%0.2f", Arr::get($_POST, "cst_fee_{$shipping_zone_id}_{$sub_value}"));
							$cst_fee_type = Arr::get($_POST, "cst_fee_type_{$shipping_zone_id}_{$sub_value}");
							$array_data[$sub_value] = array();
							$array_data[$sub_value]['fee'] = $cst_fee;
							$array_data[$sub_value]['fee_type'] = $cst_fee_type;
						}
						$json_data = json_encode($array_data);
						if ($shipping_zone_id == 0)
						{
							$shipping_zone_id = NULL;
						}
						$key = array_search($shipping_zone_id, $db_shipping_zone_id);
						if ($key === false)
						{
							$result = DB::query(Database::INSERT, "INSERT INTO user_shipping_method_data(user_id, shipping_zone_id, user_shipping_method_id, data) VALUES(:user_id, :shipping_zone_id, :user_shipping_method_id, :json_data)")
							->param(':user_id', $user_id)
							->param(':shipping_zone_id', $shipping_zone_id)
							->param(':user_shipping_method_id', $user_shipping_method_id)
							->param(':json_data', $json_data)
							->execute();
							if ( ! $result)
							{
								DB::query('NULL', 'ROLLBACK')->execute();
							}
						}
						else
						{
							$key = array_search($shipping_zone_id, $db_shipping_zone_id);
							DB::query(Database::UPDATE, "UPDATE user_shipping_method_data SET data = :json_data WHERE id = :id")
							->param(':json_data', $json_data)
							->param(':id', $db_table_id[$key])
							->execute();
							if ( ! $result)
							{
								DB::query('NULL', 'ROLLBACK')->execute();
							}
						}
					}
				}
				
				$this->update_shipping_country($user_id);
				
				
				
				DB::query('NULL', 'COMMIT')->execute();
				if ($action == 'shipping_method_add')
				{
					Request::current()->redirect('/my/shipping/shipping_method?s=a');
				}
				else
				{
					Request::current()->redirect('/my/shipping/shipping_method?s=u');
				}
			}
			else
			{
				$view->post = $post;
				$view->errors = $post->errors('my/shipping_method_add');
				
				$sz = Arr::get($_POST, 'sz');

				$minmax = Arr::get($_POST, 'minmax');
				$array_user_shipping_method = array();
				$array_user_shipping_method_data = array();
				foreach ($minmax as $index => $value)
				{
					$min = Arr::get($_POST, "cst_min_$value");
					$max = Arr::get($_POST, "cst_max_$value");
					$array_user_shipping_method[$value] = array();
					$array_user_shipping_method[$value]['min'] = $min;
					$array_user_shipping_method[$value]['max'] = $max;
				}

				if (count($sz) > 0)
				{
					foreach ($sz as $index => $shipping_zone_id)
					{
						$array_user_shipping_method_data[$shipping_zone_id]['estimated_from'] = Arr::get($_POST, "from_$shipping_zone_id");
						$array_user_shipping_method_data[$shipping_zone_id]['estimated_to'] = Arr::get($_POST, "to_$shipping_zone_id");
						$array_user_shipping_method_data[$shipping_zone_id]['estimated_dayweek'] = Arr::get($_POST, "day_week_$shipping_zone_id");
						if ($shipping_calculation == 1 OR $shipping_calculation == 2)
						{
							$array_user_shipping_method_data[$shipping_zone_id]['fee_type'] = (int) Arr::get($_POST, "fr_fee_type_$shipping_zone_id");
							$array_user_shipping_method_data[$shipping_zone_id]['fee'] = Arr::get($_POST, "fr_fee_$shipping_zone_id");
						}
						else if ($shipping_calculation == 3)
						{
							foreach ($minmax as $index => $sub_value)
							{
								$array_user_shipping_method_data[$shipping_zone_id][$sub_value] = array();
								$array_user_shipping_method_data[$shipping_zone_id][$sub_value]['fee'] = Arr::get($_POST, "cst_fee_{$shipping_zone_id}_{$sub_value}");
								$array_user_shipping_method_data[$shipping_zone_id][$sub_value]['fee_type'] = Arr::get($_POST, "cst_fee_type_{$shipping_zone_id}_{$sub_value}");
							}
						}
					}
				
				}
				$view->array_user_shipping_method = $array_user_shipping_method;
				$view->array_user_shipping_method_data = $array_user_shipping_method_data;
			}
		}

		if ($action == 'shipping_method_add')
		{
			$shipping_calculation = (int) Arr::get($_POST, 'shipping_calculation', 1);
			$view->title = I18n::get('new_shipping_method');
			$view->method = 'add';
			$view->shipping_method_name = Arr::get($_POST, 'shipping_method_name');
			$view->shipping_calculation = $shipping_calculation;
			$view->cst_type = (int) Arr::get($_POST, 'cst_type', 1);
		}
		else
		{
			$user_shipping_method_obj = DB::query(Database::SELECT, "SELECT id, name, data FROM user_shipping_method WHERE user_id = :user_id AND id = :user_shipping_method_id ORDER BY name")
			->param(':user_id', $user_id)
			->param(':user_shipping_method_id', $user_shipping_method_id)
			->execute();
			if (count($user_shipping_method_obj) > 0)
			{
				$array_user_shipping_method = self::object_to_array(json_decode($user_shipping_method_obj[0]['data']));
				$user_shipping_method_data_obj = DB::query(Database::SELECT, "SELECT id, coalesce(shipping_zone_id, 0) AS shipping_zone_id, data FROM user_shipping_method_data WHERE user_id = :user_id AND user_shipping_method_id = :user_shipping_method_id")
				->param(':user_id', $user_id)
				->param(':user_shipping_method_id', $user_shipping_method_id)
				->execute();
				
				
				$array_user_shipping_method_data = array();
				foreach ($user_shipping_method_data_obj as $result)
				{
					$array_user_shipping_method_data[$result['shipping_zone_id']] = self::object_to_array(json_decode($result['data']));
				}
				
				
				$shipping_calculation = (int) Arr::get($_POST, 'shipping_calculation', $array_user_shipping_method['shipping_calculation']);
				$view->array_user_shipping_method = $array_user_shipping_method;
				$view->array_user_shipping_method_data = $array_user_shipping_method_data;
				$view->title = I18n::get('edit_shipping_method');
				$view->method = 'edit';
				$view->shipping_method_name = Arr::get($_POST, 'shipping_method_name', $user_shipping_method_obj[0]['name']);
				$view->shipping_calculation = $shipping_calculation;
				$view->cst_type = $array_user_shipping_method['cst_type'];
				$view->id = $user_shipping_method_id;
			}
			else
			{
				Request::current()->redirect('/my/shipping/shipping_method');
			}
		}
		
		$shipping_zone_obj = DB::query(Database::SELECT, "SELECT id, name FROM shipping_zone WHERE user_id = :user_id ORDER BY name")
		->param(':user_id', $user_id)
		->execute();
		$shipping_zone_array = array('0' => I18n::get('worldwide'));
		foreach ($shipping_zone_obj as $result)
		{
			$shipping_zone_array[$result['id']] = $result['name'];
		}
		
		$preference_obj = DB::query(Database::SELECT, "SELECT preference->'currency_code' AS currency_code, preference->'weight_unit' AS weight_unit FROM user_preference WHERE user_id = :user_id")
		->param(':user_id', $user_id)
		->execute();
		$currency_code = $preference_obj[0]['currency_code'];
		$array_weight_unit = array(1 => 'g', 2 => 'kg', 3 => 'lbs', 4 => 'oz'); 
		$weight_unit = $array_weight_unit[$preference_obj[0]['weight_unit']];
		
		if ($view->cst_type == 1)
		{
			$view->min_title = I18n::get('min_weight') . " ($weight_unit)";
			$view->max_title = I18n::get('max_weight') . " ($weight_unit)";
		}
		else
		{
			$view->min_title = I18n::get('min_subtotal') . " ($currency_code)";
			$view->max_title = I18n::get('max_subtotal') . " ($currency_code)";
		}

		$country_obj = DB::query(Database::SELECT, "SELECT id, name, country_region_id FROM country WHERE status = '1' ORDER BY country_region_id, name")->execute();
		$view->country_obj = $country_obj;
		$view->currency_code = $currency_code;
		$view->weight_unit = $weight_unit;
		$view->shipping_zone_obj = $shipping_zone_obj;
		$view->shipping_zone_array = $shipping_zone_array;
		$view->cfg = $this->cfg;
		$this->template->content = $view;
	}
	
	public function action_shipping_method_delete()
	{
		$user_id = $this->auth->get_user()->id;	
		if ($_POST)
		{
			$cb = $_POST['cb'];
			
			DB::query('NULL', 'BEGIN')->execute();
			foreach ($cb as $index => $value)
			{
				$user_shipping_method_id =(int)$value;
				$result = DB::query(Database::SELECT, "DELETE FROM user_shipping_method_data WHERE user_id = :user_id AND user_shipping_method_id = :user_shipping_method_id")
				->param(':user_id', $user_id)
				->param(':user_shipping_method_id', $user_shipping_method_id)
				->execute();
				if ( ! $result)
				{
					DB::query('NULL', 'ROLLBACK')->execute();
					throw new Kohana_Exception('site_error ');
				}
				DB::query(Database::SELECT, "DELETE FROM user_shipping_method WHERE user_id = :user_id AND id = :user_shipping_method_id")
				->param(':user_id', $user_id)
				->param(':user_shipping_method_id', $user_shipping_method_id)
				->execute();
				if ( ! $result)
				{
					DB::query('NULL', 'ROLLBACK')->execute();
					throw new Kohana_Exception('site_error ');
				}
			}
			
			$this->update_shipping_country($user_id);
			DB::query('NULL', 'COMMIT')->execute();
			
			Request::current()->redirect('/my/shipping/shipping_method?s=d');
		}
	}
	

	function action_index()
	{
		$user_id = $this->auth->get_user()->id;	
		$view = View::factory(TEMPLATE . '/my/shipping_zone');
		
		$success = Arr::get($_GET, 's', '');
		if ($success != '')
		{
			if ($success == 'a')
			{
				$view->msg = I18n::get('record_added');
			}
			else if($success == 'u')
			{
				$view->msg = I18n::get('record_updated');
			}
			else if ($success == 'd')
			{
				$view->msg = I18n::get('record_deleted');
			}
		}
		$shipping_zone_obj = DB::query(Database::SELECT, "SELECT id, name FROM shipping_zone WHERE user_id = :user_id ORDER BY name")
		->param(':user_id', $user_id)
		->execute();
		
		$view->shipping_zone_obj = $shipping_zone_obj;
		$view->cfg = $this->cfg;
		$this->template->content = $view;
	}
}
