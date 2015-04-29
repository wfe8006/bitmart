<?php
//include("/var/www/bdk/dependencies/jsonRPCClient.php");

class Crypto
{
	var $_rpc_protocol;
	var $_rpc_host;
	var $_rpc_port;
	var $_rpc_username;
	var $_rpc_password;
	var $_rpc_info_valid;
	var $_crypto;
	var $_min_confirmation;
	var $_fee_per_kb;
	var $_crypto_commission;
	var $_withdrawal_fee;
	var $rpc_connection;
	
	
	function Crypto($rpc)
	{
		$this->cfg = Kohana::$config->load('general.default');
		$this->_rpc_info_valid = 0;
		if (count($rpc) > 0)
		{
			$this->_rpc_protocol = $rpc['rpc_protocol'];
			$this->_rpc_host = $rpc['rpc_host'];
			$this->_rpc_port = $rpc['rpc_port'];
			$this->_rpc_username = $rpc['rpc_username'];
			$this->_rpc_password = $rpc['rpc_password'];
			$this->_crypto = $rpc['crypto'];
			$this->_min_confirmation = $rpc['min_confirmation'];
			$this->_fee_per_kb = $rpc['fee_per_kb'];
			$this->_crypto_commission = $rpc['crypto_commission'];
			$this->_withdrawal_fee = $rpc['withdrawal_fee'];
			$this->rpc_connection = new jsonRPCClient($this->_rpc_protocol . '://' . $this->_rpc_username . ':' . $this->_rpc_password . '@' . $this->_rpc_host . ':' . $this->_rpc_port);

		}

	}
	
	function __destruct()
	{
		//if ( $this->_socket !== false )
		//	fclose ( $this->_socket );
	}
	
	function object_to_array($object)
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
	
	function validate_address($address = '')
	{
		$result['status'] = 0;
		$tmp_valid_bitcoin_address = $this->rpc_connection->validateaddress($address);
		if (isset($tmp_valid_bitcoin_address['code']) OR $tmp_valid_bitcoin_address == NULL)
		{
			$result['error'] = 'validate_address()';
		}
		else
		{
			$result['status'] = 1;
			$result['isvalid'] = $tmp_valid_bitcoin_address['isvalid'] == 1 ? 1 : 0;
			$result['ismine'] = $tmp_valid_bitcoin_address['ismine'] == 1 ? 1 : 0;
			$result['isscript'] = $tmp_valid_bitcoin_address['isscript'] == 1 ? 1 : 0;
			$result['pubkey'] = $tmp_valid_bitcoin_address['pubkey'];
			$result['iscompressed'] = $tmp_valid_bitcoin_address['iscompressed'] == 1 ? 1: 0;
			//Set label (A developer should use Bitcoin_Get_Address_Label to aquire the Unmodifed version of the label)
			$result['label'] = strip_tags($tmp_valid_bitcoin_address['account']);
		}
		return $result;
	}
	
	/*
	 Returns a new bitcoin address for receiving payments. If [account] is specified (recommended), it is added to the address book so payments received with the address will be credited to [account]. 
	*/
	function get_new_address($new_address_label='')
	{
		$result['status'] = 0;
		$tmp_new_address = $this->rpc_connection->getnewaddress($new_address_label);
		if (isset($tmp_new_address['code']) OR $tmp_new_address == NULL)
		{
			$result['error'] = 'get_new_address()';
		}
		else
		{
			$valid_address = $this->validate_address($tmp_new_address);
			if ($valid_address['status'] == 1)
			{
				if ($valid_address['isvalid'] == 1)
				{
					$result['status'] = 1;
					$result['address'] = $tmp_new_address;
				}
				else
				{
					$result['error'] = 'get_new_address()';
				}
			}
			else
			{
				$result['error'] = $valid_address['error'];
			}
		
		}
		return $result;
	}
	
