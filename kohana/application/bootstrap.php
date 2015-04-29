<?php defined('SYSPATH') or die('No direct script access.');


// -- Environment setup --------------------------------------------------------

// Load the core Kohana class
require SYSPATH.'classes/kohana/core'.EXT;

if (is_file(APPPATH.'classes/kohana'.EXT))
{
	// Application extends the core
	require APPPATH.'classes/kohana'.EXT;
}
else
{
	// Load empty core extension
	require SYSPATH.'classes/kohana'.EXT;
}

/**
 * Set the default time zone.
 *
 * @see  http://kohanaframework.org/guide/using.configuration
 * @see  http://php.net/timezones
 */
/*
default timezone set to Los Angeles (GMT-8), which is 16 hours behind Kuala Lumpur (GMT+8), see http://www.worldtimebuddy.com/

running mkgmtime("1970-01-01") using the following timezone will return:

GMT+3 Europe/Moscow: -10800
GMT+3 Asia Kuwait: -10800
GMT+2 Europe/Helsinki: -7200
GMT+1 Europe/Paris: -3600
GMT Europe/London: -3600
GMT Europe/Dublin: -3600
GMT-1 Atlantic/Azores: 3600
GMT-2 Atlantic/Stanley: 14400
GMT-3 Greenland: 14400
GMT-4 America/Santiago: 10800
GMT-5 US/East-Indiana: 18000
GMT-6 America/Mexico_City: 21600
GMT-7 US/Mountain: 25200
GMT-7 US/Arizona: 25200
GMT-8 US/Pacific: 28800
GMT-9 US/Alaska: 36000
GMT-10 US/Hawaii: 36000
*/ 
date_default_timezone_set('America/Los_Angeles');


/**
 * Set the default locale.
 *
 * @see  http://kohanaframework.org/guide/using.configuration
 * @see  http://php.net/setlocale
 */
setlocale(LC_ALL, 'en_US.utf-8');

/**
 * Enable the Kohana auto-loader.
 *
 * @see  http://kohanaframework.org/guide/using.autoloading
 * @see  http://php.net/spl_autoload_register
 */
spl_autoload_register(array('Kohana', 'auto_load'));

/**
 * Enable the Kohana auto-loader for unserialization.
 *
 * @see  http://php.net/spl_autoload_call
 * @see  http://php.net/manual/var.configuration.php#unserialize-callback-func
 */
ini_set('unserialize_callback_func', 'spl_autoload_call');

// -- Configuration and initialization -----------------------------------------

/**
 * Set the default language
 */
I18n::lang('en-us');

Kohana::$environment = ENVIRONMENT == "development" ? Kohana::DEVELOPMENT : Kohana::PRODUCTION;

/**
 * Initialize Kohana, setting the default options.
 *
 * The following options are available:
 *
 * - string   base_url    path, and optionally domain, of your application   NULL
 * - string   index_file  name of your index file, usually "index.php"       index.php
 * - string   charset     internal character set used for input and output   utf-8
 * - string   cache_dir   set the internal cache directory                   APPPATH/cache
 * - boolean  errors      enable or disable error handling                   TRUE
 * - boolean  profile     enable or disable internal profiling               TRUE
 * - boolean  caching     enable or disable internal caching                 FALSE
 */
Kohana::init(array(
	'base_url'   => '/',
	'index_file' => '',
	'errors' => true,
	//'profile'  		=> (Kohana::$environment != Kohana::PRODUCTION),
	'profile' => 0,
	'caching'    	=> (Kohana::$environment == Kohana::PRODUCTION)
));

/**
 * Attach the file write to logging. Multiple writers are supported.
 */
Kohana::$log->attach(new Log_File(APPPATH.'logs'));

/**
 * Attach a file reader to config. Multiple readers are supported.
 */
Kohana::$config->attach(new Config_File);

/**
 * Enable modules. Modules are referenced by a relative or absolute path.
 */
