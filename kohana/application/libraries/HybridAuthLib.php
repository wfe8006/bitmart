<?php
require APPPATH.'/vendor/hybridauth/Hybrid/Auth.php';

class HybridAuthLib extends Hybrid_Auth
{
	function __construct($config = array())
	{
		parent::__construct($config);
		//log_message('debug', 'HybridAuthLib Class Initalized');
	}

	public static function serviceEnabled($service)
	{
		return isset(parent::$config['providers'][$service]) && parent::$config['providers'][$service]['enabled'];
	}
}