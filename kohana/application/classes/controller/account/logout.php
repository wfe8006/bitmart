<?php
class Controller_Account_Logout extends Controller_System
{
  	public function action_index()
	{
		$this->cfg = Kohana::$config->load('general.default');
		$_SESSION = array();
		if (ini_get("session.use_cookies"))
		{
			$params = session_get_cookie_params();
			setcookie(session_name(), '', time() - 42000,
				$params["path"], $params["domain"],
				$params["secure"], $params["httponly"]
			);
		}
		Auth::instance()->logout();
		session_destroy();
		//temporarily redirect the page to main page
	 	Request::current()->redirect('http://' . $this->cfg['www_domain']);
	}
}
