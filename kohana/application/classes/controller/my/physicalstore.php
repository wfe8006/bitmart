<?php
class Controller_My_Physicalstore extends Controller_System
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

	function action_index()
	{
		$view = View::factory(TEMPLATE . '/my/physicalstore');
		$user_id = (int) $this->auth->get_user()->id;
		if ($_POST)
		{
			$post = Validation::factory($_POST);
			$country_id = (int) $post['country1'];
			$clear = (int) $post['clear'];
			
			if ($clear == 0)
			{
				$post->rule('store_name', 'not_empty');
				$post->rule('street1', 'not_empty');
				$post->rule('country1', 'not_empty');
				$post->rule('main_phone', 'not_empty');
				if ($country_id > 0)
				{
					$country_geo_info_obj = DB::query(Database::SELECT, "SELECT gi.id AS geo_info_id, cgi.compulsory_field, cgi.has_geo, gi.name, gi.short_name FROM country_geo_info cgi LEFT JOIN geo_info gi ON cgi.geo_info_id = gi.id WHERE country_id = :country_id")
					->param(':country_id', $country_id)
					->execute();
					$post->rule('street1', 'not_empty');
					foreach ($country_geo_info_obj as $record)
					{
						$compulsory_field = $record['compulsory_field'];
						if ($compulsory_field == 1)
						{
							$post->rule($record['short_name'] . '', 'not_empty');
						}
					}
				}
				
				if ($post->check())
				{
					$array_address = array();
					foreach ($country_geo_info_obj as $record)
					{
						$has_geo = $record['has_geo'];
						$field = $record['short_name'];
						if ($has_geo == 'geo1')
						{
							$geo1 = (int) $post[$field . ''];
							$array_address[$field] = (int) $post[$field . ''];
							$geo_obj = DB::query(Database::SELECT, "SELECT name AS geo1_name FROM geo1 WHERE id = :geo1")
							->param(':geo1', $geo1)
							->execute();
							if (count($geo_obj) > 0)
							{
								$$field = $geo_obj[0]['geo1_name'];
							}
						}
						else if ($has_geo == 'geo2')
						{
							$geo2 = (int) $post[$field . ''];
							$array_address[$field] = (int) $post[$field . ''];
							$geo_obj = DB::query(Database::SELECT, "SELECT name AS geo2_name FROM geo2 WHERE id = :geo2")
							->param(':geo2', $geo2)
							->execute();
							if (count($geo_obj) > 0)
							{
								$$field = $geo_obj[0]['geo2_name'];
							}
						}
						else
						{
							$array_address[$field] = $post[$field . ''];
							$$field = $post[$field . ''];
						}
						$view->${$field . ''} = $post[$field . ''];
					}
					$store_name = $array_address['store_name'] = $post['store_name'];
					$street1 = $array_address['street1'] = $post['street1'];
					$street2 = $array_address['street2'] = $post['street2'];
					$array_address['country'] = $country_id;
					$main_phone = $array_address['main_phone'] = $post['main_phone'];
					if ($street2 != '')
					{
						$street2 = $street2 . "<br>";
					}
					//to be used in /json/get_store_address directly so that we don't need to query db
					
					include  __DIR__ . "/../../" . TEMPLATE . "/country_geo_array.php"; 

					$address = $array_country_shipping_address[$country_id];
					$array_address['address'] = "<address><b>$store_name</b><br>$address<br>$main_phone</address>";

					

					if (count($array_address) > 0)
					{
						$array_old = array(':', '"null"');
						$array_new = array('=>', 'null');
						$json_address = json_encode($array_address);
						$hstore_address = substr(str_replace($array_old, $array_new, $json_address), 1, -1);
					}
					DB::query(Database::UPDATE, "UPDATE user_store SET address = :hstore_address WHERE user_id = :user_id")
					->param(':hstore_address', $hstore_address)
					->param(':user_id', $user_id)
					->execute();
					Request::current()->redirect("/my/physicalstore?m=s");
				}
				else
				{
					//the array in validate message file is not really used, we just need it as a place holder so that kohana can switch to the default error message, eg: This is a required field.
					$view->errors = $post->errors('validate');
					$view->alert_type = 'warning';
					$view->msg = I18n::get('please_correct_errors');
					$array_h_data = array();
					foreach ($country_geo_info_obj as $record)
					{
						$has_geo = $record['has_geo'];
						$field = $record['short_name'] . '';
						$required = $record['compulsory_field'] == 1 ? ' *' : '';
						$view->set($field, $post[$field]);
						$error = isset($view->errors) ? $view->errors[$field] : '';
						if ($record['short_name'] == 'country' OR $record['short_name'] == 'street1' OR $record['short_name'] == 'street2' OR $record['short_name'] == 'main_phone' OR $record['short_name'] == 'store_name')
						{
							$view->set($field, $post[$field]);
						}
						else
						{
							$array_h_data[$field] = $post[$field];
						}
					}
					//storing db store info that will be loaded into form using ajax
					$view->array_h_data = $array_h_data;
					$view->store_name = $post['store_name'];
					$view->street1 = $post['street1'];
					$view->street2 = $post['street2'];
					$view->country = $country_id;
					$view->main_phone = $post['main_phone'];
					
				}
			}
			else
			{
				$hstore_address = '';
				DB::query(Database::UPDATE, "UPDATE user_store SET address = :hstore_address WHERE user_id = :user_id")
				->param(':hstore_address', $hstore_address)
				->param(':user_id', $user_id)
				->execute();
				Request::current()->redirect("/my/physicalstore?m=s");
			}
		}
		else
		{
			$user_store_obj = DB::query(Database::SELECT, "SELECT (each(address)).key, (each(address)).value FROM user_store where user_id = :user_id")
			->param(':user_id', $user_id)
			->execute();

			if (count($user_store_obj) > 0)
			{
				//array_h_data contains integer field of an address that has to be loaded via ajax, for example, we can't just display print the value of geo1_id because its dropdown value (loaded via ajax)
				$array_h_data = array();
				foreach($user_store_obj as $record)
				{
					$key = $record['key'] . '';
					//$$key = $record['value'];
					//skip this we only need this field to be displayed to buyer at product page
					if ($record['key'] == 'store_address')
					{
					}
					else if ($record['key'] == 'country' OR $record['key'] == 'street1' OR $record['key'] == 'street2' OR $record['key'] == 'main_phone' OR $record['key'] == 'store_name')
					{
						$view->set($key, $record['value']);
					}
					else
					{
						$array_h_data[$key] = $record['value'];
					}
					
				}
				$view->array_h_data = $array_h_data;
			}
		}
		
		$msg = Arr::get($_GET, 'm');
		if ($msg)
		{
			if ($msg == 's')
			{
				$view->alert_type = 'success';
				$view->msg = I18n::get('store_info_saved');
			}
		}

		$view->country_obj = DB::query(Database::SELECT, "SELECT id, name FROM country WHERE status = '1' ORDER BY name")->execute();
		$view->cfg = $this->cfg;
		$this->template->content = $view;
	}
}