Kohana::modules(array(
	'auth'       => MODPATH.'auth',       // Basic authentication
	// 'cache'      => MODPATH.'cache',      // Caching with multiple backends
	// 'codebench'  => MODPATH.'codebench',  // Benchmarking tool
	'database'   => MODPATH.'database',   // Database access
	'image'      => MODPATH.'image',      // Image manipulation
	'orm'        => MODPATH.'orm',        // Object Relationship Mapping
	// 'unittest'   => MODPATH.'unittest',   // Unit testing
	'userguide'  => MODPATH.'userguide',  // User guide and API documentation
	'pagination' => MODPATH.'pagination',
	'swiftmailer'	=> MODPATH.'swiftmailer',	// SwiftMailer
	 ));


/**
 * Set the routes. Each route must have a minimum of a name, a URI and a set of
 * defaults for the URI.
 */
 

Route::set('auth', 'account(/auth(/<action>(/<provider>)))',	
	array('provider' => '(Facebook|Yahoo|Twitter|Google)'))
	->defaults(array(
		'directory'  => 'account',
		'controller' => 'auth',
		'action'     => 'index',
	));		

/*	
Route::set('hub', 'hub(/<controller>(/<action>(/<id>)))',
	array('controller' => '(currency|listing|general|payment|physicalstore|shipping|tax|order)'))
	->defaults(array(
		'directory'  => 'hub',
		'controller' => 'profile',
		'action'     => 'index',
	));
*/



Route::set('my', 'my(/<controller>(/<action>(/<id>)))',
	array('controller' => '(message|purchase|currency|listing|general|payment|physicalstore|shipping|tax|order|cryptowallet)'))
	->defaults(array(
		'directory'  => 'my',
		'controller' => 'message',
		'action'     => 'index',
	));		
 
Route::set('account', 'account(/<controller>(/<action>(/<id>)))',
	array('controller' => '(activate|changeemail|changepassword|login|logout|messages|profile|reset|signup|summary|general)'))
	->defaults(array(
		'directory'  => 'account',
		'controller' => 'profile',
		'action'     => 'index',
	));
	

Route::set('vendor', 'vendor/<domain>',
	array('domain' => '[0-9a-zA-Z_\-\.]{3,30}'))
	->defaults(array(
		'controller' => 'vendor',
		'action'     => 'details',
	));
	
/*
Route::set('hub', 'hub/<domain>',
	array('domain' => '[0-9a-zA-Z_\-\.]{3,30}'))
	->defaults(array(
		'controller' => 'hub',
		'action'     => 'details',
	));	
*/
	
/*	
Route::set('product', 'product(/<domain>)',
	array('domain' => '[0-9a-zA-Z_\-\.]{3,30}'))
	->defaults(array(
		'controller' => 'product',
		'action'     => 'index',
	));
*/
	
/*
us for user-submitted	
st for system
*/
Route::set('listing', '<listing_type>(/<uid>)',
	array('uid' => '[0-9a-zA-Z_\-\.]{3,30}', 'listing_type' => 'us|st'))
	->defaults(array(
		'controller' => 'listing',
		'action'     => 'index',
	));

	
Route::set('callback', 'callback/<action>(/<crypto>(/<txid>))',
	array('crypto' => '[a-z]{3,4}', 'txid' => '[0-9a-zA-Z]{64}'))
	->defaults(array(
		'controller' => 'callback',
		'action'     => 'index',
	));	

/*
Route::set('callback', 'callback/<action>',
	array())
	->defaults(array(
		'controller' => 'callback',
		'action'     => 'index',
	));	
*/

/*
Route::set('callback', '<controller>(/<action>)',
	array('controller' => 'callback1'))
	->defaults(array(
		'controller' => 'callback1',
		'action'     => 'index',
	));
*/
	
Route::set('listing_contact', 'listingcontact',
	array())
	->defaults(array(
		'controller' => 'listingcontact',
		'action'     => 'index',
	));
	
