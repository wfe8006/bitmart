<?php
class Controller_Account_Signup extends Controller_System
{
	public function before()
	{
		parent::before();
		$this->cfg = Kohana::$config->load('general.default');	
		include Kohana::find_file('libraries', 'Recaptcha');
		$this->recaptcha = new Recaptcha;
		$this->auth = Auth::instance();
		if ($this->auth->logged_in())
		{
			Request::current()->redirect('http://' . $this->cfg['www_domain']);
		}
	}

	function process_email(Validation $post = NULL)
	{
		$email = Arr::get($_GET, 'email', Arr::get($_POST, 'email'));
		$my_email = $this->auth->get_user()->email;
		$result = DB::query(Database::SELECT, "SELECT COUNT(*) AS count FROM public.user WHERE email = :email AND email != :my_email")
		->param(':email', $email)
		->param(':my_email', $my_email)
		->execute();
		//$result = DB::query(Database::SELECT, "SELECT COUNT(*) AS count FROM public.user WHERE email = $email")->execute();
		if ($this->request->is_ajax())
		{
			$this->template->title = '';
			//jquery mobile requires json output: true = record does not exist, false = record exists
			echo $result[0]['count'] == 0 ? "true" : "false";
		}
		else
		{
			if ($post == NULL)
				return;
			if (array_key_exists('email', $post->errors()))
				return;
			if ($result[0]['count'] > 0)
			{
				$post->error('email', 'email_not_available');
			}
		}
	}
	
	function process_username(Validation $post = NULL)
	{
		$username = Arr::get($_GET, 'username', Arr::get($_POST, 'username'));
		
		//only logged in user has username??
		//$my_username = $this->auth->get_user()->username;
		//$result = DB::query(Database::SELECT, "SELECT COUNT(*) AS count FROM public.user WHERE username = :username AND username != :my_username")
		$result = DB::query(Database::SELECT, "SELECT COUNT(*) AS count FROM public.user WHERE username = :username")
		->param(':username', $username)
		->execute();

		if ($this->request->is_ajax())
		{
			$this->template->title = '';
			echo $result[0]['count'] == 0 ? "true" : "false";
			
		}
		else
		{
			if ($post == NULL)
				return;
			if (array_key_exists('username', $post->errors()))
				return;
			if ($result[0]['count'] > 0)
			{
				$post->error('username', 'username_not_available');
			}
		}
	}
	
	function process_recaptcha(Validation $post = NULL)
	{
		if ($this->request->is_ajax())
		{
			$this->recaptcha->recaptcha_check_answer($_SERVER['REMOTE_ADDR'], $_GET['challenge'], $_GET['response']);
			$this->template->title = '';
			$this->template->content = $this->recaptcha->is_valid == 1 ? 1 : 0;
		}
		else
		{
			if ($post == NULL)
				return;
			if (array_key_exists('recaptcha_response_field', $post->errors()))
				return;
			$this->recaptcha->recaptcha_check_answer($_SERVER['REMOTE_ADDR'], $_POST['recaptcha_challenge_field'], $_POST['recaptcha_response_field']);
			if ($this->recaptcha->is_valid != 1)
			{
				$post->error('recaptcha_response_field', 'check_recaptcha_error');
			}
		}
	}
	
	function action_process_username()
	{
		self::process_username();
	}
	
	function action_process_email()
	{
		self::process_email();
	}
	
	function action_process_recaptcha()
	{
		self::process_recaptcha();
	}


