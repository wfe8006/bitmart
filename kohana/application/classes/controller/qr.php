<?php
class Controller_Qr extends Controller_System
{
	public function before()
	{
		parent::before();
		/*
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
		*/
	}
	
	function action_index()
	{
		include Kohana::find_file('libraries/qr', 'qrconst');
		include Kohana::find_file('libraries/qr', 'qrconfig');
		include Kohana::find_file('libraries/qr', 'qrtools');
		include Kohana::find_file('libraries/qr', 'qrspec');
		include Kohana::find_file('libraries/qr', 'qrimage');
		include Kohana::find_file('libraries/qr', 'qrinput');
		include Kohana::find_file('libraries/qr', 'qrvect');
		include Kohana::find_file('libraries/qr', 'qrbitstream');
		include Kohana::find_file('libraries/qr', 'qrsplit');
		include Kohana::find_file('libraries/qr', 'qrrscode');
		include Kohana::find_file('libraries/qr', 'qrmask');
		include Kohana::find_file('libraries/qr', 'qrencode');
		
		$address = Arr::get($_GET, 'address');
		$this->template = View::factory(TEMPLATE . '/blank');
		$this->template->content = QRcode::png(html::chars($address), false, "L", 5, 3);
		
	}
}
