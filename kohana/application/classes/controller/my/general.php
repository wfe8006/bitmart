<?php
class Controller_My_General extends Controller_System
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
		$view = View::factory(TEMPLATE . '/my/general');
		$user_id = (int)$this->auth->get_user()->id;
		$username = $this->auth->get_user()->username;
		if ($_POST)
		{
			$post = Validation::factory($_POST)
			->rule('store_name', 'not_empty');
			if ($post->check())
			{
				DB::query('NULL', 'BEGIN')->execute();
				$preference_array = array();
				$currency_code = $post['currency'];
				$weight_unit = (int) $post['weight_unit'];
				$store_name = $post['store_name'];

				/*
				$cryptocurrency = 0;
				foreach ($this->cfg_crypto as $symbol => $record)
				{
					$array_payment[$symbol]['active'] = $active = (int) $post[$symbol];
					if ($active == 1)
					{
						$cryptocurrency += $record['constant'];
					}
					$view->{$symbol} = $active;
				}
				$json_payment =  json_encode($array_payment);
				$result = DB::query(Database::UPDATE, "UPDATE user_payment_option SET option = json_add_update(option, :json_payment) WHERE user_id = :user_id")
				->param(':json_payment', $json_payment)
				->param(':user_id', $user_id)
				->execute();
				if ( ! $result)
				{
					DB::query('NULL', 'ROLLBACK')->execute();
					throw new Kohana_Exception('site_error ');
				}
				*/


				$currency_obj = DB::query(Database::SELECT, "SELECT iso4217 FROM currency WHERE iso4217 = :currency_code")
				->param(':currency_code', $currency_code)
				->execute();
				if ( ! $currency_obj)
				{
					if ( ! array_key_exists($currency_code, $this->cfg_crypto))
					{
						DB::query('NULL', 'ROLLBACK')->execute();
						throw new Kohana_Exception('site_error ');
					}
				}

		

				$user_preference_obj = DB::query(Database::SELECT, "SELECT up.id from user_preference up WHERE user_id = :user_id AND (preference->'currency_code' = :currency_code)")
				->param(':user_id', $user_id)
				->param(':currency_code', $currency_code)
				->execute();
				/*
				$user_preference_obj = DB::query(Database::SELECT, "SELECT up.id from user_preference up WHERE user_id = :user_id AND (preference->'currency_code' = :currency_code AND (preference->'cryptocurrency')::integer = ':cryptocurrency')")
				->param(':user_id', $user_id)
				->param(':currency_code', $currency_code)
				->param(':cryptocurrency', $cryptocurrency)
				->execute();
				*/

				if ( ! $user_preference_obj)
				{
					DB::query('NULL', 'ROLLBACK')->execute();
					throw new Kohana_Exception('site_error ');
				}
				if (count($user_preference_obj) == 0)
				{
					$to_usd = $this->cfg_currency[$currency_code . '_usd'];
					/*
					$result = DB::query(Database::UPDATE, "UPDATE listing_data SET listing = listing || hstore('currency_code', :currency_code) || hstore('cryptocurrency', ':cryptocurrency') ||  hstore('price_usd', CASE WHEN listing->'price' = '' THEN '' ELSE ((round(float8((listing->'price')::float * :to_usd)::numeric, 2))::text) END) WHERE (listing->'user_id')::integer = :user_id")
					->param(':currency_code', $currency_code)
					->param(':cryptocurrency', $cryptocurrency)
					->param(':user_id', $user_id)
					->param(':to_usd', $to_usd)
					->execute();
					*/
					$result = DB::query(Database::UPDATE, "UPDATE listing_data SET listing = listing || hstore('currency_code', :currency_code) || hstore('price_usd', CASE WHEN listing->'price' = '' THEN '' ELSE ((round(float8((listing->'price')::float * :to_usd)::numeric, 2))::text) END) WHERE (listing->'user_id')::integer = :user_id")
					->param(':currency_code', $currency_code)
					->param(':user_id', $user_id)
					->param(':to_usd', $to_usd)
					->execute();

					if ( ! $result)
					{
						DB::query('NULL', 'ROLLBACK')->execute();
						throw new Kohana_Exception('site_error ');
					}
				}
				/*
				$result = DB::query(Database::UPDATE, "UPDATE user_preference SET preference = preference || hstore('currency_code', :currency_code) || hstore('weight_unit', ':weight_unit') || hstore('cryptocurrency', ':cryptocurrency') WHERE user_id = :user_id")
				->param(':currency_code', $currency_code)
				->param(':weight_unit', $weight_unit)
				->param(':cryptocurrency', $cryptocurrency)
				->param(':user_id', $user_id)
				->execute();
				*/
				$result = DB::query(Database::UPDATE, "UPDATE user_preference SET preference = preference || hstore('store_name', :store_name) || hstore('currency_code', :currency_code) || hstore('weight_unit', ':weight_unit') WHERE user_id = :user_id")
				->param(':store_name', $store_name)
				->param(':currency_code', $currency_code)
				->param(':weight_unit', $weight_unit)
				->param(':user_id', $user_id)
				->execute();
				if ( ! $result)
				{
						DB::query('NULL', 'ROLLBACK')->execute();
						throw new Kohana_Exception('site_error ');
				}
				DB::query('NULL', 'COMMIT')->execute();
				$view->alert_type = 'success';
				$view->msg = I18n::get('settings_saved');
			}
			else
			{
				//the array in validate message file is not really used, we just need it as a place holder so that kohana can switch to the default error message, eg: This is a required field.
				$view->errors = $post->errors('validate');
				$view->alert_type = 'warning';
				$view->msg = I18n::get('please_correct_errors');
			}
			$view->html = $html;
		}
		else
		{
			$preference_obj = DB::query(Database::SELECT, "SELECT preference->'currency_code' AS currency_code, preference->'weight_unit' AS weight_unit, preference->'store_name' AS store_name FROM user_preference WHERE user_id = :user_id")
			->param(':user_id', $user_id)
			->execute();


			/*
			$user_payment_option_obj = DB::query(Database::SELECT, "SELECT option FROM user_payment_option WHERE user_id = :user_id")
			->param(':user_id', $user_id)
			->execute();
			$array_payment_option = self::object_to_array(json_decode($user_payment_option_obj[0]['option']));
			*/

			$currency_code = $preference_obj[0]['currency_code'];
			$weight_unit = $preference_obj[0]['weight_unit'];
			$store_name = $preference_obj[0]['store_name'];
			$view->html = $html;

			/*
			foreach ($this->cfg_crypto as $symbol => $record)
			{
					$active = $record['active'];
					if (array_key_exists($symbol, $array_payment_option) AND $active == 1)
					{
							$view->{$symbol} = $array_payment_option[$symbol]['active'];
					}
			}
			*/
		}
		
		$view->store_name = Arr::get($_POST, 'store_name', $store_name);
		$view->currency_code = Arr::get($_POST, 'currency', $currency_code);
		$view->weight_unit = Arr::get($_POST, 'weight_unit', $weight_unit);
		$view->currency_obj = DB::query(Database::SELECT, "SELECT * FROM currency ORDER BY iso4217")->execute();
		$view->cfg = $this->cfg;
		$view->cfg_crypto = $this->cfg_crypto;
		$this->template->content = $view;
	}
}