	function action_index()
	{
		$arr['header'] = I18n::get('header_signup');
		$view = View::factory(TEMPLATE . '/account/signup', $arr);
		$view->cfg = $this->cfg;
		if ($_POST)
		{
			
			//$result = DB::query(Database::SELECT, "SELECT email, country_id from beta where beta_key = $i AND used = '0'")->execute();
			//$email = $result[0]["email"];
			$post = Validation::factory($_POST)
				->rule('username', 'not_empty')
				->rule('username', 'min_length', array(':value', 3))
				->rule('username', 'max_length', array(':value', 40))
				->rule('username', 'alpha_dash')
				->rule('username', array($this, 'process_username'), array(':validation', ':field', 'username'))
				->rule('password', 'not_empty')
				->rule('password', 'min_length', array(':value', 6))
				->rule('password', 'max_length', array(':value', 40))
				->rule('cpassword', 'not_empty')
				->rule('cpassword', 'matches', array(':validation', 'password', 'cpassword'))
				->rule('email', 'not_empty')
				->rule('email', 'email')
				->rule('email', array($this, 'process_email'), array(':validation', ':field', 'email'));
				
			if ($post->check()) 
			{
				$email = $post['email'];
				$result2 = DB::query(Database::SELECT, "SELECT COUNT(*) AS user_count FROM public.user WHERE email = :email AND username = :username")
				->param(':email', $email)
				->param(':username', $post['username'])
				->execute();
				if ($result2[0]['user_count'] < 1)
				{
					$hashed_password = $this->auth->hash_password($post['password']);
					$code = text::random('alnum', 10);
					
					DB::query('NULL', 'BEGIN')->execute();
					$result = DB::query(Database::INSERT, "INSERT INTO public.user(email, username, password, activation_id, shipping_address, info) VALUES(:email, :username, :hashed_password, :code, '{}', '{}')")
					->param(':email', $email)
					->param(':username', $post['username'])
					->param(':hashed_password', $hashed_password)
					->param(':code', $code)
					->execute();
					if ( ! $result)
					{
						DB::query('NULL', 'ROLLBACK')->execute();
						throw new Kohana_Exception('site_error ');
					}
					
					
					$result = DB::query(Database::SELECT, "SELECT currval('user_id_seq') AS user_id")->execute();
					if ( ! $result)
					{
						DB::query('NULL', 'ROLLBACK')->execute();
						throw new Kohana_Exception('site_error ');
					}
					$user_id = $result[0]['user_id'];
					$result = DB::query(Database::INSERT, "INSERT INTO role_user(user_id, role_id) VALUES(:user_id, 1)")
					->param(':user_id', $user_id)
					->execute();
					if ( ! $result)
					{
						DB::query('NULL', 'ROLLBACK')->execute();
						throw new Kohana_Exception('site_error ');
					}
					
					
					$array_preference['currency_code'] = 'usd';
					$array_preference['cryptocurrency'] = 0;
					$array_preference['weight_unit'] = 1;
					$array_preference['selling_option'] = 1;
					$array_old = array(':', '"null"');
					$array_new = array('=>', 'null');
					$json_preference = json_encode($array_preference);
					$hstore_preference = substr(str_replace($array_old, $array_new, $json_preference), 1, -1);
					$result = DB::query(Database::INSERT, "INSERT INTO user_preference(user_id, preference) VALUES(:user_id, :hstore_preference)")
					->param(':user_id', $user_id)
					->param(':hstore_preference', $hstore_preference)
					->execute();
					if ( ! $result)
					{
						DB::query('NULL', 'ROLLBACK')->execute();
						throw new Kohana_Exception('site_error ');
					}
					
					$result = DB::query(Database::INSERT, "INSERT INTO user_store(user_id) VALUES(:user_id)")
					->param(':user_id', $user_id)
					->execute();
					if ( ! $result)
					{
						DB::query('NULL', 'ROLLBACK')->execute();
						throw new Kohana_Exception('site_error ');
					}
					
					$result = DB::query(Database::INSERT, "INSERT INTO user_wallet(user_id, balance) VALUES(:user_id, '{}')")
					->param(':user_id', $user_id)
					->execute();
					if ( ! $result)
					{
						DB::query('NULL', 'ROLLBACK')->execute();
						throw new Kohana_Exception('site_error ');
					}
					
					$result = DB::query(Database::INSERT, "INSERT INTO user_payment_option(user_id, option) VALUES(:user_id, '{}')")
					->param(':user_id', $user_id)
					->execute();
					if ( ! $result)
					{
						DB::query('NULL', 'ROLLBACK')->execute();
						throw new Kohana_Exception('site_error ');
					}
					
					$result = DB::query(Database::INSERT, "INSERT INTO tax(user_id, data) VALUES(:user_id, '{}')")
					->param(':user_id', $user_id)
					->execute();
					if ( ! $result)
					{
						DB::query('NULL', 'ROLLBACK')->execute();
						throw new Kohana_Exception('site_error ');
					}
					
					$result = DB::query(Database::INSERT, "INSERT INTO cart(user_id, data) VALUES(:user_id, '{}')")
					->param(':user_id', $user_id)
					->execute();
					if ( ! $result)
					{
						DB::query('NULL', 'ROLLBACK')->execute();
						throw new Kohana_Exception('site_error ');
					}
					
					/*
					DB::query(Database::INSERT, "INSERT INTO activation(uid, key) VALUES($user_id, '$code')")->execute();
					*/
					
					DB::query('NULL', 'COMMIT')->execute();
					$email_message = sprintf(I18n::get('signup.email_message'), $code);
					$transport = Swift_MailTransport::newInstance();
					$mailer = Swift_Mailer::newInstance($transport);
					$message = Swift_Message::newInstance(I18n::get('signup.email_subject'))
					->setFrom(array($this->cfg["from_email"] => $this->cfg["site_name"] . ' Support'))
					->setTo($post['email'])
					->setBody($email_message, 'text/plain');
					$mailer->send($message);
					
					
					//notify admin
					$transport = Swift_MailTransport::newInstance();
					$mailer = Swift_Mailer::newInstance($transport);
					$message = Swift_Message::newInstance("new member signup")
					->setFrom(array($this->cfg["from_email"] => $this->cfg["site_name"] . ' Support'))
					->setTo($this->cfg["from_email"])
					->setBody("new member signup: {$post['email']}", 'text/plain');
					$mailer->send($message);
					
					$signup_message = sprintf(I18n::get('signup.msg_thank_you'), $email);
					$view = View::factory(TEMPLATE . '/special_info', $arr);
					$view->msg = $signup_message;
					//if ($user_result->count() > 0) url::redirect('/account/signup/thankyou');
				}
			} 
			else 
			{
				$view->errors = $post->errors('signup');
			}
		}
		$this->template->title = I18n::get('title_signup');
		$this->template->content = $view;
	}
}
