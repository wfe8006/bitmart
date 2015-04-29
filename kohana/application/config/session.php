<?php defined('SYSPATH') or die('No direct script access.');

/*
return array(
    'native' => array
	(
        'name' => 'SASESS',
        'lifetime' => 7200,
		'validate' => array('expiration'),
    ),
);
*/

return array(
    'native' => array
    (
        'name' => 'SASESS',
        'encrypted' => true,
        'lifetime' => 604800,
        'gc_probability' => 0,
     ),
	
	'cookie' => array(
        'name' => 'SACOOK',
        'encrypted' => true,
        'lifetime' => 43200,
    ),
);
