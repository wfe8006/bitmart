<?php
class Controller_Hauth extends Controller_System
{
	function action_login()
	{
		//print('debug', "controllers.HAuth.login($provider) called");
		try
		{
			//print('debug', 'controllers.HAuth.login: loading HybridAuthLib');
			$provider = $this->request->param('provider');
			include Kohana::find_file('libraries','HybridAuthLib');
			$this->hybridauthlib = new HybridAuthLib(Kohana::$config->load('hybridauth.default'));
			if ($this->hybridauthlib->serviceEnabled($provider))
			{
				//print('debug', "controllers.HAuth.login: service $provider enabled, trying to authenticate.");
				//logged in = 1
				$service = $this->hybridauthlib->authenticate($provider);
				if ($service->isUserConnected())
				{
					//print('debug', 'controller.HAuth.login: user authenticated.');
					$user_profile = $service->getUserProfile();
					//print"<pre>";
					//print_r($user_profile);
					//print"</pre>";
					//print('info', 'controllers.HAuth.login: user profile:'.PHP_EOL.print_r($user_profile, TRUE));
					$data['user_profile'] = $user_profile;
				}
				else // Cannot authenticate user
				{
					show_error('Cannot authenticate user');
				}
			}
			else // This service is not enabled.
			{
				//show_404($_SERVER['REQUEST_URI']);
			}
		}
		catch(Exception $e)
		{
			$error = 'Unexpected error';
			switch($e->getCode())
			{
				case 0 : $error = 'Unspecified error.'; break;
				case 1 : $error = 'Hybriauth configuration error.'; break;
				case 2 : $error = 'Provider not properly configured.'; break;
				case 3 : $error = 'Unknown or disabled provider.'; break;
				case 4 : $error = 'Missing provider application credentials.'; break;
				case 5 :// print('debug', 'controllers.HAuth.login: Authentification failed. The user has canceled the authentication or the provider refused the connection.');
				         //redirect();
				         if (isset($service))
				         {
				         	//print('debug', 'controllers.HAuth.login: logging out from service.');
				         	$service->logout();
				         }
				         $error = 'User has cancelled the authentication or the provider refused the connection.';
				         break;
				case 6 : $error = 'User profile request failed. Most likely the user is not connected to the provider and he should to authenticate again.';
				         break;
				case 7 : $error = 'User not connected to the provider.';
				         break;
			}

			if (isset($service))
			{
				$service->logout();
			}

			//print('error', 'controllers.HAuth.login: '.$error);
			//print "<br>error";
			//show_error('Error authenticating user.');
		}
	}

	function action_index()
	{
		Request::current()->redirect('http://' . $this->cfg['www_domain']);
	}
}