Route::set('link', 'link',
	array())
	->defaults(array(
		'controller' => 'link',
		'action'     => 'index',
	));


Route::set('static', '<page>',
	array('page' => '(privacy|tou|contact|disclaimer|prohibitedcontent|safetyscams|faq)'))
	->defaults(array(
		'controller' => 'static',
		'action'	 => 'index',
	));		
		

Route::set('currency', '<currency>',
	array('currency' => '(btc|ltc|ppc|ftc|rdd|doge|meow|vtc|all)'))
	->defaults(array(
		'controller' => 'currency',
		'action'     => 'index',
	));	
	
Route::set('vendor_index', 'vendor',
	array())
	->defaults(array(
		'controller' => 'vendor',
		'action'     => 'index',
	));
	
Route::set('hub', 'hub(/<username>)',
	array())
	->defaults(array(
		'controller' => 'hub',
		'action'     => 'index',
	));
	
Route::set('feedback', 'feedback(/<username>)',
	array())
	->defaults(array(
		'controller' => 'feedback',
		'action'     => 'index',
	));
		
	
Route::set('location', 'location',
	array())
	->defaults(array(
		'controller' => 'location',
		'action'     => 'index',
	));

	
	

Route::set('view', 'view(/<id>)',
	array())
	->defaults(array(
		'controller' => 'view',
		'action'     => 'index',
	));

Route::set('go', 'go(/<id>)',
	array())
	->defaults(array(
		'controller' => 'go',
		'action'     => 'index',
	));
	


Route::set('preference', 'preference(/<action>)',
	array())
	->defaults(array(
		'controller' => 'preference',
		'action'	 => 'index',
	));	

Route::set('cart', 'cart(/<action>)',
	array())
	->defaults(array(
		'controller' => 'cart',
		'action'	 => 'index',
	));
	
	
Route::set('qr', 'qr(/<action>)',
	array())
	->defaults(array(
		'controller' => 'qr',
		'action'	 => 'index',
	));		

Route::set('json', '<page>(/<action>(/<param>))',
	array('page' => '(json)', 'param' => '.*'))
	->defaults(array(
		'controller' => 'json',
		'action'	 => 'index',
	));
	

	
//SEO-friendly link
/*
Route::set('category', '<category_name>/<cid>',
	array('category_id' => '[0-9]{1,20}', 'category_name' => '[A-Za-z0-9_-]{1,100}'))
	->defaults(array(
		'controller' => 'category',
		'action'     => 'index',
	));
*/	
	
Route::set('static', '<page>',
	array('page' => '(privacy|tou|contact|disclaimer)'))
	->defaults(array(
		'controller' => 'static',
		'action'	 => 'index',
	));		
	
	 

	
//single page
Route::set('page', '<controller>(/<action>(/<id>))',
	array('controller' => '(ads|details|select|postads|uploads|register)'))
	->defaults(array(
		'controller' => 'category',
		'action'	 => 'index',
	));

/*
Route::set('select_zip', '<l_url>/<select>',
    array('l_url' => '[0-9]{5}', 'select' => 'select'))
    ->defaults(array(
        'controller' => 'select',
        'action'     => 'index',
    ));
*/

/*
Route::set('browse', '<browse>', 
	array('browse' => 'b'))
    ->defaults(array(
		'controller' => 'category',
		'action'     => 'index',
	));
*/
	 
	 
//main page, index
//route /index to different pages depeding on $auth->logged_in()
//$auth = Auth::instance();
Route::set('index', '(<action>)',
	array('action' => '(index)'))
	->defaults(array(
		'controller' => 'category',
		'action'	 => 'index',
	));	 
	 
	 
	 
	 
	 
	 
	 

Route::set('error', 'error/<action>(/<message>)', array('action' => '[0-9]++', 'message' => '.+'))
    ->defaults(array(
        'controller' => 'error'
    ));	

Cookie::$domain = $domain; 
Cookie::$salt = 'L3f67_$%f^&4#-';
Session::$default = 'native';
