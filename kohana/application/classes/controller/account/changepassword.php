<?php
class Controller_Account_Changepassword extends Controller_System
{
	public function before()
	{
		parent::before();
		$this->cfg = Kohana::$config->load('general.default');
		$this->session = Session::instance();
		$this->auth = Auth::instance();
		$this->auth->auto_login();
		if ($this->auth->logged_in())
		{
			$this->user = $this->auth->get_user();
		}
		else
		{
			Request::current()->redirect('https://' . $this->cfg['www_domain'] . '/account/auth');
		}
	}	
	function process_opassword(Validation $post = NULL)
	{
		if (array_key_exists('opassword', $post->errors()))
			return;
		if ($this->auth->hash_password($_POST['opassword']) != $this->auth->get_user()->password)
		{
			$post->error('opassword', 'incorrect_password');
		}
	}
	
	function process_npassword(Validation $post = NULL)
	{
		if (array_key_exists('npassword', $post->errors()))
			return;
		if ($this->auth->hash_password($_POST['npassword']) == $this->auth->get_user()->password)
		{
			$post->error('npassword', 'same_password');
		}
	}	
		
	function action_index()
	{
		$arr['header'] = I18n::get('change_password');
		$view = View::factory(TEMPLATE . '/account/changepassword', $arr);
		$view->auth = $this->auth;
		$view->cfg = $this->cfg;
		if ($_POST)
		{
			$post = Validation::factory($_POST)
				->rule('npassword', 'not_empty')
				->rule('npassword', 'min_length', array(':value', 6))
				->rule('npassword', 'max_length', array(':value', 40))
				->rule('cpassword', 'not_empty')
				->rule('cpassword', 'matches', array(':validation', 'npassword', 'cpassword'))
				->rule('npassword', array($this, 'process_npassword'), array(':validation', ':field', 'npassword'));
			
			$secret = $this->cfg['key'];
			$hashed_secret = $this->auth->hash_password($this->auth->get_user()->username . $secret);
			//registered via social account, user didn't change the password, so no password available and we hide the current password field
			if ($this->auth->get_user()->password == $hashed_secret)
			{
			}
			else
			{
				$post->rule('opassword', array($this, 'process_opassword'), array(':validation', ':field', 'opassword'));
			}
			
			if ($post->check())
			{
				$hashed_password = $this->auth->hash_password($post['npassword']);
				$id = (int)$this->auth->get_user()->id;
				DB::query(Database::UPDATE, "UPDATE public.user SET password = :hashed_password WHERE id = :id")
				->param(':hashed_password', $hashed_password)
				->param(':id', $id)
				->execute();
				$view = View::factory(TEMPLATE . '/special_info', $arr);
				$view->url = "https://" . $this->cfg['www_domain'] . "/account/profile";
				$view->msg = I18n::get('changepassword.msg_password_changed');
			} 
			else 
			{

				
				$view->errors = $post->errors('changepassword');
			}
		}
		$this->template->title = I18n::get('header_changepassword');
		$this->template->content = $view;
	}
}