	function get_balance($account='', $minimum_confirmations=1)
	{
		$result['status'] = 0;
		$result['balance_in_coin'] = (double) 0.00000000; //Decimal/Float/Double for display only
		$result['balance_in_satoshi'] = (int) 0; //Integers only
		$account = strip_tags($account);
		$minimum_confirmations = (int) floor($minimum_confirmations); //Make integer(if for some reason it came in as a decimal)
		if ($minimum_confirmations <= 0)
		{
			$minimum_confirmations = 0;
		}
		//$tmp_balance = $this->rpc_connection->getbalance($account, $minimum_confirmations);
		$tmp_balance = $this->rpc_connection->getbalance();
		if (isset($tmp_balance['code']) OR $tmp_balance == NULL)
		{
			$result['error'] = 'get_balance()';
		}
		else
		{
			$result['status'] = 1;
			$result['balance'] = $tmp_balance;
			//$result['balance_in_coin'] = (double) $tmp_balance;//Convert Bitcoins to satoshi so we can do math with integers.
		//	$result['balance_in_satoshi'] = (int) floor($tmp_balance * 100000000); 
		}
		return $result;
	}
	
	function get_received_by_address($address='', $minimum_confirmations=1)
	{
		$result['status'] = 0;
		$result['total_received_in_coin'] = (double) 0.00000000; //Decimal/Float/Double (THIS IS FOR ONLY DISPLAYING THE TOTAL RECEIVED BALANCE IN BITCOIN , NOT FOR DOING MATH AGAINST!!! Do math in satoshi only)
		$result['total_received_in_satoshi'] = (int) 0; //Integers only
		$address = strip_tags($address);
		$minimum_confirmations = (int) floor($minimum_confirmations); //Make integer(if for some reason it came in as a decimal)
		if ($minimum_confirmations <= 0)
		{
			$minimum_confirmations = 0;
		}
		$tmp_total_received_in_coin = $this->rpc_connection->getreceivedbyaddress($address, $minimum_confirmations);
		if (isset($tmp_total_received_in_coin['code']) OR $tmp_total_received_in_coin == NULL)
		{
			$result['error'] = 'get_balance()';
		}
		else
		{
			$result['status'] = 1;
			$result['total_received_in_coin'] = (double) $tmp_total_received_in_coin; 
			$result['total_received_in_satoshi'] = (int) floor($tmp_total_received_in_coin * 100000000); //Convert Bitcoins to satoshi so we can do math with integers.
		}
		return $result;				
	}
	
	function list_transactions($account='', $count=10, $from=0)
	{
		$result['status'] = 0;
		$transaction = $this->rpc_connection->listtransactions($account, $count, $from);
		if (isset($transaction['code']) OR $transaction == NULL)
		{
			$result['error'] = 'list_transactions()';
		}
		else
		{
			$result['status'] = 1;
			$result['transaction'] = $transaction;
		}
		return $result;				
	}
	
	function get_transaction($transaction_id)
	{
		$result['status'] = 0;
		try
		{
			$transaction = $this->rpc_connection->gettransaction($transaction_id);
			if (isset($transaction['code']) OR $transaction == NULL)
			{
				$result['error'] = 'get_transactions()';
			}
			else
			{
				$result['status'] = 1;
				$result['transaction'] = $transaction;
			}
		}
		catch (Exception $e)
		{
			//most likely it will throw error for non-wallet transaction
		}
		
		return $result;				
	}
	
	function get_block($hash)
	{
		$block = $this->rpc_connection->getblock($hash);
		if (isset($block['code']) OR $block == NULL)
		{
			$result['error'] = 'get_block()';
		}
		else
		{
			$result['status'] = 1;
			$result['block'] = $block;
		}
		return $result;				
	}
	
