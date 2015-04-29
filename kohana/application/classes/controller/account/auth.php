<?php
class Controller_Account_Auth extends Controller_System
{
	public function before()
	{
		parent::before();
		$this->cfg = Kohana::$config->load('general.default');
		$this->db_cfg = Kohana::$config->load('database.default');
		include Kohana::find_file('libraries','HybridAuthLib');
		$this->hybridauthlib = new HybridAuthLib(Kohana::$config->load('hybridauth.default'));
		$this->service = "";
		$this->auth = Auth::instance();
		
		if ($this->request->action() != 'logout')
		{
			if ($this->auth->logged_in())
			{
				Request::current()->redirect('http://' . $this->cfg['www_domain']);
			}
		}
	}
	
	function process_username(Validation $post = NULL)
	{
		$username = Arr::get($_GET, 'username', Arr::get($_POST, 'username'));
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

	function action_endpoint()
	{
		//log_message('debug', 'controllers.HAuth.endpoint called.');
		//log_message('info', 'controllers.HAuth.endpoint: $_REQUEST: '.print_r($_REQUEST, TRUE));
		
		//cannot use if (isset($_REQUEST['error'])) here, will throw exception..
		//if getting error, close the parent window.
		$protocol = "http";
		if ($_REQUEST['error'])
		{
		?>
			<script language="javascript">
			if (window.opener)
			{
				
			}
			window.self.close();
			</script>
			<?php
			exit;
		}
		
		
		if ($_SERVER['REQUEST_METHOD'] === 'GET')
		{
			//log_message('debug', 'controllers.HAuth.endpoint: the request method is GET, copying REQUEST array into GET array.');
			$_GET = $_REQUEST;
		}
		//log_message('debug', 'controllers.HAuth.endpoint: loading the original HybridAuth endpoint script.');
		//require_once '/sites/www.tagaprice.tk/vendor/hybridauth/index.php';

		include(__DIR__.'/../../../vendor/hybridauth/index.php');
	}
	
	function action_social_login()
	{
		
		
		/*
		if ($this->auth->logged_in())
		{
			$this->request->redirect('http://' . $this->cfg['www_domain'] . Session::instance()->get('requested_url', '/'));
		}
		*/
		//self::check_logged_in('/account/auth/social_login');
		
		$provider = $this->request->param('provider');
		if( ! empty($provider))
		{
			try
			{
				if($this->hybridauthlib->isConnectedWith($provider))
				{
					$service = $this->hybridauthlib->authenticate($provider);
					Session::instance()->set('provider', $provider);
					if ($service->isUserConnected())
					{
						$user_profile = $service->getUserProfile();
						$identifier = $user_profile->identifier;
						$photo_url = $user_profile->photoURL;
						$firstname = $user_profile->firstName;
						$lastname = $user_profile->lastName;
						$email = $user_profile->emailVerified;
								
						$username = "$provider$identifier";
						
						//check if the social account already exists in users_social table, the identifier and provider_id will always be the same regardless of the email address change
						$user_social_obj = DB::query(Database::SELECT, "SELECT us.user_id FROM user_social us LEFT JOIN provider p ON us.provider_id = p.id WHERE us.identifier = '$identifier' AND p.name = :provider")
						->param(':provider', $provider)
						->execute();
						
						//record not found in database
						if (count($user_social_obj) == 0)
						{
							$url = "social_signup";
						
							$secret = $this->cfg['key'];
							$hashed_password = $this->auth->hash_password($username . $secret);
												
							$provider_obj = DB::query(Database::SELECT, "SELECT id FROM provider WHERE name = :provider")
							->param(':provider', $provider)
							->execute();
							$provider_id = $provider_obj[0]["id"];
							
							switch ($provider)
							{
								case "Facebook":
								case "Yahoo":
								case "Google":
								case "Twitter":
								case "Linkedin":
									break;
							}
							if ($email == "")
							{
								$email = "$provider$identifier@" . $this->cfg['domain'];
							}

							//User has to use same email account for multiple social accounts (excluding Twitter and Linkedin that don't provide email address) so that they can be linked together
							$user_obj = DB::query(Database::SELECT, "SELECT u.id AS user_id FROM public.user u WHERE email = :email")
							->param(':email', $email)
							->execute();
							
							if (count($user_obj) > 0)
							{
								$user_id = $user_obj[0]["user_id"];
							}
							else
							{
								
								DB::query('NULL', 'BEGIN')->execute();
								
								//randomly generated unique id, will be used to match the real user who changed his/her username
								$code = text::random('alnum', 10);
								
								$result = DB::query(Database::INSERT, "INSERT INTO public.user(email, username, password, activation_id, firstname, lastname, active, shipping_address, info) VALUES(:email, :username, :hashed_password, :code, :firstname, :lastname, '0', '{}', '{}')")
								->param(':email', $email)
								->param(':username', $username)
								->param(':hashed_password', $hashed_password)
								->param(':code', $code)
								->param(':firstname', $firstname)
								->param(':lastname', $lastname)
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
								$user_id = $result[0]["user_id"];
								
								$result = DB::query(Database::INSERT, "INSERT INTO role_user(user_id, role_id) VALUES(:user_id, '1')")
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
									throw new Kohana_Exception('site_error');
								}
								
								
								$result = DB::query(Database::INSERT, "INSERT INTO cart(user_id, data) VALUES(:user_id, '{}')")
								->param(':user_id', $user_id)
								->execute();
								if ( ! $result)
								{
									DB::query('NULL', 'ROLLBACK')->execute();
									throw new Kohana_Exception('site_error');
								}
								

								$result = DB::query(Database::UPDATE, "UPDATE public.user SET username = :username WHERE id = :user_id")
								->param(':username', "user$user_id")
								->param(':user_id', $user_id)
								->execute();
								if ( ! $result)
								{
									DB::query('NULL', 'ROLLBACK')->execute();
									throw new Kohana_Exception('site_error');
								}
							
								DB::query('NULL', 'COMMIT')->execute();
								// temporary solution to track new members
								$transport = Swift_MailTransport::newInstance();
								$mailer = Swift_Mailer::newInstance($transport);
								$message = Swift_Message::newInstance('New Member Signup')
								->setFrom(array($this->cfg['noreply_email'] => 'Mailer'))
								->setTo($this->cfg['support_email'])
								->setBody($email, 'text/plain');
								$mailer->send($message);
							}
							DB::query(Database::INSERT, "INSERT INTO user_social(provider_id, identifier, email, firstname, lastname, user_id) VALUES(:provider_id, :identifier, :email, :firstname, :lastname, :user_id)")
							->param(':provider_id', $provider_id)
							->param(':identifier', $identifier)
							->param(':email', $email)
							->param(':firstname', $firstname)
							->param(':lastname', $lastname)
							->param(':user_id', $user_id)
							->execute();
							
							//Request::current()->redirect('https://' . $this->cfg['www_domain'] . '/account/auth/set_username');
							
							//$this->auth->force_login("user$user_id");
							//c = force change username
							//Session::instance()->set('requested_url', 'account/profile?c=1');
							Session::instance()->set("code", $code);
							?>
							<script language="javascript">
							
							var field = 'fb';
							var url = window.location.href;
							if(url.indexOf('?' + field + '=') != -1)
								via_fb = 1;
							else if(url.indexOf('&' + field + '=') != -1)
								via_fb = 1;
							else
								via_fb = 0;

							
							if (window.opener)
								{
									try
									{
										window.self.close();
										window.opener.parent.$.hybridauth_social_sing_on.close();
									} catch(err)
									{
									}
									window.opener.parent.location.href = "https://<?php echo $this->cfg['www_domain'] . '/account/auth/set_username' ?>";
								}
							//
							//if (via_fb == 0)
							//	window.self.close();
							//
							</script>
							<?php
							die();
							
							
							
							
						}
						else
						{
							$user_id = $user_social_obj[0]["user_id"];
							$user_obj = DB::query(Database::SELECT, "SELECT u.username, u.active, u.activation_id FROM public.user u LEFT JOIN user_social us ON u.id = us.user_id WHERE u.id = :user_id")
							->param(':user_id', $user_id)
							->execute();
							$active = $user_obj[0]['active'];
							$activation_id = $user_obj[0]['activation_id'];
							if ($active == 0)
							{
								Session::instance()->set("code", $activation_id);
								?>
								<script language="javascript">
								
								var field = 'fb';
								var url = window.location.href;
								if(url.indexOf('?' + field + '=') != -1)
									via_fb = 1;
								else if(url.indexOf('&' + field + '=') != -1)
									via_fb = 1;
								else
									via_fb = 0;
  
								
								if (window.opener)
								{
									try
									{
										window.self.close();
										window.opener.parent.$.hybridauth_social_sing_on.close();
									} catch(err)
									{
									}
									window.opener.parent.location.href = "https://<?php echo $this->cfg['www_domain'] . '/account/auth/set_username' ?>";
								}
								//
								//if (via_fb == 0)
								//	window.self.close();
								//
								</script>
								<?php
								die();
							}
							else
							{
								$username = $user_obj[0]["username"];
								$this->auth->force_login($username);
							}
						}
						
					}

					?>
					
				<script language="javascript">
					if (window.opener)
					{
						try
						{
							window.opener.parent.$.hybridauth_social_sing_on.close();
						} catch(err)
						{
						}
						window.opener.parent.location.href = "<?php echo "http://" . $this->cfg['www_domain'] ?>/<?php echo Session::instance()->get('requested_url', ''); ?>";
					}
					window.self.close();
				</script>
				<?php
					die();
					
				}
				
				if ($this->hybridauthlib->serviceEnabled($provider))
				{
					//print('debug', "controllers.HAuth.login: service $provider enabled, trying to authenticate.");
					
					//here the redirect will occur
					$service = $this->hybridauthlib->authenticate($provider);
					// Cannot authenticate user
					if (! $service->isUserConnected())
					{
						//show_error('Cannot authenticate user');
					
					}
				}
				else // This service is not enabled.
				{
					//show_404($_SERVER['REQUEST_URI']);
				}
			}
			catch(Exception $e)
			{
				$error = 'Unexpected error';
				switch($e->getCode())
				{
					case 0 : $error = 'Unspecified error.'; break;
					case 1 : $error = 'Hybriauth configuration error.'; break;
					case 2 : $error = 'Provider not properly configured.'; break;
					case 3 : $error = 'Unknown or disabled provider.'; break;
					case 4 : $error = 'Missing provider application credentials.'; break;
					case 5 :// print('debug', 'controllers.HAuth.login: Authentification failed. The user has canceled the authentication or the provider refused the connection.');
							 //redirect();
							 if (isset($service))
							 {
								//print('debug', 'controllers.HAuth.login: logging out from service.');
								$service->logout();
							 }
							 //show_error('User has cancelled the authentication or the provider refused the connection.');
							 break;
					case 6 : $error = 'User profile request failed. Most likely the user is not connected to the provider and he should to authenticate again.';
							 break;
					case 7 : $error = 'User not connected to the provider.';
							 break;
				}

				if (isset($service))
				{
					$service->logout();
				}
				throw new Kohana_Exception('site_error');
				//print "<br>error: $error";
				//show_error('Error authenticating user.');
			}
		}
		else
		{
			$view = View::factory(TEMPLATE . '/account/social_login');
			$this->template = $view;
		}
	}
	
