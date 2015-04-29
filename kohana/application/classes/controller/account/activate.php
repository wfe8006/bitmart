<?php
class Controller_Account_Activate extends Controller_System
{
	function action_index()
	{	
		$this->cfg = Kohana::$config->load('general.default');	
		$view = View::factory(TEMPLATE . '/special_info', array('header' => I18n::get('header_activate')));
		$id = Arr::get($_GET, 'id');
		$result = DB::query(Database::SELECT, "SELECT COUNT(*) AS count FROM public.user WHERE activation_id = :id AND active = '0'")
		->param(':id', $id)
		->execute();
		if ($result[0]['count'] > 0)
		{
			DB::query(Database::UPDATE, "UPDATE public.user SET active = '1' WHERE activation_id = :id AND active = '0'")
			->param(':id', $id)
			->execute();
			$view->msg = sprintf(I18n::get('activate.msg_activation_success'), 'https://' . $this->cfg['www_domain']);
		} 
		else 
		{	
			$view->msg = I18n::get('activate.msg_activation_failed');
		}
		$this->template->title = I18n::get('header_activate');
		$this->template->content = $view;
	}
}