	//walletnotify called by the daemon when there's a transaction found in the wallet, received or sent
	function wallet_notify($transaction_id)
	{
		$result['status'] = 0;
		//$transaction_id = '797aa470d4ca6c362ce38de41422d794224e938d860c942ee44776c583229e54';
		//$a = $this->rpc_connection->gettransaction($transaction_id);
		$transaction = $this->get_transaction($transaction_id);

			
		$myFile = "/tmp/temp.txt";
		$fh = fopen($myFile, 'a+') or die("can't open file");
		$string = $this->_crypto . ' wallet() ' . date('d-m-Y h:i:s', time()) . " $transaction_id " . PHP_EOL;
		$string2 = json_encode($transaction);
		
		fwrite($fh, $string);
		fwrite($fh, $string2 . " ========== ". PHP_EOL);
		fclose($fh);
		

		if ($transaction['status'] == 1)
		{
			$crypto = $this->_crypto;
			$tx = $transaction['transaction'];
			$confirmation = $tx['confirmations'];
			if ($confirmation == '')
			{
				$confirmation = 0;
			}
			$txid = $tx['txid'];
			$time = $tx['time'];
			
			$category = $tx['details'][0]['category'];
		
			$transaction_obj = DB::query(Database::SELECT, "SELECT confirmation FROM crypto_transaction WHERE crypto = :crypto AND txid = :txid")
			->param(':crypto', $this->_crypto)
			->param(':txid', $txid)
			->execute();
			
			if (count($transaction_obj) == 0)
			{

				
				
				//The following block if ($category == 'send' won't be triggered as cryptowallet/withdraw_confirm will generate a new tx_id record and into it into the database (means there is already a 'send' record in the database. If the following block is triggered, means the txid record generated during send_coin() was not inserted into the db properly and we want to look at the situation properly. Probably send email notification etc
				if ($category == 'send')
				{
					$result['status'] = 0;
				}
				else
				{
					
					DB::query('NULL', 'BEGIN')->execute();
					
					$fee = $tx['details'][0]['fee'];
					if ($fee == '')
					{
						$fee = 0;
					}
					$account = $tx['details'][0]['account'];
					$address = $tx['details'][0]['address'];
					$amount = $tx['details'][0]['amount'] * 1e8 * ((100 - $this->_crypto_commission) / 100);
					
					
					
					
					
					
					
					
					
					$order_obj = DB::query(Database::SELECT, "SELECT o.id, o.data, o.data->>'new_total' AS total, u.username AS seller, u.email, date_part('epoch', submitted)::int AS submitted FROM public.order o LEFT JOIN public.user u ON (data->'seller_id')::text::int = u.id WHERE data->>'crypto_address' = :crypto_address AND data->>'payment_method_name' = :crypto")
					->param(':crypto_address', $address)
					->param(':crypto', $crypto)
					->execute();
					if ( ! $order_obj)
					{
						DB::query('NULL', 'ROLLBACK')->execute();
						throw new Kohana_Exception('site_error ');
					}
					if (count($order_obj) == 1)
					{
						
					
						
						
						
						
						$order_id = $order_obj[0]['id'];
						
						$db_obj = DB::query(Database::INSERT, "INSERT into crypto_transaction(crypto, address, category, amount, confirmation, txid, time, account, fee, order_id) VALUES(:crypto, :address, :category, :amount, :confirmation, :txid, :time, :account, :fee, :order_id)")
						->param(':crypto', $crypto)
						->param(':address', $address)
						->param(':category', $category)
						->param(':amount', $amount)
						->param(':confirmation', $confirmation)
						->param(':txid', $txid)
						->param(':time', $time)
						->param(':account', $account)
						->param(':fee', $fee)
						->param(':order_id', $order_id)
						->execute();
						
						if ( ! $db_obj)
						{
							DB::query('NULL', 'ROLLBACK')->execute();
							throw new Kohana_Exception('site_error ');
						}
						
						
						
						$total = $order_obj[0]['total'] * 1e8;
						$email = $order_obj[0]['email'];
						$array_data = self::object_to_array(json_decode($order_obj[0]['data']));
						$order_message = "\r\n\r\n" . I18n::get('order_summary') . "\r\n====================\r\n";
						$product = '';
						foreach ($array_data['item'] as $index => $record)
						{
							$title = HTML::chars($record['title']);
							$quantity = $record['quantity'];
							$order_message .= HTML::chars($record['title']) . ' x ' . $record['quantity'] . "\r\n";
						}
						$currency = strtoupper($array_data['new_currency_code']);
						$order_message .= "====================\r\n\r\n";
						$order_message .= I18n::get('subtotal') . ': ' . $array_data['new_grand_subtotal'] . " $currency\r\n";
						$order_message .= I18n::get('shipping') . ': ' . $array_data['new_shipping'] . " $currency\r\n";
						$order_message .= I18n::get('tax') . ': ' . $array_data['new_tax'] . " $currency\r\n";
						$order_message .= I18n::get('total') . ': ' . $array_data['new_total'] . " $currency\r\n";
						$order_message .= I18n::get('payment_method') . ': ' . $array_data['payment_method'] . "\r\n";
						$order_message .= I18n::get('shipping_service') . ': ' . $array_data['shipping_service'] . "\r\n";
						$order_message .= I18n::get('order_date') . ': ' .  date('M d, Y', $order_obj[0]['submitted']) . "\r\n";

						//if the amount received is the same with/greater than the amount request, mark the order status = paid + send an email notification to the seller,
						//test value
						//$total = 30000000;
						
						if ($amount >= $total)
						{
							
							//order_status = 3: payment_received, hardcoded
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
							$email_subject = sprintf(I18n::get('email.subject.payment_received'), $order_id);
							$email_message = sprintf(I18n::get('email.message.payment_received'), $amount / 1e8 . ' ' . strtoupper($crypto), $order_id, $order_message);
							$transport = Swift_MailTransport::newInstance();
							$mailer = Swift_Mailer::newInstance($transport);
							$message = Swift_Message::newInstance($email_subject)
							->setFrom(array($this->cfg["from_email"] => $this->cfg["site_name"] . ' Support'))
							->setTo($email)
							->setBody($email_message, 'text/plain');
							$mailer->send($message);
							
							
							$transport = Swift_MailTransport::newInstance();
							$mailer = Swift_Mailer::newInstance($transport);
							$message = Swift_Message::newInstance("new transaction for order $order_id $currency")
							->setFrom(array($this->cfg["from_email"] => $this->cfg["site_name"] . ' Support'))
							->setTo($this->cfg["from_email"])
							->setBody('', 'text/plain');
							$mailer->send($message);

							
							
							//print"<br>$email_subject";
							//print"<br>$email_message";
						}
						
						
						
						
					}
					$result['status'] = 1;
					DB::query('NULL', 'COMMIT')->execute();
				}
				
				
				
				
				
				
				
				
				
				
			}
			else
			{
				//wallet_notify() might be triggered twice..
				$result['status'] = 1;
			}
		}
		else
		{
			$result['error'] = 'wallet_notify()';
		}
		return $result;
	}
	
