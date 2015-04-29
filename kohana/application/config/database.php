<?php defined('SYSPATH') OR die('No direct access allowed.');

$ip = $_SERVER['SERVER_ADDR'];

if ($ip == DEVELOPMENT_SERVER)
{
    $postgresql_hostname = $ip;
    $postgresql_password = 'postgres';
    $sphinx_hostname = "192.168.0.5";
}
else if ($ip == PRODUCTION_SERVER)
{
    $postgresql_hostname = "127.0.0.1";
    $postgresql_password = '';
    $sphinx_hostname = "127.0.0.1";
}

return array
(
	'default' => array
	(
		'type'       => 'postgresql',
		'connection' => array(
			'hostname'   => $postgresql_hostname,
			'username'   => 'postgres',
			'password'   => $postgresql_password,
			'persistent' => FALSE,
			'database'   => 'bitmart',
		),
		'primary_key'  => '',
		'schema'       => '',
		'table_prefix' => '',
		'charset'      => 'utf8',
		'caching'      => FALSE,
		'profiling'    => TRUE,
	),
	
	'sphinx' => array
	(
		'type'       => 'mysql',
		'connection' => array(
			'hostname'	=> $sphinx_hostname,
			'persistent'=> FALSE,
		),
		'table_prefix' => '',
		'charset'      => 'utf8',
		'caching'      => FALSE,
		'profiling'    => TRUE,
	),
	'sphinx' => array
	(
		'type'       => 'pdo',
		'connection' => array(
		    'dsn'	=> "mysql:host=$sphinx_hostname;port=9306",
			'persistent'=> FALSE,
		),
		'table_prefix' => '',
		'charset'      => 'utf8',
		'caching'      => FALSE,
		'profiling'    => TRUE,
	),



);
