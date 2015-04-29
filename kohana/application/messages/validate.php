<?php defined('SYSPATH') or die('No direct script access.');

return array(
	'not_empty'    => 'This is a required field.',
	'matches'      => ':field must be the same as :param1',
	'regex'        => ':field does not match the required format',
	'exact_length' => ':field must be exactly :param1 characters long',
	'min_length'   => ':field must be at least :param1 characters long',
	'max_length'   => ':field must be less than :param1 characters long',
	'in_array'     => ':field must be one of the available options',
	'alpha'        => 'Please use string only in this field',
	'digit'        => 'Please use numbers only in this field (no dots or dashes)',
	'decimal'      => ':field must be a decimal with :param1 places',
	'range'        => ':field must be within the range of :param1 to :param2',
	'alpha_numeric'=> 'Please use alphabetical characters and numbers only in this field',
);