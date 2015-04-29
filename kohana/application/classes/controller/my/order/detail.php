<?php
$user_id = (int)$this->auth->get_user()->id;
$username = $this->auth->get_user()->username;
$user_email = (int)$this->auth->get_user()->email;
$order_id = (int) Arr::get($_GET, 'id', Arr::get($_POST, 'id'));

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
	return array_map('object_to_array', $object);
}

function send_update_email($order_id, $order_status, $initiated_by = '')
{
	$cfg = Kohana::$config->load('general.default');
	$order_obj2 = DB::query(Database::SELECT, "SELECT o.id, o.data, ub.username AS buyer_username, ub.email AS buyer_email, us.username AS seller_username, us.email AS seller_email, date_part('epoch', submitted)::int AS submitted FROM public.order o LEFT JOIN public.user ub ON o.buyer_id = ub.id LEFT JOIN public.user us ON o.seller_id = us.id WHERE o.id = :order_id")
	->param(':order_id', $order_id)
	->execute();
	if ( ! $order_obj2)
	{
		DB::query('NULL', 'ROLLBACK')->execute();
		throw new Kohana_Exception('site_error ');
	}
	
	if (count($order_obj2) == 1)
	{
		$order_id = $order_obj2[0]['id'];
		$buyer_username = $order_obj2[0]['buyer_username'];
		$buyer_email = $order_obj2[0]['buyer_email'];
		$seller_username = $order_obj2[0]['seller_username'];
		$seller_email = $order_obj2[0]['seller_email'];
	
		$array_data = object_to_array(json_decode($order_obj2[0]['data']));
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
		$order_message .= I18n::get('order_date') . ': ' .  date('M d, Y', $order_obj2[0]['submitted']) . "\r\n";
		
		
		if ($order_status == 'order_shipped')
		{
			$email = $buyer_email;
			$email_subject = sprintf(I18n::get('email.subject.order_shipped'), $order_id);
			$email_message = sprintf(I18n::get('email.message.order_shipped'), $order_id, $order_message);
		}
		else if ($order_status == 'order_cancelled')
		{
			$email = $buyer_email;
			$email_subject = sprintf(I18n::get('email.subject.order_cancelled'), $order_id);
			$email_message = sprintf(I18n::get('email.message.order_cancelled'), $order_id, $order_message);
		}
		else if ($order_status == 'payment_release_requested')
		{
			$email = $buyer_email;
			$url = "https://" . $cfg['www_domain'] . "/my/purchase/detail?id=$order_id";
			$email_subject = sprintf(I18n::get('email.subject.payment_release_requested'), $order_id);
			$email_message = sprintf(I18n::get('email.message.payment_release_requested'), $order_id, date('M d, Y h:i', time()), $url, $order_message);
		}
		else if ($order_status == 'payment_release_denied')
		{
			$email = $seller_email;
			$email_subject = sprintf(I18n::get('email.subject.payment_release_denied'), $order_id);
			$email_message = sprintf(I18n::get('email.message.payment_release_denied'), $order_id, $order_message);
		}
		else if ($order_status == 'payment_released')
		{
			$email = $seller_email;
			$email_subject = sprintf(I18n::get('email.subject.payment_released'), $order_id);
			$email_message = sprintf(I18n::get('email.message.payment_released'), $order_id, $order_message);
		}
		else if ($order_status == 'dispute_opened')
		{
			$dispute_msg = $dispute_by == 'buyer' ? "the buyer $buyer_username" : "the seller $seller_username";
			$email = array($buyer_email, $seller_email);
			$email_subject = sprintf(I18n::get('email.subject.dispute_opened'), $order_id);
			$email_message = sprintf(I18n::get('email.message.dispute_opened'), $dispute_msg, $order_id, $order_message);
		}
		else if ($order_status == 'order_cancelled')
		{
			$cancel_msg = $dispute_by == 'buyer' ? "the buyer $buyer_username" : "the seller $seller_username";
			$email = array($buyer_email, $seller_email);
			$email_subject = sprintf(I18n::get('email.subject.order_cancelled'), $order_id);
			$email_message = sprintf(I18n::get('email.message.order_cancelled'), $cancel_msg, $order_id, $order_message);
		}
		
		$transport = Swift_MailTransport::newInstance();
		$mailer = Swift_Mailer::newInstance($transport);
		$message = Swift_Message::newInstance($email_subject)
		->setFrom(array($cfg["support_email"] => $cfg["site_name"] . ' Support'))
		->setTo($email)
		->setBody($email_message, 'text/plain');
		$mailer->send($message);
	}

}

