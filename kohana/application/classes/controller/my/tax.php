<?php
class Controller_My_Tax extends Controller_System
{
	public function before()
	{
		parent::before();
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
		$user_id = $this->auth->get_user()->id;	
		$view = View::factory(TEMPLATE . '/my/tax');
		
		$country_obj = DB::query(Database::SELECT, "SELECT id, name FROM country WHERE status = '1' ORDER BY name")->execute();
		$tax_region_obj = DB::query(Database::SELECT, "SELECT tr.country_id, gi.name, gi.short_name, tr.data FROM tax_region tr LEFT JOIN geo_info gi ON tr.geo_info_id = gi.id ")->execute();
		$array_tax_region = array();
		foreach ($tax_region_obj as $result)
		{
			$array_tax_region[$result['country_id']]['short_name'] = $result['short_name'];
			$array_tax_region[$result['country_id']]['name'] = $result['name'];
			$array_tax_region[$result['country_id']]['data'] = self::object_to_array(json_decode($result['data']));
		}
		//print'<pre>';
		//print_r($array_tax_region);
		//print'</pre>';
		
		if ($_POST)
		{
			$array_data = array();
			foreach ($country_obj as $result)
			{
				$rate = sprintf("%0.2f", Arr::get($_POST, "rate_{$result['id']}"));
				if ($rate != 0.00)
				{
					$array_data[$result['id']] = array();
					$array_data[$result['id']]['rate'] = $rate;
					$array_data[$result['id']]['type'] = (int) Arr::get($_POST, "type_{$result['id']}");
					$array_data[$result['id']]['name'] = '';
				}
				if (isset($array_tax_region[$result['id']]))
				{
					$region_name = $array_tax_region[$result['id']]['short_name'];
					$array_data[$result['id']]['name'] = $region_name;
					$array_data[$result['id']][$region_name] = array();
					foreach ($array_tax_region[$result['id']]['data'] as $index => $name)
					{
						$rate = sprintf("%0.2f", Arr::get($_POST, "rate_{$result['id']}_$index"));
						if ($rate != 0.00)
						{
							$array_data[$result['id']][$region_name][$index] = array();
							$array_data[$result['id']][$region_name][$index]['rate'] = $rate;
							$array_data[$result['id']][$region_name][$index]['type'] = (int) Arr::get($_POST, "type_{$result['id']}_$index");
						}
					}
				}
			}
			$json_data = json_encode($array_data);
			DB::query(Database::UPDATE, "UPDATE tax SET data = :json_data WHERE user_id = :user_id")
			->param(':json_data', $json_data)
			->param(':user_id', $user_id)
			->execute();
			//Request::current()->redirect('/my/tax?s=u');
		}
		$tax_obj = DB::query(Database::SELECT, "SELECT data FROM tax WHERE user_id = :user_id")
		->param(':user_id', $user_id)
		->execute();
		$array_tax = self::object_to_array(json_decode($tax_obj[0]['data']));

		$success = Arr::get($_GET, 's', '');
		if ($success != '')
		{
			if($success == 'u')
			{
				$view->msg = I18n::get('record_updated');
			}
		}
		$view->array_tax = $array_tax;
		$view->array_tax_region = $array_tax_region;
		$view->country_obj = $country_obj;	
		$view->cfg = $this->cfg;		
		$this->template->content = $view;
	}
}
