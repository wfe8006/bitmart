<?php
class Controller_Static extends Controller_System
{
	public function before()
	{
		parent::before();
		$this->cfg = Kohana::$config->load('general.default');
	}
	
	function process_recaptcha(Validation $post = NULL, $field = NULL)
	{
		if (array_key_exists('recaptcha_response_field', $post->errors()))
			return;
		$this->recaptcha->recaptcha_check_answer($_SERVER['REMOTE_ADDR'], $_POST['recaptcha_challenge_field'], $_POST['recaptcha_response_field']);
		if ($this->recaptcha->is_valid != 1)
		{
			$post->error('recaptcha_response_field', 'check_recaptcha_error');
		}
	}
	
	function action_index()
	{
		$page = Request::current()->param('page');
		$this->template->title = i18n::get('title_' . $page) . " | " . $this->cfg["site_name"];
		$view = View::factory(TEMPLATE . "/$page");
		if ($page == 'contact')
		{
			$this->auth = Auth::instance();
			$this->auth->auto_login();
			$logged_in = $this->auth->logged_in();
			
			$view->message = "";
			$view->logged_in = $logged_in;

			
			if ($logged_in)
			{
				$this->user = $this->auth->get_user();
				if ($_POST)
				{
					$post = Validation::factory($_POST)
						->rule('message', 'not_empty');
						
						
					if ($post->check()) 
					{
						$ip = $_SERVER['REMOTE_ADDR'];	
						$email = $this->user->email;
						$name = $this->user->username;
						$message = Arr::get($_POST, 'message');
						$transport = Swift_MailTransport::newInstance();
						$mailer = Swift_Mailer::newInstance($transport);
						$mail = Swift_Message::newInstance($this->cfg["site_name"] . ' Feedback')
						->setFrom(array($email => $name))
						->setTo($this->cfg["support_email"])
						->setBody($message . "\r\n\r\nip address: $ip", 'text/plain');
						$mailer->send($mail);
						$arr['header'] = "Contact Us";
						$view = View::factory(TEMPLATE . '/special_info', $arr);
						$view->msg = "Thank you, your feedback has been submitted.";
					}
					else
					{
						$view->logged_in = $logged_in;
						$view->errors = $post->errors('reset');
						$view->message = Arr::get($_POST, 'message');
						$view->msg = "";
					}
				}
			}
			else
			{
				
				if (TEMPLATE == "fullsite")
				{
					include Kohana::find_file('libraries', 'Recaptcha');
					$this->recaptcha = new Recaptcha;
					$view->recaptcha = $this->recaptcha->recaptcha_get_html();
				}
				$view->name = "";
				$view->email = "";
				if ($_POST)
				{
					$post = Validation::factory($_POST)
						->rule('name', 'not_empty')
						->rule('email', 'not_empty')
						->rule('email', 'email')
						->rule('message', 'not_empty');
						/*
						if (TEMPLATE == "fullsite")
						{
							$post->rule('recaptcha_response_field', array($this, 'process_recaptcha'), array(':validation'));
						}
						*/
						
					if ($post->check()) 
					{
						$ip = $_SERVER['REMOTE_ADDR'];	
						$email = Arr::get($_POST, 'email');
						$name = Arr::get($_POST, 'name');
						$message = Arr::get($_POST, 'message');
						$transport = Swift_MailTransport::newInstance();
						$mailer = Swift_Mailer::newInstance($transport);
						$mail = Swift_Message::newInstance($this->cfg["site_name"] . ' Feedback')
						->setFrom(array($email => $name))
						->setTo($this->cfg["support_email"])
						->setBody($message . "\r\n\r\nip address: $ip", 'text/plain');
						$mailer->send($mail);
						$arr['header'] = "Contact Us";
						$view = View::factory(TEMPLATE . '/special_info', $arr);
						$view->msg = "Thank you, your feedback has been submitted.";
					}
					else
					{
						$view->errors = $post->errors('reset');
						$view->name = Arr::get($_POST, 'name');
						$view->email = Arr::get($_POST, 'email');
						$view->message = Arr::get($_POST, 'message');
						$view->msg = "";
					}
				}
			}
			
			
			
		}

		$view->cfg = $this->cfg;
		$view->cc = 'ccfff';
		$this->template->content = $view;
	}
}