$view = View::factory(TEMPLATE . '/my/order/detail');

if (Request::current()->controller() == 'purchase')
{
	$order_obj = DB::query(Database::SELECT, "SELECT o.id AS order_id, seller_id, u.username AS seller, data, date_part('epoch', submitted)::int AS submitted, os.name AS order_status FROM public.order o LEFT JOIN public.user u ON o.seller_id = u.id LEFT JOIN order_status os ON (o.data->>'order_status')::integer = os.id WHERE buyer_id = :user_id AND o.id = :order_id")
	->param(':user_id', $user_id)
	->param(':order_id', $order_id)
	->execute();
	$view->buyer = $this->auth->get_user()->username;
	$view->seller = $order_obj[0]['seller'];
	$view->menu = 'my_menu';
	$view->url = $url = 'purchase';
	$user_rating_query = DB::query(Database::SELECT, "SELECT buyer_feedback AS feedback FROM user_rating WHERE order_id = :order_id AND (buyer_feedback->>'status')::integer = 0")
	->param(':order_id', $order_id);
	$identity = 'buyer';
}
else
{
	$order_obj = DB::query(Database::SELECT, "SELECT o.id AS order_id, buyer_id, u.username AS buyer, data, date_part('epoch', submitted)::int AS submitted FROM public.order o LEFT JOIN public.user u ON o.buyer_id = u.id WHERE seller_id = :user_id AND o.id = :order_id")
	->param(':user_id', $user_id)
	->param(':order_id', $order_id)
	->execute();
	$view->buyer = $order_obj[0]['buyer'];
	$view->seller = $this->auth->get_user()->username;
	$view->menu = 'menu';
	$view->url = $url = 'order';
	$user_rating_query = DB::query(Database::SELECT, "SELECT seller_feedback AS feedback FROM user_rating WHERE order_id = :order_id AND (seller_feedback->>'status')::integer = 0")
	->param(':order_id', $order_id);
	$identity = 'seller';
}


