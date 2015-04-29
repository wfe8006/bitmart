<?php
class Controller_Account_Profile extends Controller_System
{
	public function before()
	{
		parent::before();
		$this->cfg = Kohana::$config->load('general.default');
		$this->session = Session::instance();
		$this->auth = Auth::instance();

		/*
		include Kohana::find_file('libraries','HybridAuthLib');
		$this->hybridauthlib = new HybridAuthLib(Kohana::$config->load('hybridauth.default'));
		$provider = Session::instance()->get('provider');
		$service = $this->hybridauthlib->authenticate($provider);
		if ($service->isUserConnected())
		{
			$user_profile = $service->getUserProfile();
			print"<pre>";
			print_r($user_profile);
			print"</pre>";
		}
		*/

		if ($this->auth->logged_in())
		{
			$this->user = $this->auth->get_user();
		}
		else
		{
			Request::current()->redirect('https://' . $this->cfg['www_domain'] . '/account/auth');
		}
		
	}
	
	function process_username(Validation $post = NULL)
	{
		if ($this->request->is_ajax())
		{
			$username = Arr::get($_GET, 'username');
			$my_username = $this->auth->get_user()->username;
			$result = DB::query(Database::SELECT, "SELECT COUNT(*) AS count FROM public.user WHERE username = :username AND username != :my_username")
			->param(':username', $username)
			->param(':my_username', $my_username)
			->execute();
			if (TEMPLATE == "fullsite")
			{
				echo $result[0]['count'] == 0 ? 1 : 0;
			}
			else
			{
				echo $result[0]['count'] == 0 ? "true" : "false";
			}
		}
		else
		{
		
			if (array_key_exists('username', $post->errors()))
				return;
			if ($_POST['username'] !=  $this->auth->get_user()->username)
			{
				$username = $_POST['username'];
				$result = DB::query(Database::SELECT, "SELECT COUNT(*) AS taken FROM public.user WHERE username = :username")
				->param(':username', $username)
				->execute();
				if ($result[0]['taken'] > 0)
				{
					$post->error('username', 'username_not_available'); 
				}
			}
			
			//check if username in db is valid
			$hybridauth_username = "user" . $this->auth->get_user()->id;
			if ($this->auth->get_user()->username == $hybridauth_username)
			{
				$post->error('username', 'username_cannot_be_used'); 
			}
			
			//check if posted username is valid
			if ($_POST['username'] == $hybridauth_username)
			{
				$post->error('username', 'username_cannot_be_used');
			}
		}
	}
	
	function process_email(Validation $post = NULL)
	{
		if ($this->request->is_ajax())
		{
			$email = Arr::get($_GET, 'email');
			$my_email = $this->auth->get_user()->email;
			$result = DB::query(Database::SELECT, "SELECT COUNT(*) AS count FROM public.user WHERE email = :email AND email != :my_email")
			->param(':email', $email)
			->param(':my_email', $my_email)
			->execute();
			if (TEMPLATE == "fullsite")
			{
				//echo $result[0]['count'] == 0 ? 1 : 0;
				echo $result[0]['count'] == 0 ? "true" : "false";
			}
			else
			{
				echo $result[0]['count'] == 0 ? "true" : "false";
			}
		}
		else
		{
			if (array_key_exists('email', $post->errors()))
				return;
			if ($_POST['email'] !=  $this->auth->get_user()->email)
			{
				$email = $_POST['email'];
				$result = DB::query(Database::SELECT, "SELECT COUNT(*) AS taken FROM public.user WHERE email = :email")
				->param(':email', $email)
				->execute();
				if ($result[0]['taken'] > 0)
				{
					$post->error('email', sprintf('email_not_available', array($email))); 
				}
			}
		}
	}
	
