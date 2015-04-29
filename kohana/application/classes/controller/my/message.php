<?php
class Controller_My_Message extends Controller_System
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
	
	function action_process_username(Validation $post = NULL)
	{
		$username = Arr::get($_POST, 'recipient_username');
		$my_username = $this->auth->get_user()->username;
		$valid = 0;
		if ($username != $my_username)
		{
			$username_obj = DB::query(Database::SELECT, "SELECT COUNT(id) AS total FROM public.user WHERE username = :username")
			->param(':username', $username)
			->execute();
			$this->template->title = '';
			$valid = $username_obj[0]['total'] == 1 ? 1 : 0;
		}
		if ($post == NULL)
			return;
		if (array_key_exists('recipient_username', $post->errors()))
			return;
		if ($valid == 0)
		{
			$post->error('recipient_username', 'default');
		}
		
	}
	
	function action_detail()
	{
		$user_id = (int)$this->auth->get_user()->id;
		if ($_POST)
		{
			$post = Validation::factory($_POST)
			->rule('message', 'not_empty');


			if ($post->check()) 
			{
				$message_id = (int) Arr::get($_POST, 'id');
				$message_obj = DB::query(Database::SELECT, "SELECT subject, from_user_id, to_user_id FROM message WHERE id = :message_id AND (from_user_id = :user_id OR to_user_id = :user_id)")
				->param(':user_id', $user_id)
				->param(':message_id', $message_id)
				->execute();
				if (count($message_obj) > 0)
				{
					DB::query('NULL', 'BEGIN')->execute();
					$subject = $message_obj[0]['subject'];
					$pg_from_user_id = $message_obj[0]['from_user_id'];
					$pg_to_user_id = $message_obj[0]['to_user_id'];

					if ($pg_from_user_id == $user_id)
					{
						$from_user_id = $pg_from_user_id;
						$to_user_id = $pg_to_user_id;
					}
					else
					{
						$from_user_id = $pg_to_user_id;
						$to_user_id = $pg_from_user_id;
					}
					$message = $post['message'];
					$message_obj = DB::query(Database::INSERT, "INSERT INTO message(from_user_id, to_user_id, message, parent_message_id) VALUES(:from_user_id, :to_user_id, :message, :message_id)")
					->param(':from_user_id', $from_user_id)
					->param(':to_user_id', $to_user_id)
					->param(':message', $message)
					->param(':message_id', $message_id)
					->execute();
					if ( ! $message_obj)
					{
						DB::query('NULL', 'ROLLBACK')->execute();
						throw new Kohana_Exception('site_error ');
					}
					$user_obj = DB::query(Database::SELECT, "SELECT u.email FROM public.user u WHERE id = :recipient_id")
					->param(':recipient_id', $to_user_id)
					->execute();
					if ( ! $user_obj)
					{
						DB::query('NULL', 'ROLLBACK')->execute();
						throw new Kohana_Exception('site_error ');
					}
					else
					{
						$email = $user_obj[0]['email'];
						$my_username = $this->auth->get_user()->username;
						$url = "https://" . $this->cfg['www_domain'] . "/my/message/detail?id=$message_id";
						$email_subject = sprintf(I18n::get('email.subject.personal_message_reply_received'), $subject);
						$email_message = sprintf(I18n::get('email.message.personal_message_reply_received'), $my_username, $message, $url);
						$transport = Swift_MailTransport::newInstance();
						$mailer = Swift_Mailer::newInstance($transport);
						$message = Swift_Message::newInstance($email_subject)
						->setFrom(array($this->cfg["from_email"] => $this->cfg["site_name"] . ' Support'))
						->setTo($email)
						->setBody($email_message, 'text/plain');
						$mailer->send($message);
						
					}
					DB::query('NULL', 'COMMIT')->execute();
					Request::current()->redirect("/my/message?m=sent");
				}
			}
			else
			{
				$view->errors = $post->errors('validate');
			}
		}

		$message_id = (int) Arr::get($_GET, 'id');
		$message_obj = DB::query(Database::SELECT, "SELECT id from_user_id FROM message WHERE id = :message_id AND (from_user_id = :user_id OR to_user_id = :user_id)")
		->param(':message_id', $message_id)
		->param(':user_id', $user_id)
		->execute();
		if (count($message_obj) > 0)
		{
			$from_user_id = $message_obj[0]['from_user_id'];
			//we already know the from_user_id
			if ($from_user_id == $user_id)
			{
				$query = 'm.to_user_id = u.id';
			}
			//we want to know the from_user_id
			else
			{
				$query = 'm.from_user_id = u.id';
			}
			$message_obj = DB::query(Database::SELECT, "SELECT uf.username AS from, ut.username AS to, subject, message, date_part('epoch', posted)::int AS posted FROM message m LEFT JOIN public.user uf ON m.from_user_id = uf.id LEFT JOIN public.user ut ON m.to_user_id = ut.id WHERE m.id = :message_id OR (parent_message_id = :message_id) ORDER BY m.id")
			->param(':message_id', $message_id)
			->execute();
		
			if (count($message_obj) > 0)
			{
				$view = View::factory(TEMPLATE . '/my/message/detail');
				$view->message_obj = $message_obj;
				$view->message_id = $message_id;
				$view->cfg = $this->cfg;
				$this->template->content = $view;
			}
			else
			{
				Request::current()->redirect("/");
			}
		}
	}
	
	function action_new()
	{
		$user_id = (int)$this->auth->get_user()->id;
		$view = View::factory(TEMPLATE . '/my/message/new');
		
		if ($_POST)
		{
			$post = Validation::factory($_POST)
				->rule('recipient_username', 'not_empty')
				->rule('recipient_username', array($this, 'action_process_username'), array(':validation', ':field', 'recipient_username'))
				->rule('subject', 'not_empty')
				->rule('message', 'not_empty');
			if ($post->check()) 
			{
				DB::query('NULL', 'BEGIN')->execute();
				$username = Arr::get($_POST, 'recipient_username');
				$user_obj = DB::query(Database::SELECT, "SELECT id AS user_id FROM public.user WHERE username = :username")
				->param(':username', $username)
				->execute();
				$recipient_id = $user_obj[0]['user_id'];
				$subject = $post['subject'];
				$message = $post['message'];
				$message_obj = DB::query(Database::INSERT, "INSERT INTO message(from_user_id, to_user_id, subject, message) VALUES(:user_id, :recipient_id, :subject, :message)")
				->param(':user_id', $user_id)
				->param(':recipient_id', $recipient_id)
				->param(':subject', $subject)
				->param(':message', $message)
				->execute();
				if ( ! $message_obj)
				{
					DB::query('NULL', 'ROLLBACK')->execute();
					throw new Kohana_Exception('site_error ');
				}
				
				$message_obj = DB::query(Database::SELECT, "SELECT currval('message_id_seq') AS message_id")->execute();
				if ( ! $message_obj)
				{
					DB::query('NULL', 'ROLLBACK')->execute();
					throw new Kohana_Exception('site_error ');
				}
				$message_id = $message_obj[0]['message_id'];
				
				$user_obj = DB::query(Database::SELECT, "SELECT u.email FROM public.user u WHERE id = :recipient_id")
				->param(':recipient_id', $recipient_id)
				->execute();
				if ( ! $user_obj)
				{
					DB::query('NULL', 'ROLLBACK')->execute();
					throw new Kohana_Exception('site_error ');
				}
				else
				{
					$email = $user_obj[0]['email'];
					$my_username = $this->auth->get_user()->username;
					$url = "https://" . $this->cfg['www_domain'] . "/my/message/detail?id=$message_id";
					$email_subject = sprintf(I18n::get('email.subject.personal_message_received'), $subject);
					$email_message = sprintf(I18n::get('email.message.personal_message_received'), $my_username, $message, $url);
					$transport = Swift_MailTransport::newInstance();
					$mailer = Swift_Mailer::newInstance($transport);
					$message = Swift_Message::newInstance($email_subject)
					->setFrom(array($this->cfg["from_email"] => $this->cfg["site_name"] . ' Support'))
					->setTo($email)
					->setBody($email_message, 'text/plain');
					$mailer->send($message);
					
				}
				DB::query('NULL', 'COMMIT')->execute();
				Request::current()->redirect('/my/message?m=sent');
				
			} 
			else 
			{
				$view->errors = $post->errors('message');
			}
		}
		$view->cfg = $this->cfg;
		$this->template->content = $view;
	}
	
	function action_do_inbox()
	{
		self::action_do_archive('inbox');
	}
	
	function action_do_delete()
	{
		self::action_do_archive('delete');
	}
	
	function action_do_delete_forever()
	{
		self::action_do_archive('delete_forever');
	}
	
	function action_do_archive($action = 'archive')
	{
		$array_http = explode("/", strtok(Request::initial()->referrer(),'?'));
		//get the action name of the original controller: eg, inbox/sent
		$action_orig = $array_http[count($array_http) - 1];
		if ($action_orig == 'message')
		{
			$action_orig = 'inbox';
		}
		$user_id = (int)$this->auth->get_user()->id;
		/*
		message table:
		column [from|to]_user_message_type:
		"": normal, not affected
		0: deleted - if both from_user_message_type and to_user_message_type == 0, the record can be deleted
		1: archive
		2: trash
		3: delete forever, mark the column as 3
		*/
		if ($action == 'archive')
		{
			$message_type = 1;
		}
		else if ($action == 'delete')
		{
			$message_type = 2;
		}
		else if ($action == 'delete_forever')
		{
			$message_type = 3;
		}
		else if ($action == 'inbox')
		{
			$message_type = 'NULL';
		}
		
		$cb = Arr::get($_POST, 'cb');
		if (count($cb) > 0)
		{
			foreach ($cb as $index => $value)
			{
				$message_id = (int) $value;
				$message_obj = DB::query(Database::SELECT, "SELECT from_user_id, to_user_id FROM message m WHERE m.id = :message_id AND parent_message_id IS NULL")
				->param(':message_id', $message_id)
				->execute();
				if (count($message_obj) > 0)
				{
					DB::query(Database::UPDATE, "UPDATE message SET from_user_message_type = case when from_user_id = :user_id then :message_type else null end, to_user_message_type = case when to_user_id = :user_id then :message_type else null end WHERE parent_message_id = :message_id OR id = :message_id")
					->param(':user_id', $user_id)
					->param(':message_type', $message_type)
					->param(':message_id', $message_id)
					->execute();
				}
			}
			Request::current()->redirect("/my/message/$action_orig?m=$action");
		}
	}
	
	function action_inbox()
	{
		self::action_index(1);
	}
	
	function action_sent()
	{
		self::action_index(2);
	}
	
	function action_archive()
	{
		self::action_index(3);
	}
	
	function action_trash()
	{
		self::action_index(4);
	}
	
	function action_index($type = 1)
	{
		/*
		$type = 1: inbox
		$type = 2: sent
		$type = 3: archive
		$type = 4: trash
		*/
		$array_type['inbox'] = 1;
		$array_type['sent'] = 2;
		$array_type['archive'] = 3;
		$array_type['trash'] = 4;
		
		$action_orig = Arr::get($_GET, 'o');
		if ($action_orig)
		{
			$type = $array_type[$action_orig];
		}
		
		$array_uri = explode('/', Request::current()->uri());
		//my/message
		if (count($array_uri) == 2)
		{
			$action = 'inbox';
		}
		else
		{
			$action = $array_uri[count($array_uri) - 1];
		}
		
		
		$user_id = (int)$this->auth->get_user()->id;
		$view = View::factory(TEMPLATE . '/my/message/index');
		$limit = $this->cfg['item_per_page'];
		$offset = ((int)Arr::get($_GET, 'page', 1) - 1) * $limit;
		
		$m = Arr::get($_GET, 'm');
		if ($m == 'sent')
		{
			$view->msg = I18n::get('message_sent');
		}
		else if ($m == 'archive')
		{
			$view->msg = I18n::get('message_archive');
		}
		else if ($m == 'delete')
		{
			$view->msg = I18n::get('message_delete');
		}
		else if ($m == 'inbox')
		{
			$view->msg = I18n::get('message_inbox');
		}
		else if ($m == 'delete_forever')
		{
			$view->msg = I18n::get('message_deleted_permanently');
		}
		
		
		
		//echo Request::current()->uri();

		if ($type == 1)
		{
			$view->message_obj = $order_obj = DB::query(Database::SELECT, "WITH message_sent AS (SELECT id AS parent_message_id FROM message m WHERE m.to_user_id = :user_id AND parent_message_id IS NULL AND m.to_user_message_type IS NULL UNION DISTINCT SELECT parent_message_id FROM message m WHERE m.to_user_id = :user_id AND parent_message_id IS NOT NULL AND m.to_user_message_type IS NULL) SELECT m.id, m.subject, uf.username AS from_user, ut.username AS to_user, date_part('epoch', posted)::int AS posted FROM message_sent ms LEFT JOIN message m ON ms.parent_message_id = m.id LEFT JOIN public.user uf ON m.from_user_id = uf.id LEFT JOIN public.user ut ON m.to_user_id = ut.id ORDER BY m.id DESC LIMIT :limit OFFSET :offset")
			->param(':user_id', $user_id)
			->param(':limit', $limit)
			->param(':offset', $offset)
			->execute();
			
			$message_count_obj = DB::query(Database::SELECT, "WITH message_sent AS (SELECT id AS parent_message_id FROM message m WHERE m.to_user_id = :user_id AND parent_message_id IS NULL UNION DISTINCT SELECT DISTINCT parent_message_id FROM message m WHERE m.to_user_id = :user_id AND parent_message_id IS NOT NULL) SELECT COUNT(ms.parent_message_id) AS total FROM message_sent ms")
			->param(':user_id', $user_id)
			->execute();
		}
		else if ($type == 2)
		{
			$view->message_obj = DB::query(Database::SELECT, "WITH message_sent AS (SELECT id AS parent_message_id FROM message m WHERE m.from_user_id = :user_id AND parent_message_id IS NULL AND from_user_message_type IS NULL UNION DISTINCT SELECT parent_message_id FROM message m WHERE m.from_user_id = :user_id AND parent_message_id IS NOT NULL AND from_user_message_type IS NULL) SELECT m.id, m.subject, uf.username AS from_user, ut.username AS to_user, date_part('epoch', posted)::int AS posted FROM message_sent ms LEFT JOIN message m ON ms.parent_message_id = m.id LEFT JOIN public.user uf ON m.from_user_id = uf.id LEFT JOIN public.user ut ON m.to_user_id = ut.id ORDER BY m.id DESC LIMIT :limit OFFSET :offset")
			->param(':user_id', $user_id)
			->param(':limit', $limit)
			->param(':offset', $offset)
			->execute();
	
			$message_count_obj = DB::query(Database::SELECT, "WITH message_sent AS (SELECT id AS parent_message_id FROM message m WHERE m.from_user_id = :user_id AND parent_message_id IS NULL UNION DISTINCT SELECT parent_message_id FROM message m WHERE m.from_user_id = :user_id AND parent_message_id IS NOT NULL) SELECT COUNT(ms.parent_message_id) AS total FROM message_sent ms")
			->param(':user_id', $user_id)
			->execute();
		}
		else if ($type == 3)
		{
			$view->message_obj = DB::query(Database::SELECT, "SELECT m.id, m.subject, uf.username AS from_user, ut.username AS to_user, date_part('epoch', posted)::int AS posted FROM message m LEFT JOIN public.user uf ON m.from_user_id = uf.id LEFT JOIN public.user ut ON m.to_user_id = ut.id WHERE m.parent_message_id IS NULL AND ((from_user_id = :user_id AND m.from_user_message_type = '1') OR (to_user_id = :user_id AND m.to_user_message_type = '1')) ORDER BY m.id DESC LIMIT :limit OFFSET :offset")
			->param(':user_id', $user_id)
			->param(':limit', $limit)
			->param(':offset', $offset)
			->execute();
			$message_count_obj = DB::query(Database::SELECT, "SELECT count(m.id) AS total FROM message m WHERE m.parent_message_id IS NULL AND ((from_user_id = :user_id AND m.from_user_message_type = '1') OR (to_user_id = :user_id AND m.to_user_message_type = '1'))")
			->param(':user_id', $user_id)
			->execute();
		}
		else if ($type == 4)
		{
			$view->message_obj = DB::query(Database::SELECT, "SELECT m.id, m.subject, uf.username AS from_user, ut.username AS to_user, date_part('epoch', posted)::int AS posted FROM message m LEFT JOIN public.user uf ON m.from_user_id = uf.id LEFT JOIN public.user ut ON m.to_user_id = ut.id WHERE m.parent_message_id IS NULL AND ((from_user_id = :user_id AND m.from_user_message_type = '2') OR (to_user_id = :user_id AND m.to_user_message_type = '2')) ORDER BY m.id DESC LIMIT :limit OFFSET :offset")
			->param(':user_id', $user_id)
			->param(':limit', $limit)
			->param(':offset', $offset)
			->execute();
			$message_count_obj = DB::query(Database::SELECT, "SELECT count(m.id) AS total FROM message m WHERE m.parent_message_id IS NULL AND ((from_user_id = :user_id AND m.from_user_message_type = '2') OR (to_user_id = :user_id AND m.to_user_message_type = '2'))")
			->param(':user_id', $user_id)
			->execute();
		}

		$pagination = Pagination::factory(array(
			'query_string'   => 'page',
			'total_items'    => $message_count_obj[0]['total'],
			'items_per_page' => $limit,
			'style'          => 'classic',
			'auto_hide'      => TRUE
		));
		$view->type = $type;
		$view->action = $action;
		$view->pagination = $pagination->render();
		$view->cfg = $this->cfg;
		$this->template->content = $view;
	}
}