	function action_login()
	{


		$view = View::factory(TEMPLATE . '/account/auth');
		$view->cfg = $this->cfg;
		$this->template->title = i18n::get('login');
		if ($this->auth->get_user() AND $this->auth->get_user()->active == 0)
		{
			//Auth::instance()->logout will destroy $_POST also, causing username + password to vanish
			$username = $_POST['username'];
			$password = $_POST['password'];
			$_SESSION = array();
			session_destroy();
			Auth::instance()->logout();
			$_POST['username'] = $username;
			$_POST['password'] = $password;
			$view->errors = Array('account_not_activiated' => 'Please confirm your email address.'); 
		}

		/*
		if ($this->auth->logged_in())
		{
			$this->request->redirect('http://' . $this->cfg['www_domain'] . Session::instance()->get('requested_url', '/'));
		}
		*/
		if (isset($_POST))
		{
			$post = Validation::factory($_POST)
				->rule('username','not_empty')
				->rule('password','not_empty');
					

			if ($post->check())
			{
				$remember = (int)$post['remember'] == 1 ? TRUE : FALSE;
				
				
				if ($this->auth->login($post['username'], $post['password'], $remember))
				
				{
					
					
					if ($this->auth->get_user() AND $this->auth->get_user()->active == 0)
					{
						
						$username = $_POST['username'];
						$password = $_POST['password'];
						$_SESSION = array();
						session_destroy();
						Auth::instance()->logout();
						$_POST['username'] = $username;
						$_POST['password'] = $password;
						$view->errors = Array('invalid_login' => 'Account not activated. Please confirm your email address.'); 
					}
					else
					{
						$requested_url = Session::instance()->get('requested_url', '');
						if (strstr($requested_url, 'account'))
						{
							$protocol = 'https';
						}
						else
						{
							$protocol = 'http';
						}
						$this->request->redirect("$protocol://" . $this->cfg['www_domain'] . $requested_url);
					}
					
				}
				else
				{
					$view->errors = Array('invalid_login' => 'Invalid username or password');
				}

				
			}
			else
			{
				$view->errors = $post->errors('login');
			}
		}
		$this->template->content = $view;
	}
	