	function process_dob(Validation $post = NULL)
	{
		if ($_POST['month'] != "-" AND $_POST['day'] != "-" AND $_POST['year'] != "-")
		{
			$valid = checkdate($_POST['month'], $_POST['day'], $_POST['year']);
			if ($valid != 1)
			{
				$post->error('dob', 'incorrect_dob');
			}
		}
		else
		{
			$post->error('dob', 'incorrect_dob');
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

	function action_index()
	{
		$view = View::factory(TEMPLATE . '/account/profile', array('header' => I18n::get('header_profile')));
		

		
		if ($_POST)
		{
			//max_length check not needed here, done in html with maxlength=""
			$post = Validation::factory($_POST)
				->rule('email', 'not_empty')
				->rule('email', 'email')
				->rule('email', array($this, 'process_email'), array(':validation', ':field', 'email'))
				->rule('firstname', 'alpha')
				->rule('lastname', 'alpha')
				->rule('phone', 'phone', array(':value', array(6,7,8,9,10,11)));
			
			/*
			$post->rule('username', 'not_empty')
				->rule('username', 'min_length', array(':value', 3))
				->rule('username', 'alpha_dash')
				->rule('username', array($this, 'process_username'), array(':validation', ':field', 'username'))
			*/
				
			if ($post['month'] == '-' AND $post['day'] == '-' AND $post['year'] == '-')
			{
				$dob = null;
			}
			else
			{
				$post->rule('dob', array($this, 'process_dob'), array(':validation', ':field', 'dob'));
				$dob = (int)$post['year'] . '-' . (int)$post['month'] . '-' . (int)$post['day'];
			}
			if ($post->check()) 
			{
				$username = $post['username'];
				$firstname = $post['firstname'];
				$lastname = $post['lastname'];
				$gender = (int)Arr::get($_POST, 'gender');
				$country = (int)Arr::get($_POST, 'country');
				if ($country == 0)
				{
					$country = null;
				}
				$id = (int)$this->auth->get_user()->id;
				
				
				//DB::query(Database::UPDATE, "UPDATE public.user SET username = $username, firstname = $firstname, lastname = $lastname, gender = '$gender', dob = $dob, country_id = $country, phone = '{$post['phone']}' WHERE id = '$id'")->execute();
				DB::query(Database::UPDATE, "UPDATE public.user SET firstname = :firstname, lastname = :lastname, gender = ':gender', dob = :dob, country_id = :country, phone = :phone WHERE id = :id")
				->param(':firstname', $firstname)
				->param(':lastname', $lastname)
				->param(':gender', $gender)
				->param(':dob', $dob)
				->param(':country', $country)
				->param(':phone', $post['phone'])
				->param(':id', $id)
				->execute();
				
		
				
				
				if ($post['email'] != $this->auth->get_user()->email)
				{
					$code = text::random($type = 'alnum', $length = 10);
					$email_message = sprintf(I18n::get('profile.email_message'), $post['email'], $post['email'], $code);
					$transport = Swift_MailTransport::newInstance();
					$mailer = Swift_Mailer::newInstance($transport);
					$message = Swift_Message::newInstance(I18n::get('profile.email_subject'))
					->setFrom(array($this->cfg["from_email"] => $this->cfg["site_name"] . ' Support'))
					->setTo($post['email'])
					->setBody($email_message, 'text/plain');
					$mailer->send($message);
					DB::query(Database::INSERT, "INSERT INTO new_email(user_id, email, code) VALUES(:id, :email, :code)")
					->param(':id', $id)
					->param(':email', $post['email'])
					->param(':code', $code)
					->execute();
					$view->msg = I18n::get('profile.msg_profile_saved')."<br>".sprintf(I18n::get('profile.msg_email_sent'), $post['email']);
				}
				else
				{
					$view->msg = I18n::get('profile.msg_profile_saved');
				}
			}
			else 
			{
				$view->errors = $post->errors('profile');
				if (array_key_exists('email', $view->errors))
				{
					$view->errors['email'] = sprintf($view->errors['email'], $post['email']);
				}
			}
		}
		$user_id = $this->auth->get_user()->id;
		$view->cfg = $this->cfg;
		$view->country_result = DB::query(Database::SELECT, "SELECT id, name FROM country ORDER BY name")->execute();
		
		$this->template->title = I18n::get('header_profile');
		$this->template->content = $view;
	}
}
