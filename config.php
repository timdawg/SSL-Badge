<?php
	// Enable Error Reporting (debug only)
	//ini_set('display_errors',1);
	//error_reporting(E_ALL|E_STRICT);

	// Config Constants	
	define('ALLOW_ANY', true);		// true = allowed to be used for any website / false = restricted to the domains in $allowed_domains
	define('GENERATE_FORM', true);		// true = enable HTML code generator form
	define("ALLOWED_DOMAINS", serialize(array()));		// String array of allowed domains (if ALLOW_ANY = false)
	define('IMG_PATH', 'images/');			// Path to large SVG images
	define('IMG_PATH_SM', 'images/sm/');	// Path to small SVG images
	define('REPORT_CACHE_AGE', 24);			// max cache report age (hours)
	define('BROWSER_CACHE_AGE', 86400);		// how long (seconds) the browser should cache the image [set to 0 to disable browser cache]
	define('APC_CACHE_AGE', 86400);			// how long (seconds) to cache results in APC cache (on this server) [set to 0 to disable APC cache]
	// MySQL Config (If MySQL is enabled, APC is not used)
	define('MYSQL_CACHE_AGE', 0);		// how long (seconds) to cache results in the MySQL database [set to 0 to disable MySQL cache]
	define('MYSQL_SERVER', '');
	define('MYSQL_USERNAME', '');
	define('MYSQL_PASSWORD', '');
	define('MYSQL_DATABASE', '');
?>