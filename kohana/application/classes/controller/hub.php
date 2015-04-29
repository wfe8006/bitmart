<?php
class Controller_Hub extends Controller_System
{
	public function before()
	{
		parent::before();
		$this->session = Session::instance();
		$this->auth = Auth::instance();
		$this->cfg = Kohana::$config->load('general.default');
		if (Request::current()->protocol() == "https")
		{
			Request::current()->redirect('http://' . $this->cfg['www_domain']);
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
	
	function action_index()
	{
		$username = Request::current()->param('username');
		$view = View::factory(TEMPLATE . '/hub');
		
		$user_obj = DB::query(Database::SELECT, "SELECT id AS user_id, info->'rating' AS rating, info->'total_rating' AS total_rating FROM public.user WHERE username = :username")
		->param(':username', $username)
		->execute();
		if (count($user_obj) > 0)
		{
			$user_id = json_decode($user_obj[0]['user_id']);
			$rating = json_decode($user_obj[0]['rating']);
			$total_rating = json_decode($user_obj[0]['total_rating']);

			include( __DIR__ . '/../../views/fullsite/search.php');
		}
		else
		{
			Request::current()->redirect('http://' . $this->cfg['www_domain']);
		}
		$this->template->content = $view;
	}
}
