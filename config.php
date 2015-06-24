<?php
	// Enable Error Reporting (debug only)
	//ini_set('display_errors',1);
	//error_reporting(E_ALL|E_STRICT);
	
	// Config Variables	
	$public = true;		// true = public (allowed to be used for any website) / false = restricted to the domains in $allowed_domains
	$generate_form = true;		// true = enable HTML code generator form
	$allowed_domains = array();		// String array of allowed domains (if $public = false)
	$img_path = 'images/';		// Path to large SVG images
	$img_path_sm = 'images/sm/';		// Path to small SVG images
	$cache_age = 24;		// max cache report age 
	
?>