	function action_logout()
	{
		$_SESSION = array();
		session_destroy();
		Auth::instance()->logout();
		//temporarily redirect the page to main page
		Request::current()->redirect('http://' . $this->cfg['www_domain']);
	}
	
	function action_set_username()
	{
		$activation_id = Session::instance()->get("code");
		$view = View::factory(TEMPLATE . '/account/set_username');
		
		if ( ! isset($activation_id))
		{
			Request::current()->redirect('http://' . $this->cfg['www_domain']);
		}
		if ($_POST)
		{
			$username = Arr::get($_POST, 'username');
			$post = Validation::factory($_POST)
				->rule('username', 'not_empty')
				->rule('username', 'min_length', array(':value', 3))
				->rule('username', 'max_length', array(':value', 40))
				->rule('username', 'alpha_dash')
				->rule('username', array($this, 'process_username'), array(':validation', ':field', 'username'));
				
			if ($post->check()) 
			{
				DB::query('NULL', 'BEGIN')->execute();
				$result = DB::query(Database::SELECT, "SELECT username FROM public.user WHERE activation_id = :activation_id")
				->param(':activation_id', $activation_id)
				->execute();
				if ( ! $result)
				{
					DB::query('NULL', 'ROLLBACK')->execute();
					throw new Kohana_Exception('site_error');
				}
				if (count($result) > 0)
				{
					$result = DB::query(Database::UPDATE, "UPDATE public.user SET username = :username, active = '1' WHERE activation_id = :activation_id AND active = '0'")
					->param(':username', $post['username'])
					->param(':activation_id', $activation_id)
					->execute();
					if ( ! $result)
					{
						DB::query('NULL', 'ROLLBACK')->execute();
						throw new Kohana_Exception('site_error');
					}
					DB::query('NULL', 'COMMIT')->execute();
					$this->auth->force_login($username);
					Request::current()->redirect('http://' . $this->cfg['www_domain']);
					
				}
			} 
			else 
			{
				$view->errors = $post->errors('set_username');
			}
		}
		$view->cfg = $this->cfg;
		$this->template->content = $view;
	}
	
	function action_index()
	{
		$view = View::factory(TEMPLATE . '/account/auth');
		$view->cfg = $this->cfg;
		if ($this->service != "")
		{
			if ($this->service->isUserConnected())
			{
				$view->service = $service;
		
			}
		}
		$this->template->content = $view;
	}
}