	//blocknotify called by the daemon when there's a new block created (block contains a list of transactions)
	function block_notify($blockhash)
	{
		$result['status'] = 0;
		$myFile = "/tmp/temp.txt";
		$fh = fopen($myFile, 'a+') or die("can't open file");
		$string = $this->_crypto . ' block function() ' . date('d-m-Y h:i:s', time()) . " $blockhash " . PHP_EOL;
		fwrite($fh, $string);
		fclose($fh);
		
		/*
		DB::query(Database::UPDATE, "UPDATE crypto_transaction SET status = CASE WHEN confirmation + 1 >= :min_confirmation THEN 1::bit(1) ELSE 0::bit(1) END, confirmation = confirmation + 1 WHERE status = '0' AND crypto = :crypto")
			->param(':min_confirmation', $this->_min_confirmation)
			->param(':crypto', $this->_crypto)
			->execute();
		*/

		try
		{
			$block = $this->get_block($blockhash);
		}
		catch (Exception $e)
		{
			throw new Kohana_Exception('$this->get_block');
		}

		if ($block['status'] == 1)
		{
			
			//first step
			//checking each transaction in the new block, if it doesn't exist in the db, insert it via wallet_notify(). Note that kittehcoin + ppcoin don't have wallet_notify handler
			$height = $block['block']['height'];
			$time = time();
			$array_tx = $block['block']['tx'];
			if (count($array_tx) > 0)
			{
				$status = 1;
				foreach ($array_tx as $index => $tx_id)
				{
					$transaction = $this->get_transaction($tx_id);
					if ($transaction['status'] == 1)
					{
						$wallet = $this->wallet_notify($tx_id);
						
						if ($wallet['status'] == 0)
						{
							$status = 0;
							break;
						}
					}
				}
			}
			$transaction_obj = DB::query(Database::SELECT, "SELECT id FROM crypto_block WHERE hash = :hash AND crypto_type = :crypto")
			->param(':hash', $blockhash)
			->param(':crypto', $this->_crypto)
			->execute();
			

			if (count($transaction_obj) == 0)
			{
				$db_obj = DB::query(Database::INSERT, "INSERT into crypto_block(crypto_type, hash, height, time, status) VALUES(:crypto, :hash, :height, :time, '$status')")
				->param(':crypto', $this->_crypto)
				->param(':hash', $blockhash)
				->param(':height', $height)
				->param(':time', $time)
				->execute();
			}
			

			//second step
			//query each transaction that is not fully confirmed, then update the number of confirmations
			$transaction_obj = DB::query(Database::SELECT, "SELECT id, category, txid, confirmation, order_id FROM crypto_transaction WHERE status = '00' AND crypto = :crypto")
			->param(':crypto', $this->_crypto)
			->execute();
			if (count($transaction_obj) > 0)
			{
				foreach ($transaction_obj as $record)
				{
					$category = $record['category'];
					$id = $record['id'];
					$txid = $record['txid'];
					$db_confirmation = $record['confirmation'];
					$order_id = $record['order_id'];
					$transaction = $this->get_transaction($txid);
					
					if ($transaction['status'] == 1)
					{
						$tx = $transaction['transaction'];
						$confirmation = $tx['confirmations'];
						if ($confirmation >= $this->_min_confirmation)
						{
							
							if ($category == 'send')
							{
								$status = '01';
							}
							else
							{
								//the transaction now has full confirmation, but we still need to wait for the buyer to release the payment, after that we will change it from (binary)10 (2 - fully confirmed + not released) to (binary)01 (1 - fully confirmed + released)
								$status = '10';
								
								$order_obj = DB::query(Database::SELECT, "SELECT u.email, o.data FROM public.order o LEFT JOIN public.user u ON o.buyer_id = u.id WHERE o.id = :order_id")
								->param(':order_id', $order_id)
								->execute();
								
								//check for instant digital delivery
								if (count($order_obj) == 1)
								{
									$array_data = self::object_to_array(json_decode($order_obj[0]['data']));
									$email = $order_obj[0]['email'];
									//$array_data['new_grand_subtotal']
								
									
									//total items for instant digital delivery (eg: 1 x battlefield 4 code and 3 x  minecraft code
									$total_idd_quantity = 0;
									$db_idd_quantity = 0;
									$array_digital_content_id = array();
									foreach ($array_data['item'] as $listing_data_id => $value)
									{
										$idd = $value['idd'];
										$quantity = $value['quantity'];
										$total_idd_quantity += $quantity;
										if ($idd == 1)
										{
											$digital_content_obj = DB::query(Database::SELECT, "SELECT id, content FROM digital_content WHERE used = '0' AND active = '1' AND listing_data_id = :listing_data_id LIMIT :quantity")
											->param(':listing_data_id', $listing_data_id)
											->param(':quantity', $quantity)
											->execute();
											
											$db_idd_quantity += count($digital_content_obj);
											if (count($digital_content_obj) > 0)
											{
												foreach ($digital_content_obj as $record)
												{
													$digital_content_id = $record['id'];
													$content = $record['content'];
													$array_digital_content_id[] = $digital_content_id;
												}
												
												
												$query_digital_content_id = implode(',', $array_digital_content_id);
												DB::query(Database::UPDATE, "UPDATE digital_content SET order_id = :order_id, used = '1' WHERE id IN ($query_digital_content_id)")
												->param(':order_id', $order_id)
												->execute();
											}
										}
										/*
										print"<pre>";
										print_r($array_data);
										print"</pre>";
										print "<br>query_digital_content_id: $query_digital_content_id";
										print "<br>total_idd_quantity: $total_idd_quantity";
										print "<br>db_idd_quantity: $db_idd_quantity";
										*/
									}
									if ($db_idd_quantity > 0)
									{
										$email_subject = sprintf(I18n::get('email.subject.idd'), $order_id);
										$url = "https://" . $this->cfg["www_domain"]. "/my/purchase/detail?id=$order_id";
										$email_message = sprintf(I18n::get('email.message.idd'), $order_id, $url);

										$transport = Swift_MailTransport::newInstance();
										$mailer = Swift_Mailer::newInstance($transport);
										$message = Swift_Message::newInstance($email_subject)
										->setFrom(array($this->cfg["from_email"] => $this->cfg["site_name"] . ' Support'))->setTo($email)
										->setBody($email_message, 'text/plain');
										$mailer->send($message);

										if ($total_idd_quantity == $db_idd_quantity)
										{
											//set status to shipped
											$array_data['order_status'] = 11;
										}
										else
										{
											$array_data['order_status'] = 10;
										}
										$json_data = json_encode($array_data);
										DB::query(Database::UPDATE, "UPDATE public.order SET data = json_add_update(data, :json_data) WHERE id = :order_id")
										->param(':json_data', $json_data)
										->param(':order_id', $order_id)
										->execute();
									
									}
								}
							}
						}
						else
						{
							$status = '00';
						}
						if ($confirmation > 10)
						{
							$confirmation = 10;
						}
						DB::query(Database::UPDATE, "UPDATE crypto_transaction SET status = '$status', confirmation = ':confirmation' WHERE id = :id")
						->param(':confirmation', $confirmation)
						->param(':id', $id)
						->execute();
					}
				}
			}
			else
			{
				
			}
		}
		else
		{
			$result['error'] = 'block_notify()';
		}
		return $result;
	}
	
