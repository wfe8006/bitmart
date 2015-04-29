<?php defined('SYSPATH') or die('No direct script access.');
abstract class Controller_System extends Controller_Template {
	public $template = DEFAULT_TEMPLATE;
	private $benchmark;
	
	public function strstr_array($haystack, $needle_array) 
	{
		if ( ! is_array($needle_array))
		{
			return false;
		}
		foreach ($needle_array as $needle) 
		{
			if (strstr($haystack, $needle))
			{
				return $haystack;
			}
		}
	}
	
	private function trim_input($param)
	{
		if ( is_array($param))
			return $param;
		else
			return trim($param);
	}
	
	public function before()
	{
		// Be sure to only profile if it's enabled
		if (Kohana::$profiling === TRUE AND ! $this->request->is_ajax())
		{
			$this->benchmark = Profiler::start('Your Category', __FUNCTION__);
		}
		
		
		
		

		$_POST = array_map('self::trim_input', $_POST);
		$_GET = array_map('self::trim_input', $_GET);
		
		//only use xss_clean when we want to display html output, we will use html::chars to filter non-html output
		
		//$_POST = array_map(array('Security', 'xss_clean'), $_POST);
		//$_GET = array_map(array('Security', 'xss_clean'), $_GET);


		parent::before();
		if (Request::initial()->is_ajax())
		{
			$this->template = new View(TEMPLATE . '/blank');
		}
		
		
	
		
		
		
		
		$this->cfg = Kohana::$config->load('general.default');
		$this->cfg_currency = Kohana::$config->load('general.currency');
		$this->cfg_crypto = Kohana::$config->load('general.crypto');
		$this->session = Session::instance();
		$this->encrypt = Encrypt::instance('tripledes');
		$this->auth = Auth::instance();
		$this->template->item_count = 110;
		
		//this probably shouldn't be here, in classes/controller/account/auth set_username needs session['code'] to work
		//as user logged in using hybridauth won't be able to set the username directly (because $haystack code has excluded auth controller from request_url, so we need to use this method to let them change their usernames
		if ($this->auth->logged_in())
		{
			if (Request::current()->controller() != 'auth' AND $this->auth->get_user()->active == 0)
			{
				//echo 'https://' . $this->cfg['www_domain'] . '/account/auth/set_username';
				//exit;
				Request::current()->redirect('https://' . $this->cfg['www_domain'] . '/account/auth/set_username');
				exit;
			}
		}
		
		$uri = $_SERVER['REQUEST_URI'];
		$qs_arr = explode('?', $uri);
		$qs = count($qs_arr) > 1 ? "?" . $qs_arr[1] : "";
		$haystack = array(
						'account/login',
						'account/logout',
						'account/auth',
						'account/signup',
						'account/activate',
						'preference',
						'ltc',
						'btc',
						'ppc',
						'ftc',
						'doge',
						'meow',
						'rdd',
						'vtc',
						'json',
						'all',
					);
					
		$session_url = substr($this->session->get('requested_url'), 1);
		if (in_array($session_url, $haystack))
		{
			$this->session->set('requested_url', '');
		}
		else
		{
			if (self::strstr_array($this->request->uri(), $haystack))
			{
				if ($this->request->uri() != 'preference')
				{
					//$this->session->set('requested_url', '');
				}
			}
			else
			{
				//$this->session->set('requested_url', URL::site($this->request->uri()) . $qs);
			}
		}
	}
	
	function sanitize_output($buffer)
			{
				$search = array(
					'/\>[^\S ]+/s', //strip whitespaces after tags, except space
					'/[^\S ]+\</s', //strip whitespaces before tags, except space
					'/(\s)+/s'  // shorten multiple whitespace sequences
					);
				$replace = array(
					'>',
					'<',
					'\\1'
					);
				$buffer = preg_replace($search, $replace, $buffer);

				return $buffer;
			}
	
	public function after()
	{
		if ($this->auto_render)
		{
			
			//print "<br>".
			//print "<br>====" . __('Hello, world!');
			//echo __('layout.signed_in_as', array(':name'=> 'test'), '');
			//echo I18n::lang()."lang";
			//$pos = strpos(Router::$routed_uri, Router::$controller);
			//$url = substr(Router::$routed_uri, 0, $pos+strlen(Router::$controller));
			//$this->template->content = '';
			//$this->header = Kohana::lang('headers.'.$url."/".Router::$method);
			//$this->header = __(Kohana::message('about', 'title'), $params);
			//$template = 'templates/default';
			parent::after();
			//ob_start(array($this, 'sanitize_output'));
			if (isset($this->benchmark))
			{
				Profiler::stop($benchmark);
				echo View::factory('profiler/stats');
			}
			//$this->template->content["cfg"] = $this->cfg;
		}
	}
}
