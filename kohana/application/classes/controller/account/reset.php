<?php
class Controller_Account_Reset extends Controller_System
{
    public function before()
	{
		parent::before();
		$this->cfg = Kohana::$config->load('general.default');
		include Kohana::find_file('libraries', 'Recaptcha');
		$this->recaptcha = new Recaptcha;
	}
	
	public static function process_email(Validation $post = NULL, $field = NULL)
	{
		if (array_key_exists('email', $post->errors()))
			return;
		$email = $post[$field];
		$taken = DB::query(Database::SELECT, "SELECT COUNT(*) AS count FROM public.user WHERE email = :email")
		->param(':email', $email)
		->execute();
		if ($taken[0]['count'] < 1)
		{
			$post->error('email', 'email_not_exist');
		}
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
		$arr['header'] = I18n::get('header_reset');
		$view = View::factory(TEMPLATE . '/account/reset', $arr);
		$view->cfg = $this->cfg;
		if (TEMPLATE == "fullsite")
		{
			$view->recaptcha = $this->recaptcha->recaptcha_get_html();
		}
		if (Arr::get($_POST, 't') == 1)
		{
			$post = Validation::factory($_POST)
				->rule('email', 'not_empty')
				->rule('email', 'email')
				->rule('email', 'max_length', array(':value', 50))
				->rule('email', array($this, 'process_email'), array(':validation', ':field', 'email'));
				/*
				if (TEMPLATE == "fullsite")
				{
					$post->rule('recaptcha_response_field', array($this, 'process_recaptcha'), array(':validation', ':field', 'recaptcha_response_field'));
				}
				*/
			if ($post->check()) 
			{
				$email = Arr::get($_POST, 'email');
				$code = text::random($type = 'alnum', $length = 10);
				$email_message = sprintf(I18n::get('reset.email_body'), $post['email'], $code);
				$transport = Swift_MailTransport::newInstance();
				$mailer = Swift_Mailer::newInstance($transport);
				$message = Swift_Message::newInstance(I18n::get('reset.email_subject'))
				->setFrom(array($this->cfg["from_email"] => $this->cfg["site_name"] . ' Support'))
				->setTo($post['email'])
				->setBody($email_message, 'text/plain');
				$mailer->send($message);

				$exist = DB::query(Database::SELECT, "SELECT id FROM reset WHERE email = :email")
				->param(':email', $email)
				->execute();
				if ($exist[0]['id'] > 0)
				{
					$reset_id = $exist[0]['id'];
					DB::query(Database::INSERT, "UPDATE reset SET code = :code WHERE id = :reset_id")
					->param(':code', $code)
					->param(':reset_id', $reset_id)
					->execute();
				}
				else
				{
					DB::query(Database::INSERT, "INSERT INTO reset(email, code) VALUES(:email, :code)")
					->param(':email', $email)
					->param(':code', $code)
					->execute();
				}
				
				
				$view = View::factory(TEMPLATE . '/special_info', $arr);
				$view->msg = sprintf(I18n::get('reset.msg_email_sent'), $post['email']);
			}
			else
			{
				$view->errors = $post->errors('reset');
			}
		} 
		elseif (Arr::get($_POST, 't') == 2 OR (Arr::get($_GET, 'email') AND Arr::get($_GET, 'code')))
		{
			$view = View::factory(TEMPLATE . '/special_info', $arr);
			$email = Arr::get($_GET, 'email', Arr::get($_POST, 'email'));
			$code = Arr::get($_GET, 'code', Arr::get($_POST, 'code'));
			$result = DB::query(Database::SELECT, "SELECT EXTRACT(day from now() - reset_date) AS days FROM reset WHERE email = :email AND code = :code")
			->param(':email', $email)
			->param(':code', $code)
			->execute();
			if ( ! $result[0])
			{
				$view->msg = I18n::get('invalid_request');
			} 
			elseif ($result[0]['days'] > 7) 
			{
				$view->msg = I18n::get('expired_request');
			}
			elseif (Arr::get($_GET, 'email') AND Arr::get($_GET, 'code'))
			{
				$view = View::factory(TEMPLATE . '/account/reset_now', $arr);
				$view->email = $email;
				$view->cfg = $this->cfg;
			}
			else
			{
				$auth = Auth::instance();
				$post = Validation::factory($_POST)
					->rule('npassword', 'not_empty')
					->rule('npassword', 'min_length', array(':value', 6))
					->rule('npassword', 'max_length', array(':value', 50))
					->rule('cpassword', 'not_empty')
					->rule('cpassword', 'matches', array(':validation', 'npassword', 'cpassword'));
				if ($post->check()) 
				{
					DB::query(Database::DELETE, "DELETE FROM reset WHERE code = :code AND email = :email")
					->param(':code', $code)
					->param(':email', $email)
					->execute();
					$hashed_password = $auth->hash_password($post['npassword']);
					DB::query(Database::UPDATE, "UPDATE public.user SET password = :hashed_password WHERE email = :email")
					->param(':hashed_password', $hashed_password)
					->param(':email', $email)
					->execute();
					$view = View::factory(TEMPLATE . '/special_info', $arr);
					$view->msg = I18n::get('reset.msg_password_reset');
				}
				else
				{
					$view = View::factory(TEMPLATE . '/account/reset_now', $arr);
					$view->cfg = $this->cfg;
					$view->errors = $post->errors('reset');
				}
			} 
		}
		$this->template->title = I18n::get('header_reset');
		$this->template->content = $view;
	}
}
