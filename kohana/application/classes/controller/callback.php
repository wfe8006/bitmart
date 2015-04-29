<?php
class Controller_Callback extends Controller_System
{
	public function before()
	{
		parent::before();
	}
	
	function action_wallet()
	{
		$this->template->title = '';
		$this->template = View::factory(TEMPLATE . '/blank');
		$this->template->content = "";
		$crypto = Request::current()->param('crypto');
		$txid = Request::current()->param('txid');
		if ($crypto == '' OR $txid == '')
		{
			return -1;
		}
		else
		{
			if (array_key_exists($crypto, $this->cfg_crypto))
			{
				include Kohana::find_file('libraries', 'Crypto');
				include Kohana::find_file('libraries', 'jsonRPCClient');
				$array_crypto_cfg = $this->cfg_crypto[$crypto];
				$array_crypto_cfg['crypto'] = $crypto;
				$array_crypto_cfg['crypto_commission'] = $this->cfg['crypto_commission'];
				$crypto_obj = new Crypto($array_crypto_cfg);
				$crypto_obj->wallet_notify($txid);
				return 0;
			}
			else
			{
				return -1;
			}	
		}
	}
	
	function action_block()
	{
		$this->template->title = '';
		$this->template = View::factory(TEMPLATE . '/blank');
		$this->template->content = "";
		$crypto = Request::current()->param('crypto');
		$blockhash = Request::current()->param('txid');
		if ($crypto == '' OR $blockhash == '')
		{
			return -1;
		}
		else
		{
			if (array_key_exists($crypto, $this->cfg_crypto))
			{
				include Kohana::find_file('libraries', 'Crypto');
				include Kohana::find_file('libraries', 'jsonRPCClient');
				$array_crypto_cfg = $this->cfg_crypto[$crypto];
				$array_crypto_cfg['crypto'] = $crypto;
				$crypto_obj = new Crypto($array_crypto_cfg);
				$crypto_obj->block_notify($blockhash);
				return 0;
			}
			else
			{
				return -1;
			}	
		}
	}
}
