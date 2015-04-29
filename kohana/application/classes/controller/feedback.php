<?php
class Controller_Feedback extends Controller_System
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
		$view = View::factory(TEMPLATE . '/feedback');
		
		$user_obj = DB::query(Database::SELECT, "SELECT id AS user_id FROM public.user WHERE username = :username")
		->param(':username', $username)
		->execute();
		if (count($user_obj) > 0)
		{	
			$user_id = $user_obj[0]['user_id'];
			$view->username = $username;
			$rating_obj = DB::query(Database::SELECT, "SELECT order_id, u.username, u.info->'rating' AS rating, seller_feedback AS feedback FROM user_rating ur LEFT JOIN public.user u ON seller_id = u.id WHERE (seller_feedback->>'status')::integer = 1 AND buyer_id = :user_id UNION ALL SELECT order_id, u.username, u.info->'rating' AS rating, buyer_feedback AS feedback FROM user_rating ur LEFT JOIN public.user u ON buyer_id = u.id WHERE (buyer_feedback->>'status')::integer = 1 AND seller_id = :user_id ORDER BY order_id DESC")
			->param(':user_id', $user_id)
			->execute();
		}
		$view->rating_obj = $rating_obj;
		$this->template->content = $view;
	}
}
