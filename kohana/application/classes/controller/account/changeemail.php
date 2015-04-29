<?php
class Controller_Account_Changeemail extends Controller_System
{
    public function before()
	{
		parent::before();
		$this->session = Session::instance();
		$this->auth = Auth::instance();
		$this->auth->auto_login();
		$this->cfg = Kohana::$config->load('general.default');
		if ($this->auth->logged_in())
		{
			$this->user = $this->auth->get_user();
		}
		else
		{
			Request::current()->redirect('https://' . $this->cfg['www_domain'] . '/account/auth');
		}
	}
	
	function action_index()
	{
		$email = Arr::get($_GET, 'email');
		$code = Arr::get($_GET, 'code');
		$view = View::factory(TEMPLATE . '/special_info', array('header' => I18n::get('header_changeemail')));
		$result = DB::query(Database::SELECT, "SELECT EXTRACT(day FROM now() - change_date) AS days, user_id, email FROM new_email WHERE email = :email AND code = :code")
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
		else
		{
			DB::query(Database::UPDATE, "UPDATE public.user SET email = '{$result[0]['email']}' WHERE id = :id")
			->param(':id', $result[0]['user_id'])
			->execute();
			$view->msg = I18n::get('changeemail.msg_email_changed');
		}
		$view->url = "http://" . $this->cfg['www_domain'] . "/";
		$this->template->title = I18n::get('header_changeemail');
		$this->template->content = $view;
	}
}
