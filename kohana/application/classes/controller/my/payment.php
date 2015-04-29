<?php
class Controller_My_Payment extends Controller_System
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
		$view = View::factory(TEMPLATE . '/my/payment');
		$user_id = (int)$this->auth->get_user()->id;
	
		
		//$query = DB::query(Database::SELECT, 'SELECT * FROM users WHERE username = :user');
		
	
		if ($_POST)
		{
			$post = Validation::factory($_POST);
			
			
			if ($post->check())
			{
				

				
				$array_payment['cash_on_delivery']['active'] = (int) $post['cash_on_delivery'];
				$array_payment['cash_on_delivery']['cash_on_delivery_note'] = $post['cash_on_delivery_note'];
				
				$array_payment['bank_deposit']['active'] = (int) $post['bank_deposit'];
				$array_payment['bank_deposit']['bank_deposit_note'] = $post['bank_deposit_note'];

				$array_payment['money_order']['active'] = (int) $post['money_order'];
				$array_payment['money_order']['money_order_note'] = $post['money_order_note'];
				
				$array_payment['cashier_check']['active'] = (int) $post['cashier_check'];
				$array_payment['cashier_check']['cashier_check_note'] = $post['cashier_check_note'];
				
				$array_payment['personal_check']['active'] = (int) $post['personal_check'];
				$array_payment['personal_check']['personal_check_note'] = $post['personal_check_note'];

				
				$cryptocurrency = 0;
				foreach ($this->cfg_crypto as $symbol => $record)
				{
					$active = $record['active'];
					if ($active == 1)
					{
						$array_payment[$symbol]['active'] = (int) $post[$symbol];
						if ($array_payment[$symbol]['active'] == 1)
						{
							$cryptocurrency += $record['constant'];
						}
					}
				}
				
				
				$json_payment = json_encode($array_payment);

				DB::query(Database::UPDATE, "UPDATE user_payment_option SET option = json_add_update(option, :json_payment) WHERE user_id = :user_id")
				->param(':json_payment', $json_payment)
				->param(':user_id', $user_id)
				->execute();
				
				
				$result = DB::query(Database::UPDATE, "UPDATE user_preference SET preference = preference || hstore('cryptocurrency', ':cryptocurrency') WHERE user_id = :user_id")
				->param(':cryptocurrency', $cryptocurrency)
				->param(':user_id', $user_id)
				->execute();
				
				
				$result = DB::query(Database::UPDATE, "UPDATE listing_data SET listing = listing || hstore('cryptocurrency', ':cryptocurrency') WHERE (listing->'user_id')::integer = :user_id")
				->param(':cryptocurrency', $cryptocurrency)
				->param(':user_id', $user_id)
				->execute();
				
			
				//DB::query(Database::UPDATE, "UPDATE user_payment_option SET option = '$hstore_payment' WHERE user_id = '$user_id'")->execute();
				$view->msg = I18n::get('record_updated');
			}
			else
			{
				$view->errors = $post->errors('validate');
			}
		}
		
		//json format
		$user_payment_option_obj = DB::query(Database::SELECT, "SELECT option FROM user_payment_option WHERE user_id = :user_id")
		->param(':user_id', $user_id)
		->execute();
		
		$array_payment_option = self::object_to_array(json_decode($user_payment_option_obj[0]['option']));

		
		//hstore format
		/*
		$user_payment_option_obj = DB::query(Database::SELECT, "SELECT (each(option)).key, (each(option)).value FROM user_payment_option where user_id = '$user_id'")->execute();
		foreach($user_payment_option_obj as $record)
		{
			$view->set($record['key'], $record['value']);
		}
		*/
		
		$view->cash_on_delivery = Arr::get($_POST, 'cash_on_delivery', $array_payment_option['cash_on_delivery']['active']);
		$view->cash_on_delivery_note = Arr::get($_POST, 'cash_on_delivery_note', $array_payment_option['cash_on_delivery']['cash_on_delivery_note']);
		
		$view->bank_deposit = Arr::get($_POST, 'bank_deposit', $array_payment_option['bank_deposit']['active']);
		$view->bank_deposit_note = Arr::get($_POST, 'bank_deposit_note', $array_payment_option['bank_deposit']['bank_deposit_note']);
		
		$view->money_order = Arr::get($_POST, 'money_order', $array_payment_option['money_order']['active']);
		$view->money_order_note = Arr::get($_POST, 'money_order_note', $array_payment_option['money_order']['money_order_note']);
		
		$view->cashier_check = Arr::get($_POST, 'cashier_check', $array_payment_option['cashier_check']['active']);
		$view->cashier_check_note = Arr::get($_POST, 'cashier_check_note', $array_payment_option['cashier_check']['cashier_check_note']);
		
		$view->personal_check = Arr::get($_POST, 'personal_check', $array_payment_option['personal_check']['active']);
		$view->personal_check_note = Arr::get($_POST, 'personal_check_note', $array_payment_option['personal_check']['personal_check_note']);
		
		foreach ($this->cfg_crypto as $symbol => $record)
		{
			$active = $record['active'];
			if ($active == 1)
			{
				$view->{$symbol} = Arr::get($_POST, $symbol, $array_payment_option[$symbol]['active']);
			}
		}
		
		$view->cfg = $this->cfg;
		$view->cfg_crypto = $this->cfg_crypto;
		$this->template->content = $view;
	}
}