	//recipient_amount in integer only!! no decimal points!!!
	function send_coin($address, $recipient_amount)
	{
		$result['status'] = 0;
		$result['error'] = '';
		$unspent = $this->rpc_connection->listunspent();
		if (isset($unspent['code']) OR $unspent == NULL)
		{
			$result['error'] = 'send_coin()';
		}
		else
		{
			$array_input = array();
			$input_balance_before_fee = 0;
			
			foreach ($unspent as $record)
			{
				if ($input_balance_before_fee < $recipient_amount)
				{
					$array_input[] = array("txid" => $record["txid"], "vout" => $record["vout"]);
					$input_balance_before_fee += (int) round($record["amount"] * 1e8);
				}
			}
			
			if (count($unspent) < 1)
			{
				$result['error'] = 'send_coin(): no unspent outputs available';
			}
			
			//do not use setaccount here to set account name for address because the recipient address might belong to third party owner
			// fee calculate by ($perkb * size in kb) following the formula at:
			// http://bitcoin.stackexchange.com/questions/1195/how-to-calculate-transaction-size-before-sending
			// there are two outputs, one is the change output for bitmart (collecting withdrawal fee), the other output is the recipient address (2 * 34 + 10) = 78
			//There are always 3 array elements in jsonrpc gettransaction, 2 elements for bitmart change address + 1 element for recipient address, here we don't take $tx['details'][0] directly, but we split them based on address count, so, bitmart address count is always 2 and recipient address count is 1
			
			
			$input_count = count($array_input);
			
			$transaction_fee = (int) max($this->_fee_per_kb * floor((($input_count * 148) + 78) / 1024), $this->_fee_per_kb);
			$input_balance_after_fee = $input_balance_before_fee - $transaction_fee;
			$bitmart_amount = $input_balance_after_fee - $recipient_amount;
			if ($bitmart_amount > 0)
			{
				$record = $this->get_new_address();
				if ($record['status'] == 1)
				{
					$array_address[$record['address']] = $bitmart_amount / 1e8;
				}
				else
				{
					$result['error'] = $record['error'];
				}
			}
			
			$array_address[$address] = $recipient_amount / 1e8;
			
			/*
			print"<br>input count: $input_count";
			print"<br>transaction size: " . (($input_count * 148) + 78);
			print"<br>input balance before fee: $input_balance_before_fee === " . $input_balance_before_fee / 1e8;
			print"<br>transaction fee: $transaction_fee === " . $transaction_fee / 1e8;
			print"<br>input balance after fee: $input_balance_after_fee === " . $input_balance_after_fee / 1e8;
			print"<br>recipient amount: $recipient_amount === " . $recipient_amount / 1e8;
			print"<br>bitmart amount: $bitmart_amount === " . $bitmart_amount / 1e8;
			*/
			
			if ($result['error'] == 0)
			{
				if ($input_balance_after_fee < 0)
				{
					$result['error'] = 'send_coin(): insufficient fund';
				}
				else
				{
					$rawtx = $this->rpc_connection->createrawtransaction($array_input, $array_address);
					if (isset($rawtx['code']) OR $rawtx == NULL)
					{
						$result['error'] = 'send_coin() createrawtransaction';
					}
					else
					{
						$signedtx = $this->rpc_connection->signrawtransaction($rawtx);
						if (isset($signedtx['code']) OR $signedtx == NULL)
						{
							$result['error'] = 'send_coin() signrawtransaction';
						}
						else
						{
							if ($signedtx["complete"] == 1)
							{
								$txid = $this->rpc_connection->sendrawtransaction($signedtx["hex"]);
								if (isset($rawtx['code']) OR $rawtx == NULL)
								{
									$result['error'] = 'send_coin() sendrawtransaction';
								}
								else
								{
									$result['txid'] = $txid;
									$result['status'] = 1;
								}
							}
							else
							{
								$result['error'] = 'send_coin(): sendrawtransaction transaction did not sign completely';
							}
						}
					}
				}
			}
		}
		return $result;
	}
}

