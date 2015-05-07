<?php
if (Request::current()->uri() != 'hub/listing1' AND Request::current()->uri() != '/1')
{
	$container = 'container';
}
else
{
	$container = '';
}
$total_cart_item = cookie::get("total_cart_item", 0);
//$total_cart_item =  Session::instance()->get('total_cart_item', 0);


function object_to_array2($object)
{
	if (! is_object($object) AND ! is_array($object))
	{
		return $object;
	}
	if (is_object($object))
	{
		$object = get_object_vars($object);
	}
	return array_map('object_to_array2', $object);
}

$cfg = Kohana::$config->load('general.default');
$as_loc = cookie::get("as_loc");
$get_l = Arr::get($_GET, "l");
if (isset($l))
{
	$l = $get_l;
}
else
{
	if ($as_loc == "")
	{
		$l = "/?";
	}
	else
	{
		$l = "/?l=$as_loc&";
	}
}

$preference = I18n::get('currency');
$cookie_preference = object_to_array2(json_decode(Cookie::get("preference", 0)));

function is_bot()
{
    $bots = array(
		'AddThis.com',
        'Baiduspider',
		'bingbot',
		'Butterfly',
		'facebookexternalhit',
		'Googlebot',
		'ia_archiver',
		'msnbot',
		'NetcraftSurveyAgent',
		'PrintfulBot',
		'RavenCrawler',
        'R6_FeedFetcher',
		'Sogou web spider',
		'TweetmemeBot',
		'Twitterbot',
		'UnwindFetchor',
		'urlresolver',
		'Yahoo! Slurp',
    );
    foreach ($bots as $b)
	{
        if ( stripos( $_SERVER['HTTP_USER_AGENT'], $b ) !== false ) return true;
    }
    return false;
}

//cookie is not set, get it from maxmind api
//no cookie found:

if ( ! is_bot() AND $cookie_preference === 0)
{
	$cookie_preference = array();
	/*
	$cc = geoip_country_code_by_name(Request::$client_ip);
	if ($cc == false)
	{
		$cc = 'US';
	}
	$preference_obj = DB::query(Database::SELECT, "SELECT c.id AS country_id, c.name, c.iso3166, cr.id AS currency_id, cr.iso4217 FROM country c LEFT JOIN currency cr ON c.currency_id = cr.id WHERE iso3166 = '$cc' ORDER BY name")->execute();
	if (count($preference_obj) > 0)
	{
		$cookie_preference = array();
		$cookie_preference['convert_currency'] = 1;
		$cookie_preference['country_id'] = $preference_obj[0]['country_id'];
		$cookie_preference['country_name'] = $preference_obj[0]['name'];
		$cookie_preference['country_code'] = $cc;
		$cookie_preference['currency_id'] = $preference_obj[0]['currency_id'];
		$cookie_preference['currency_code'] = $preference_obj[0]['iso4217'];
		cookie::set("preference", json_encode($cookie_preference), Date::YEAR);
	}
	*/
	
	//temporary settings until we launch location service
	$cookie_preference['cryptocurrency'] = 'original';
	cookie::set("preference", json_encode($cookie_preference), Date::YEAR);
	Request::current()->redirect('/');
}

$convert_currency = $cookie_preference['convert_currency'];
$currency_code = $cookie_preference['currency_code'];
$cryptocurrency = $cookie_preference['cryptocurrency'];
$country_name = $cookie_preference['country_name'];

$preference = I18n::get('listings') . ': ';
if ($cryptocurrency !== 0)
{
	$preference .= strtoupper($cryptocurrency);
}
else
{
	if ($currency_code == '')
	{
		$preference .= I18n::get('all');

	}
	else
	{
		$preference .= strtoupper($currency_code);
	}
}

