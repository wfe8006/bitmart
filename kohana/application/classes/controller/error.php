<?php defined('SYSPATH') or die('No direct script access.');
class Controller_Error extends Controller_System {
    public function before()
    {
        parent::before();
        // Internal request only!
        if (Request::$initial !== Request::$current) {
            //if ($message = rawurldecode($this->request->param('message'))) {
            //    $this->template->message = $message;
            //}
        } else {
            $this->request->action(404);
        }
        $this->response->status((int) $this->request->action());
    }
    public function action_404()
    {
		$this->template = View::factory(TEMPLATE . '/error/404');
    }
    public function action_500()
    {
		$this->template = View::factory(TEMPLATE . '/error/500');
    }
    public function action_503()
    {
        $this->template = View::factory(TEMPLATE . '/error/503');
    }
}