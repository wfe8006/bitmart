<?php
class Controller_My_Order extends Controller_System
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
	
	function action_detail()
	{
		include 'order/detail.php';
	}

	function action_index()
	{
		include 'order/index.php';
	
	}
}
