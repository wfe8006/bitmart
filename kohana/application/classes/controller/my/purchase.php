<?php
class Controller_My_Purchase extends Controller_System
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

	function action_detail()
	{
		include 'order/detail.php';
	}
	
	function action_index()
	{
		include 'order/index.php';
	}
}