if (count($order_obj) > 0)
{
	if ($_POST)
	{
		DB::query('NULL', 'BEGIN')->execute();
		$array_order = array();
		$order_id = (int) Arr::get($_POST, 'id');
		$order_status = (int) Arr::get($_POST, 'order_status');
		
		$array_rating = array();
		$array_rating['rating'] = (int) Arr::get($_POST, 'rating');
		$array_rating['feedback'] = substr(Arr::get($_POST, 'feedback'), 0, 100);
		if (Arr::get($_POST, 'submit_deny'))
		{
			if ($username == $view->buyer)
			{
				//escrow payment denied, reset the time to 0 and notify seller about it
				$array_data['escrow'] = array();
				$array_data['escrow']['requested'] = 0;
				$json_data = json_encode($array_data);
			
				DB::query(Database::UPDATE, "UPDATE public.order SET data = json_add_update(data, :json_data) WHERE id = :order_id")
				->param(':json_data', $json_data)
				->param(':order_id', $order_id)
				->execute();
				send_update_email($order_id, 'payment_release_denied');
			}
		}
		else if (Arr::get($_POST, 'submit_request'))
		{
			$action_request = Arr::get($_POST, 'action_request', 0);
			if ($action_request == 1 AND $username == $view->seller)
			{
				$array_data['escrow'] = array();
				$array_data['escrow']['requested'] = time();
				$json_data = json_encode($array_data);
			
				DB::query(Database::UPDATE, "UPDATE public.order SET data = json_add_update(data, :json_data) WHERE id = :order_id")
				->param(':json_data', $json_data)
				->param(':order_id', $order_id)
				->execute();
				$code = 'rpr';
				send_update_email($order_id, 'payment_release_requested');
			}
		}
		else if (Arr::get($_POST, 'submit_release') OR Arr::get($_POST, 'submit_accept'))
		{
			$action_release = Arr::get($_POST, 'action_release', 0);
			$action_accept = Arr::get($_POST, 'action_accept', 0);
			//escrow payment can be only released by the buyer
			if (($action_release == 1 OR $action_accept == 1) AND $username == $view->buyer)
			{
				DB::query(Database::UPDATE, "UPDATE crypto_transaction SET status = '01' WHERE order_id = :order_id AND status = '10'")
				->param(':order_id', $order_id)
				->execute();
				
				//mark order_status as completed if escrow payment has been released
				$array_data['order_status'] = 12;
				$array_data['escrow'] = array();
				$array_data['escrow']['released'] = time();
				$json_data = json_encode($array_data);
			
				DB::query(Database::UPDATE, "UPDATE public.order SET data = json_add_update(data, :json_data) WHERE id = :order_id")
				->param(':json_data', $json_data)
				->param(':order_id', $order_id)
				->execute();
				$code = 'pr';
				
				send_update_email($order_id, 'payment_released');
			}
		}
		else if (Arr::get($_POST, 'submit_dispute'))
		{
			$action_dispute = Arr::get($_POST, 'action_dispute', 0);
			if ($action_dispute == 1)
			{
				$initiated_by = Request::current()->controller() == 'purchase' ? 'buyer' : 'seller';
				send_update_email($order_id, 'dispute_opened', $initiated_by);


				$array_data['dispute'] = array();
				$array_data['dispute']['by'] = $initiated_by;
				$array_data['dispute']['time'] = time();
				
				$json_data = json_encode($array_data);
				DB::query(Database::UPDATE, "UPDATE public.order SET data = json_add_update(data, :json_data) WHERE id = :order_id")
				->param(':json_data', $json_data)
				->param(':order_id', $order_id)
				->execute();
				$code = 'do';
				

				//notify admin
				$transport = Swift_MailTransport::newInstance();
				$mailer = Swift_Mailer::newInstance($transport);
				$message = Swift_Message::newInstance("Dispute opened for order #$order_id")
				->setFrom(array($cfg["support_email"] => $cfg["site_name"] . ' Support'))
				->setTo($cfg["support_email"])
				->setBody("Dispute opened for order #$order_id", 'text/plain');
				$mailer->send($message);
			}
		}
		else if (Arr::get($_POST, 'submit_cancel'))
		{
			$action_cancel = Arr::get($_POST, 'action_cancel', 0);
			$cancellation_reason = Arr::get($_POST, 'cancellation_reason', '');
			if ($action_cancel == 1 AND $cancellation_reason != '')
			{
				$initiated_by = Request::current()->controller() == 'purchase' ? 'buyer' : 'seller';
				send_update_email($order_id, 'order_cancelled', $initiated_by);

				$array_data['order_status'] = 13;
				$array_data['cancel'] = array();
				$array_data['cancel']['by'] = $initiated_by;
				$array_data['cancel']['time'] = time();
				$array_data['cancel']['reason'] = $cancellation_reason;

				$json_data = json_encode($array_data);
				DB::query(Database::UPDATE, "UPDATE public.order SET data = json_add_update(data, :json_data) WHERE id = :order_id")
				->param(':json_data', $json_data)
				->param(':order_id', $order_id)
				->execute();
				$code = 'oc';
			
				
				//notify admin
				$transport = Swift_MailTransport::newInstance();
				$mailer = Swift_Mailer::newInstance($transport);
				$message = Swift_Message::newInstance("Order cancelled for order #$order_id")
				->setFrom(array($cfg["support_email"] => $cfg["site_name"] . ' Support'))
				->setTo($cfg["support_email"])
				->setBody("Order cancelled for order #$order_id", 'text/plain');
				$mailer->send($message);
			}
		}
		else if (Arr::get($_POST, 'submit_rating'))
		{
			$array_rating['timestamp'] = time();
			$array_rating['status'] = 1;
			$json_rating = json_encode($array_rating);
			if ($identity == 'seller')
			{
				$result = DB::query(Database::UPDATE, "UPDATE user_rating SET seller_feedback = json_add_update(buyer_feedback, :json_rating) WHERE order_id = :order_id AND (seller_feedback->>'status')::integer = 0")
				->param(':json_rating', $json_rating)
				->param(':order_id', $order_id)
				->execute();
				$id = $order_obj[0]['buyer_id'];
				$rating_obj = DB::query(Database::SELECT, "SELECT COUNT(*), CASE (buyer_feedback->>'rating')::integer WHEN 1 THEN 'negative' WHEN 2 THEN 'negative' WHEN 3 THEN 'neutral' WHEN 4 THEN 'positive' WHEN 5 THEN 'positive' END FROM user_rating WHERE seller_id = :id AND (buyer_feedback->>'status')::integer = 1 GROUP BY CASE (buyer_feedback->>'rating')::integer WHEN 1 THEN 'negative' WHEN 2 THEN 'negative' WHEN 3 THEN 'neutral' WHEN 4 THEN 'positive' WHEN 5 THEN 'positive' END UNION ALL SELECT COUNT(*), CASE (seller_feedback->>'rating')::integer WHEN 1 THEN 'negative' WHEN 2 THEN 'negative' WHEN 3 THEN 'neutral' WHEN 4 THEN 'positive' WHEN 5 THEN 'positive' END FROM user_rating WHERE buyer_id = :id AND (seller_feedback->>'status')::integer = 1 GROUP BY CASE (seller_feedback->>'rating')::integer WHEN 1 THEN 'negative' WHEN 2 THEN 'negative' WHEN 3 THEN 'neutral' WHEN 4 THEN 'positive' WHEN 5 THEN 'positive' END")
				->param(':id', $id)
				->execute();
				$rating_query = 'buyer_id';
			}
			else
			{
				$result = DB::query(Database::UPDATE, "UPDATE user_rating SET buyer_feedback = json_add_update(buyer_feedback, :json_rating) WHERE order_id = :order_id AND (buyer_feedback->>'status')::integer = 0")
				->param(':json_rating', $json_rating)
				->param(':order_id', $order_id)
				->execute();
				
				$id = $order_obj[0]['seller_id'];
				$rating_obj = DB::query(Database::SELECT, "SELECT COUNT(*), CASE (buyer_feedback->>'rating')::integer WHEN 1 THEN 'negative' WHEN 2 THEN 'negative' WHEN 3 THEN 'neutral' WHEN 4 THEN 'positive' WHEN 5 THEN 'positive' END FROM user_rating WHERE seller_id = :id AND (buyer_feedback->>'status')::integer = 1 GROUP BY CASE (buyer_feedback->>'rating')::integer WHEN 1 THEN 'negative' WHEN 2 THEN 'negative' WHEN 3 THEN 'neutral' WHEN 4 THEN 'positive' WHEN 5 THEN 'positive' END UNION ALL SELECT COUNT(*), CASE (seller_feedback->>'rating')::integer WHEN 1 THEN 'negative' WHEN 2 THEN 'negative' WHEN 3 THEN 'neutral' WHEN 4 THEN 'positive' WHEN 5 THEN 'positive' END FROM user_rating WHERE buyer_id = :id AND (seller_feedback->>'status')::integer = 1 GROUP BY CASE (seller_feedback->>'rating')::integer WHEN 1 THEN 'negative' WHEN 2 THEN 'negative' WHEN 3 THEN 'neutral' WHEN 4 THEN 'positive' WHEN 5 THEN 'positive' END")
				->param(':id', $id)
				->execute();
				$rating_query = 'seller_id';
			}

			if ( ! $rating_obj)
			{
				DB::query('NULL', 'ROLLBACK')->execute();
				throw new Kohana_Exception('site_error ');
			}
			if (count($rating_obj) > 0)
			{
				$array_rating = array();
				$array_rating['positive'] = 0;
				$array_rating['negative'] = 0;
				$array_rating['neutral'] = 0;
				foreach ($rating_obj as $record)
				{
					$total = $record['count'];
					$rating = $record['case'];
					$array_rating[$rating] += $total;
				}
				$total_rating = $array_rating['positive'] + $array_rating['negative'] + $array_rating['neutral'];
				$rating = round(($array_rating['positive'] * 100) / $total_rating);
				$array_data = array();
				$array_data['total_rating'] = $total_rating;
				$array_data['rating'] = "$rating";
				$json_data = json_encode($array_data);
				$result = DB::query(Database::UPDATE, "UPDATE public.user SET info = json_add_update(info, :json_data) WHERE id = :$rating_query")
				->param(':json_data', $json_data)
				->param(":$rating_query", $id)
				->execute();
			}
			$code = 'fs';
		}
		else if (Arr::get($_POST, 'submit_order_status'))
		{
			if ($username == $view->seller)
			{
				$array_data['order_status'] = $order_status;
				$json_data = json_encode($array_data);
				$result = DB::query(Database::UPDATE, "UPDATE public.order SET data = json_add_update(data, :json_data) WHERE id = :order_id")
				->param(':json_data', $json_data)
				->param(':order_id', $order_id)
				->execute();
				if ( ! $result)
				{
					DB::query('NULL', 'ROLLBACK')->execute();
					throw new Kohana_Exception('site_error ');
				}
				
				if ($order_status == 11)
				{
					send_update_email($order_id, 'order_shipped');
				}
				else if ($order_status == 13)
				{
					send_update_email($order_id, 'order_cancelled');
				}
			}
		}
		else if (Arr::get($_POST, 'submit_message'))
		{
			$post = Validation::factory($_POST)
			->rule('message', 'not_empty');
			if ($post->check()) 
			{
				$message = Arr::get($_POST, 'message');
				$time = time();
				$result = DB::query(Database::INSERT, "INSERT INTO order_message(order_id, posted, message, user_id) VALUES(:order_id, :posted, :message, :user_id)")
				->param(':order_id', $order_id)
				->param(':posted', $time)
				->param(':message', $message)
				->param(':user_id', $user_id)
				->execute();
				if ( ! $result)
				{
					DB::query('NULL', 'ROLLBACK')->execute();
					throw new Kohana_Exception('site_error ');
				}
				else
				{
					if ($url == 'purchase')
					{
						$sender = "The buyer $username";
						$recipient = 'seller_id';
					}
					else
					{
						$sender = "The seller $username";
						$recipient = 'buyer_id';
					}
					$user_obj = DB::query(Database::SELECT, "SELECT u.email FROM public.order o LEFT JOIN public.user u ON o.$recipient = u.id WHERE o.id = :order_id")
					->param(':order_id', $order_id)
					->execute();
					if ( ! $user_obj)
					{
						DB::query('NULL', 'ROLLBACK')->execute();
						throw new Kohana_Exception('site_error ');
					}
					else
					{
						$email = $user_obj[0]['email'];
						
					}
					$email_url = "https://" . $this->cfg['www_domain'] . "/my/$url/detail?id=$order_id";
					$email_subject = sprintf(I18n::get('email.subject.order_message_received'), $order_id);
					$email_message = sprintf(I18n::get('email.message.order_message_received'), $sender, $order_id, $message, $email_url);
					$transport = Swift_MailTransport::newInstance();
					$mailer = Swift_Mailer::newInstance($transport);
					$message = Swift_Message::newInstance($email_subject)
					->setFrom(array($cfg["support_email"] => $cfg["site_name"] . ' Support'))
					->setTo($email)
					->setBody($email_message, 'text/plain');
					$mailer->send($message);
				}
			}
		}
		DB::query('NULL', 'COMMIT')->execute();
		if ($code != '')
		{
			Request::current()->redirect("/my/$url/detail?id=$order_id&m=$code");
		}
		else
		{
			Request::current()->redirect("/my/$url/detail?id=$order_id&m=u");
		}
	}

	
	$view->digital_content_obj = DB::query(Database::SELECT, "SELECT id, content, listing_data_id FROM digital_content WHERE order_id = :order_id")
	->param(':order_id', $order_id)
	->execute();
	$view->cfg_crypto = $this->cfg_crypto;
	$view->array_order = $array_order = object_to_array(json_decode($order_obj[0]['data']));
	$address = HTML::chars($array_order['shipping_address']);
	$address_old = array('&lt;address&gt;', '&lt;/address&gt;', '&lt;b&gt;', '&lt;/b&gt;', '&lt;br&gt;');
	$address_new = array('<address>', '</address>', '<b>', '</b>', '<br>');
	$address = str_replace($address_old, $address_new, $address);
	$view->shipping_address = $address;
	$view->order_status = $view->array_order['order_status'];
	$user_rating_obj = $user_rating_query->execute();

	
	
	if (count($user_rating_obj) > 0)
	{
		$array_feedback = object_to_array(json_decode($user_rating_obj[0]['feedback']));
		$view->rating = $array_feedback['rating'];
		$view->feedback = $array_feedback['feedback'];
		$view->user_rating_obj = $user_rating_obj;
	}
	
	$msg = Arr::get($_GET, 'm');
	if ($msg != '')
	{
		if ($msg == 'u')
		{
			$view->msg = I18n::get('record_updated');
		}
		else if ($msg == 'pr')
		{
			$view->msg = I18n::get('payment_released_message');
		}
		else if ($msg == 'do')
		{
			$view->msg = I18n::get('dispute_opened_message');
		}
		else if ($msg == 'oc')
		{
			$view->msg = I18n::get('order_cancelled_message');
		}
		else if ($msg == 'fs')
		{
			$view->msg = I18n::get('feedback_submitted_message');
		}
		else if ($msg == 'rpr')
		{
			$view->msg = I18n::get('request_payment_release_message');
		}
		else if ($msg == 'pm')
		{
			$view->msg = I18n::get('payment_made');
		}
	}
	
	$currency = $array_order['new_currency_code'];
	$view->enough_balance = 0;
	if (array_key_exists(strtolower($currency), $this->cfg_crypto))
	{
		$view->is_crypto = 1;
		$array_crypto_cfg = $this->cfg_crypto[$currency];
		$view->min_confirmation = $array_crypto_cfg['min_confirmation'];
		
		if (in_array($view->order_status, array(2)) AND $url == 'purchase')
		{
			// we do not want to include transaction of type 'receive' that is not confirmed yet (status = '00') but we want to make sure that all 'send' transactions are counted
			$balance_obj = DB::query(Database::SELECT, "SELECT crypto, sum(amount) + sum(fee) AS balance FROM crypto_transaction WHERE account = :username AND (status = '01' OR category = 'send') AND crypto = :crypto GROUP BY crypto")
			->param(':username', $username)
			->param(':crypto', $currency)
			->execute();

			if (count($balance_obj) > 0)
			{
				foreach ($balance_obj as $record)
				{
					$crypto = $record['crypto'];
					$balance = $record['balance'];
					$total_decimal = $active['new_total'] * 1e8;
					if ($balance > $total_decimal)
					{
						$view->enough_balance = 1;
					}
				}
			}
		}
	}
	else
	{
		$view->is_crypto = 0;
	}
		

		

	$view->crypto_transaction_obj = DB::query(Database::SELECT, "SELECT crypto, confirmation, time, amount, status FROM crypto_transaction WHERE order_id = :order_id")
	->param(':order_id', $order_id)
	->execute();
	
	$view->order_message_obj = DB::query(Database::SELECT, "SELECT om.id, om.posted, om.message, om.user_id, u.username FROM order_message om LEFT JOIN public.user u ON om.user_id = u.id WHERE om.order_id = :order_id ORDER BY om.id DESC")
	->param(':order_id', $order_id)
	->execute();

	$view->user_id = $user_id;
	$view->order_status_obj = DB::query(Database::SELECT, "SELECT id, name FROM order_status ORDER BY odr")->execute();
	$view->order_id = $order_obj[0]['order_id'];
	$view->order_date = date('M d, Y', $order_obj[0]['submitted']);
	$view->cfg = $this->cfg;
	$this->template->content = $view;
}
else
{
	Request::current()->redirect("/");
}