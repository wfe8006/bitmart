<?php
class Controller_My_CryptoWallet extends Controller_System
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
			$array_action = array(
			'withdraw_confirm'
			);
			
			if (in_array($this->request->action(), $array_action))
			{
			}
			else
			{
				Request::current()->redirect('https://' . $this->cfg['www_domain'] . '/account/auth');
			}
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

	function action_withdraw_confirm()
	{
		$hash = Request::current()->param('id');
		$transaction_obj = DB::query(Database::SELECT, "SELECT id, address, amount, crypto FROM crypto_transaction WHERE txid = :hash AND category = 'send'")
			->param(':hash', $hash)
			->execute();
		
		if (count($transaction_obj) == 1)
		{
			$transaction_id = $transaction_obj[0]['id'];
			$address = $transaction_obj[0]['address'];
			$currency = $transaction_obj[0]['crypto'];
			//revert the negative sign to positive, pass it to send_coin() function
			$net_withdrawal = 0 - $transaction_obj[0]['amount'];
			$array_crypto_cfg = $this->cfg_crypto[$currency];

			include Kohana::find_file('libraries', 'Crypto');
			include Kohana::find_file('libraries', 'jsonRPCClient');
			$crypto = new Crypto($array_crypto_cfg);
			
			$record = $crypto->send_coin($address, $net_withdrawal);
			if ($record['status'] == 0)
			{
				$error = $record['error'];
				throw new Kohana_Exception($error);
			}
			else
			{
				$txid = $record['txid'];
				$result = DB::query(Database::UPDATE, "UPDATE crypto_transaction SET txid = :txid WHERE id = :id")
				->param(':txid', $txid)
				->param(':id', $transaction_id)
				->execute();
				if ( ! $result)
				{
					throw new Kohana_Exception('site_error ');
				}
				Request::current()->redirect("/my/cryptowallet/withdraw/$currency?m=confirmed");
			}
		}
		else
		{
			$view = View::factory(TEMPLATE . '/special_info');
			$view->msg = I18n::get('invalid_request');
			$this->template->content = $view;
		}
	}
	
	function action_withdraw()
	{
		$username = $this->user->username;
		$user_id = $this->user->id;
		$email = $this->user->email;
		$currency = Request::current()->param('id');
		
		if (array_key_exists($currency, $this->cfg_crypto))
		{
			$crypto_name = $this->cfg_crypto[$currency]['name'];
			$array_crypto_cfg = $this->cfg_crypto[$currency];
			
			$balance_obj = DB::query(Database::SELECT, "SELECT sum(amount) + sum(fee) AS balance FROM crypto_transaction WHERE account = :username AND (status = '01' OR category = 'send') AND crypto = :crypto GROUP BY crypto")
			->param(':username', $username)
			->param(':crypto', $currency)
			->execute();

			if ( ! $balance_obj)
			{
				throw new Kohana_Exception('site_error ');
			}

			if ($_POST)
			{
				$address = Arr::get($_POST, 'address');
				$amount_to_withdraw = (int) (Arr::get($_POST, 'amount') * 1e8);
				$balance = $balance_obj[0]['balance'];
				$withdrawal_fee = $array_crypto_cfg['withdrawal_fee'];
				
				if (is_numeric($amount_to_withdraw) AND $amount_to_withdraw > 0)
				{
					$net_withdrawal = $amount_to_withdraw - $withdrawal_fee;
					if ($amount_to_withdraw <= $balance)
					//if ($net_withdrawal < $balance)
					{
						/*
						print "<br>withdrawal fee: $withdrawal_fee === " . $withdrawal_fee / 1e8;
						print "<br>net withdrawal: $net_withdrawal === " . $net_withdrawal / 1e8;
						print "<br>amount to withdraw: $amount_to_withdraw === " . $amount_to_withdraw / 1e8;
						print "<br>user balance: $balance === " . $balance / 1e8;
						*/
						
						//we generate a random confirmation number for the pending transaction: 50-char)
						$hash = Text::random('alnum', 50);
						DB::query('NULL', 'BEGIN')->execute();
						$result = DB::query(Database::INSERT, "INSERT into crypto_transaction(crypto, address, category, amount, txid, time, account, fee, confirmation) VALUES(:crypto, :address, :category, :amount, :txid, :time, :account, :fee, 0)")
								->param(':crypto', $currency)
								->param(':address', $address)
								->param(':category', 'send')
								->param(':amount', -$net_withdrawal)
								->param(':txid', $hash)
								->param(':time', time())
								->param(':account', $username)
								->param(':fee', -$withdrawal_fee)
								->execute();
						if ( ! $result)
						{
							DB::query('NULL', 'ROLLBACK')->execute();
							throw new Kohana_Exception('site_error ');
						}
							
						$result = DB::query(Database::SELECT, "SELECT currval('crypto_transaction_id_seq') AS crypto_transaction_id")->execute();
						if ( ! $result)
						{
							DB::query('NULL', 'ROLLBACK')->execute();
							throw new Kohana_Exception('site_error ');
						}
						$crypto_transaction_id = $result[0]['crypto_transaction_id'];
						
						$result = DB::query(Database::INSERT, "INSERT into crypto_withdrawal_log(crypto_transaction_id, user_id, ip_address) VALUES(:crypto_transaction_id, :user_id, :ip_address)")
								->param(':crypto_transaction_id', $crypto_transaction_id)
								->param(':user_id', $user_id)
								->param(':category', 'send')
								->param(':ip_address', Request::$client_ip)
								->execute();
						if ( ! $result)
						{
							DB::query('NULL', 'ROLLBACK')->execute();
							throw new Kohana_Exception('site_error ');
						}
						DB::query('NULL', 'COMMIT')->execute();

						$amount_to_withdraw =  $net_withdrawal / 1e8 . ' ' . strtoupper($currency);;
						$email_message = sprintf(I18n::get('confirmation_of_withdrawal_message'), $username, $amount_to_withdraw, $address, $hash);
						$transport = Swift_MailTransport::newInstance();
						$mailer = Swift_Mailer::newInstance($transport);
						$message = Swift_Message::newInstance(I18n::get('confirmation_of_withdrawal_subject'))
						->setFrom(array($this->cfg["from_email"] => $this->cfg["site_name"] . ' Support'))
						->setTo($email)
						->setBody($email_message, 'text/plain');
						$mailer->send($message);
						
						Request::current()->redirect("/my/cryptowallet/withdraw/$currency?m=sent");
					}
					else
					{
						Request::current()->redirect("/my/cryptowallet/withdraw/$currency");

					}
				}
				else
				{
					Request::current()->redirect("/my/cryptowallet/withdraw/$currency");
				}
			}
			else
			{
				$balance = sprintf('%0.8f', $balance_obj[0]['balance'] / 1e8);
				$withdrawal_fee = sprintf('%0.8f', $array_crypto_cfg['withdrawal_fee'] / 1e8);
			}
			
			$view = View::factory(TEMPLATE . '/my/cryptowallet_withdraw');
			
			$m = Arr::get($_GET, 'm');
			if ($m == 'sent')
			{
				$view->msg = I18n::get('withdrawal_request_submitted');
				$view->alert_type = 'success';
			}
			else if ($m == 'confirmed')
			{
				$view->msg = I18n::get('withdrawal_request_confirmed');
				$view->alert_type = 'success';
			}
			$view->cfg = $this->cfg;
			$view->currency = $currency;
			$view->crypto_name = $crypto_name;
			$view->balance = $balance;
			$view->withdrawal_fee = $withdrawal_fee;
			$this->template->content = $view;
		}
	}
	
	function action_make_payment()
	{

		$username = $this->user->username;
		$user_id = $this->user->id;
		$order_id = Arr::get($_GET, 'order_id', Arr::get($_POST, 'order_id'));
		$order_obj = DB::query(Database::SELECT, "SELECT o.data->'payment_method_name' AS payment_method_name, o.data->'new_total' AS new_total, u.username AS seller_username FROM public.order o LEFT JOIN public.user u ON o.seller_id = u.id WHERE o.buyer_id = :buyer_id AND o.id = :order_id AND (o.data->>'order_status')::integer = :order_status")
		->param(':buyer_id', $user_id)
		->param(':order_status', 2)
		->param(':order_id', $order_id)
		->execute();
		if (count($order_obj) == 1)
		{
			$currency = self::object_to_array(json_decode($order_obj[0]['payment_method_name']));
			$new_total = self::object_to_array(json_decode($order_obj[0]['new_total']));
			$seller_username = $order_obj[0]['seller_username'];
			
			if (array_key_exists($currency, $this->cfg_crypto))
			{
				// we do not want to include transaction of type 'receive' that is not confirmed yet (status = '00') but we want to make sure that all 'send' transactions are counted
				$balance_obj = DB::query(Database::SELECT, "SELECT crypto, sum(amount) + sum(fee) AS balance FROM crypto_transaction WHERE account = :username AND (status = '01' OR category = 'send') AND crypto = :crypto GROUP BY crypto")
				->param(':username', $username)
				->param(':crypto', $currency)
				->execute();
				$enough_balance = 0;
				if (count($balance_obj) > 0)
				{
					foreach ($balance_obj as $record)
					{
						$crypto = $record['crypto'];
						$balance = $record['balance'];
						$total_decimal = $new_total * 1e8;

						if ($balance > $total_decimal)
						{
							if (Arr::get($_POST, 'order_id'))
							{
								$order_id = Arr::get($_POST, 'order_id');
								DB::query('NULL', 'BEGIN')->execute();
								$transaction_obj = DB::query(Database::SELECT, "SELECT id FROM crypto_transaction WHERE order_id = :order_id AND account = :account AND crypto = :crypto AND category = 'send' AND amount = -$total_decimal")
								->param(':order_id', $order_id)
								->param(':account', $username)
								->param(':crypto', $currency)
								->execute();
								if (count($transaction_obj) < 1)
								{
									$time = time();


									//credit from buyer
									$transaction_obj = DB::query(Database::INSERT, "INSERT INTO crypto_transaction (crypto, address, category, amount, confirmation, txid, time, account, status, order_id) VALUES(:crypto, 'balance_transfer', 'send', -$total_decimal, 100, 'balance_transfer', :time, :account, '01', :order_id)")
									->param(':crypto', $currency)
									->param(':time', $time)
									->param(':account', $username)
									->param(':order_id', $order_id)
									->execute();
									if ( ! $transaction_obj)
									{
										DB::query('NULL', 'ROLLBACK')->execute();
										throw new Kohana_Exception('site_error ');
									}
									//debit to seller, set status = '10' so that it's escrow
									$transaction_obj = DB::query(Database::INSERT, "INSERT INTO crypto_transaction (crypto, address, category, amount, confirmation, txid, time, account, status, order_id) VALUES(:crypto, 'balance_transfer', 'receive', $total_decimal, 100, 'balance_transfer', :time, :account, '10', :order_id)")
									->param(':crypto', $currency)
									->param(':time', $time)
									->param(':account', $seller_username)
									->param(':order_id', $order_id)
									->execute();
									if ( ! $transaction_obj)
									{
										DB::query('NULL', 'ROLLBACK')->execute();
										throw new Kohana_Exception('site_error ');
									}
									$array_data['order_status'] = 3;
									$json_data = json_encode($array_data);
									$update_obj = DB::query(Database::UPDATE, "UPDATE public.order SET data = json_add_update(data, :json_data) WHERE id = :order_id")
									->param(':json_data', $json_data)
									->param(':order_id', $order_id)
									->execute();
									if ( ! $update_obj)
									{
										DB::query('NULL', 'ROLLBACK')->execute();
										throw new Kohana_Exception('site_error ');
									}
									DB::query('NULL', 'COMMIT')->execute();
									$msg = Arr::get($_GET, 'm');
									Request::current()->redirect("/my/purchase/detail?id=$order_id&m=pm");
								}
							}
							else if (Arr::get($_GET, 'order_id'))
							{
								$view = View::factory(TEMPLATE . '/my/cryptowallet_make_payment');
								$view->payment_msg = sprintf(I18n::get("make_payment_msg"), $order_id, $order_id);
								$view->currency = $currency;
								$view->balance = sprintf('%0.8f', (float) $balance / 1e8);
								$view->total_decimal = sprintf('%0.8f', (float) $total_decimal / 1e8);
								$view->array_balance = $array_balance;
								$view->cfg_crypto = $this->cfg_crypto;
								$view->cfg = $this->cfg;
								$view->enough_balance = 1;
								$view->order_id = $order_id;
								$this->template->content = $view;
							}
						}
					}
				}
			}
		}
	}
	
	function action_transaction()
	{
		$username = $this->user->username;
		$user_id = $this->user->id;
		$currency = Request::current()->param('id');
		if (array_key_exists($currency, $this->cfg_crypto))
		{
			$crypto_name = $this->cfg_crypto[$currency]['name'];
			$array_crypto_cfg = $this->cfg_crypto[$currency];
		
			$transaction_obj = DB::query(Database::SELECT, "SELECT id, address, category, amount, confirmation, txid, time, fee, status, order_id FROM crypto_transaction WHERE account = :account AND crypto = :crypto ORDER BY id DESC")
			->param(':account', $username)
			->param(':crypto', $currency)
			->execute();
			if ( ! $transaction_obj)
			{
				throw new Kohana_Exception('site_error ');
			}
			
			$view = View::factory(TEMPLATE . '/my/cryptotransaction');
			$view->crypto_name = $crypto_name;
			$view->transaction_obj = $transaction_obj;
			$view->min_confirmation = $array_crypto_cfg['min_confirmation'];
			$view->cfg = $this->cfg;
			$this->template->content = $view;
		}
	}
	
	function action_index()
	{
		$username = $this->user->username;
		$user_id = $this->user->id;
		$array_balance = array();
		foreach ($this->cfg_crypto as $symbol => $record)
		{
			$active = $record['active'];
			if ($active == 1)
			{
				$array_balance[$symbol] = sprintf('%0.8f', (float) 0 / 1e8);
			}
		}
		
		// we do not want to include transaction of type 'receive' that is not confirmed yet (status = '00') but we want to make sure that all 'send' transactions are counted
		$balance_obj = DB::query(Database::SELECT, "SELECT crypto, sum(amount) + sum(fee) AS balance FROM crypto_transaction WHERE account = :username AND (status = '01' OR category = 'send') GROUP BY crypto")
		->param(':username', $username)
		->execute();

		if (count($balance_obj) > 0)
		{
			foreach ($balance_obj as $record)
			{
				$crypto = $record['crypto'];
				$balance = $record['balance'];
				$array_balance[$crypto] = sprintf('%0.8f', $balance / 1e8);
			}
		}

		
		$view = View::factory(TEMPLATE . '/my/cryptowallet');
		$view->array_balance = $array_balance;
		$view->cfg_crypto = $this->cfg_crypto;
		$view->cfg = $this->cfg;
		$this->template->content = $view;
	}
}
