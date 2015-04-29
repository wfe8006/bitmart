<?php
class Controller_Currency extends Controller_System
{
	public function before()
	{
		parent::before();
	}

	function action_index()
	{
		$currency = Request::current()->param('currency');
		Request::current()->redirect("/preference?currency=$currency");
	}
}