/*
if ($convert_currency == 1)
{
	$currency_code = $cookie_preference['currency_code'];
	if ($country_name != '' AND $currency_code != '')
	{
		$preference = "$country_name - " . strtoupper($currency_code);
	}
	else if ($country_name != '' AND $currency_code == '')
	{
		$preference = $country_name;
	}
	else if ($country_name == '' AND $currency_code != '')
	{
		$preference = strtoupper($currency_code);
	}
}
else
{
	if ($country_name != '')
	{
		$preference = $country_name;
	}
}
*/
?>
<!DOCTYPE html>
<html lang="en">
	<head>
		<title><?php echo isset($title) ? html::chars($title) : $cfg["site_name"] . " Marketplace | Buy and Sell goods with Bitcoin" ?></title>
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<meta name="description" content="<?php echo isset($description) ? html::chars($description) : "Buy and sell for almost everything with cryptocurrencies at " . $cfg["site_name"] ?>">
		<meta name="keywords" content="<?php echo isset($keywords) ? $keywords : "classifieds, classifieds listing, bitcoin classifieds, bitcoin marketplace, litecoin marketplace, buy and sell online, free online store, use bitcoin, bitcoin shopping, cryptocurrencies, bitcoin online store, litecoin online store, buy and sell, sell online, online shopping" ?>">
		<meta property="fb:app_id" content="<?php echo $cfg["fb_app_id"] ?>" />  
		<meta property="og:title" content="<?php echo isset($title) ? $title : $cfg["site_name"] . " Marketplace | Buy and Sell goods with Bitcoin" ?>" />
		<meta property="og:image" content="http://<?php echo isset($og_img) ? $og_img : $cfg['www_domain'] . '/logo.png' ?>" /> 
		<meta property="og:description" content="<?php echo isset($description) ? html::chars($description) : "Buy and sell for almost everything with cryptocurrencies at " . $cfg["site_name"] ?>" />
		<meta property="og:url" content="http://<?php echo $cfg['www_domain'] . URL::site(Request::current()->uri()) ?>" />
		<meta property="og:site_name" content="<?php echo $cfg["site_name"] ?>" />
		<meta property="og:type" content="article" />
		<link href="/css/bootstrap.min.css" rel="stylesheet" media="screen">
		<link href="<?php echo $cfg['fullsite.css'] ?>" rel="stylesheet" type="text/css" media="screen">
		<!-- HTML5 shim and Respond.js IE8 support of HTML5 elements and media queries -->
		<!--[if lt IE 9]>
		  <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
		  <script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
		<![endif]-->
	</head>
	<body>
		<header class="navbar navbar-static-top bs-docs-nav" id="top" role="banner">
			<div class="container-main">
				<div class="navbar-header">
					<button class="navbar-toggle" type="button" data-toggle="collapse" data-target=".bs-navbar-collapse">
						<span class="sr-only">Toggle navigation</span>
						<span class="icon-bar"></span>
						<span class="icon-bar"></span>
						<span class="icon-bar"></span>
					</button>
					<a href="../" class="navbar-brand"><span id="logo_all"><?php echo $cfg["site_name"] ?></span></a>
				</div>
				
				<nav class="collapse navbar-collapse bs-navbar-collapse" role="navigation">
					<ul class="nav navbar-nav">
					<?php
					if (Auth::instance()->logged_in())
					{
					?>
						<li class="dropdown">
							<a href="#" class="dropdown-toggle" data-toggle="dropdown"><?php echo I18n::get('my_store') ?> <b class="caret"></b></a>
							<ul class="dropdown-menu">
								<li><a href="/my/listing/new"><?php echo I18n::get('new_listing') ?></a></li>
								<li><a href="/my/listing"><?php echo I18n::get('my_listings') ?></a></li>
								<li><a href="/my/order"><?php echo I18n::get('sales_order') ?></a></li>
								<li><a href="/my/general"><?php echo I18n::get('settings') ?></a></li>
								<li><a href="/hub/<?php echo Auth::instance()->get_user()->username ?>"><?php echo I18n::get('store_url') ?></a></li>
							 </ul>
						</li>
					
						<li class="dropdown">
							<a href="#" class="dropdown-toggle" data-toggle="dropdown"><?php echo I18n::get('my_account') ?> <b class="caret"></b></a>
							<ul class="dropdown-menu">
								<li><a href="/account/profile"><?php echo I18n::get('profile') ?></a></li>
								<li><a href="/my/cryptowallet"><?php echo I18n::get('wallets') ?></a></li>
								<li><a href="/my/purchase"><?php echo I18n::get('purchase_history') ?></a></li>
								<li><a href="/my/message"><?php echo I18n::get('messages') ?></a></li>
							 </ul>
						</li>
						<li><a href="https://<?php echo $cfg['www_domain'] ?>/account/logout"><?php echo I18n::get('log_out') ?></a></li>
					
					<?php
					}
					else
					{
					?>
						<li><a href="https://<?php echo $cfg['www_domain'] ?>/account/auth"><?php echo I18n::get('log_in') ?></a></li>
						
						<li><a href="https://<?php echo $cfg['www_domain'] ?>/account/signup"><?php echo I18n::get('sign_up') ?></a></li>
					<?php
					}
					?>
		
					
						<li><a href="/cart"><?php echo I18n::get('cart') . " ($total_cart_item)" ?></a></li>
						<li class="dropdown">
							<a href="#" class="dropdown-toggle" data-toggle="dropdown"><?php echo $preference ?><b class="caret"></b></a>
							<ul class="dropdown-menu">
								<li><a href="/preference?currency=btc"><?php echo I18n::get('bitcoin') ?></a></li>
								<li><a href="/preference?currency=ltc"><?php echo I18n::get('litecoin') ?></a></li>
								<li><a href="/preference?currency=ppc"><?php echo I18n::get('peercoin') ?></a></li>
								<li><a href="/preference?currency=doge"><?php echo I18n::get('dogecoin') ?></a></li>
	
								<!--
								<li><a href="/preference?currency=meow"><?php echo I18n::get('kittehcoin') ?></a></li>
								<li><a href="/preference?currency=rdd"><?php echo I18n::get('reddcoin') ?></a></li>
								//-->
								<li><a href="/preference?currency=all"><?php echo I18n::get('all') ?></a></li>
								<li><a href="/preference"><?php echo I18n::get('search_preferences') ?></a></li>
							 </ul>
						</li>
						
					</ul>
					<form class="navbar-form navbar-right navbar-input-group" role="search" action="/" method="get">
						<div class="form-group">
							<input id="q" name="q" type="text" placeholder="Search..." class="form-control" value="">
						</div>
						<button type="submit" class="btn btn-danger">Go</button>
					</form>

				</nav>
			</div>
		</header>
		<hr class="hr_main">

		<div class="container wrapper">   


			<div class="container">
				<div class="row content">
					<?php echo $content ?>
				</div>
				<div class="footer">
					<hr>
	  
					<span class="col-lg-2">
						<ul class="nav nav-list">
							<li><a href="/tou">Terms of Use</a></li>
							<li><a href="/privacy">Privacy Policy</a></li>
							<li><a href="/disclaimer">Disclaimer</a></li>
						</ul>
					</span>

					<span class="col-lg-2">
						<ul class="nav nav-list">
							<li><a href="/contact">Contact Us</a></li>
						</ul>
					</span>
					<span class="col-lg-2">
						
					</span>
					<div class="clearfix"></div>
					<div class="pull-right">
					  <p class="text-muted"><small>Copyright &copy; <?php echo $cfg["site_name"] ?></small></p>
					</div>
				</div>
			</div>
		</div>


	
	
		<script src="//netdna.bootstrapcdn.com/bootstrap/3.1.1/js/bootstrap.min.js"></script>
		
	
	</body>
</html>
