<?php	
	// Config Include
	if (file_exists('config.php')) {
		include('config.php');
	}
	
	// Default Config Constants (if not defined in config.php)
	if(!defined('ALLOW_ANY'))
		define('ALLOW_ANY', true);
	if(!defined('GENERATE_FORM'))
		define('GENERATE_FORM', true);
	if(defined('ALLOWED_DOMAINS'))
		$allowed_domains = unserialize(ALLOWED_DOMAINS);
	else
		$allowed_domains = array();
	if(!defined('IMG_PATH'))
		define('IMG_PATH', 'images/');
	if(!defined('IMG_PATH_SM'))
		define('IMG_PATH_SM', 'images/sm/');
	if(!defined('REPORT_CACHE_AGE'))
		define('REPORT_CACHE_AGE', 24);
	if(!defined('BROWSER_CACHE_AGE'))
		define('BROWSER_CACHE_AGE', 86400);
	if(!defined('APC_CACHE_AGE'))
		define('APC_CACHE_AGE', 86400);
	if(!defined('MYSQL_CACHE_AGE'))
		define('MYSQL_CACHE_AGE', 0);
	if(!defined('MYSQL_SERVER'))
		define('MYSQL_SERVER', '');
	if(!defined('MYSQL_USERNAME'))
		define('MYSQL_USERNAME', '');
	if(!defined('MYSQL_PASSWORD'))
		define('MYSQL_PASSWORD', '');
	if(!defined('MYSQL_DATABASE'))
		define('MYSQL_DATABASE', '');
		
	// Constants
	define('PNG_HEADER', "\211PNG\r\n\032\n");
	define('SVG_HEADER', "<svg");
	define('APC_PREFIX', 'ssl_badge_');	
	
	// Database Include
	if (MYSQL_CACHE_AGE > 0) {
		$sql_con = mysql_connect(MYSQL_SERVER, MYSQL_USERNAME, MYSQL_PASSWORD) or die('Could not connect: ' . mysql_error());
		mysql_select_db(MYSQL_DATABASE) or die('Could not select database');
		
		$db_init = mysql_query('SELECT 1 from `ssl_badge_cache` LIMIT 1');
		
		if($db_init == false)
		{
			$query = 'CREATE TABLE `ssl_badge_cache` (`id` int(11) NOT NULL auto_increment, `domain` varchar(255) NOT NULL, `grade` varchar(2) NOT NULL,';
			$query .= '`expires` datetime NOT NULL, PRIMARY KEY  (`id`)) ENGINE=MyISAM  DEFAULT CHARSET=utf8;';
			mysql_query($query);
		}
	}	
	
	// Parameters
	$test_domain = $_GET['domain'];
	// Image Path (default to large badges, if &sm=true query string, then use the small badges)
	$image_path = IMG_PATH;
	if($_GET['sm']=='true')
		$image_path = IMG_PATH_SM;
	// As plain text instead of image (default=false)
	$as_text = false;
	if($_GET['text']=='true')
		$as_text = true;
	// Start new (ignore cache / default uses cache)
	$start_new = false;
	$from_cache = true;
	$rpt_cache_age = REPORT_CACHE_AGE;
	if($_GET['new']=='true')
	{
		$start_new = true;
		$from_cache = false;
		$rpt_cache_age = NULL;
	}		
	
	// Init Variables
	$apc_cached_grade = false;	
	$mysql_cached_grade = false;
	$mysql_cached_expires = NULL;

	// API
	require_once 'sslLabsApi.php';
	$api = new sslLabsApi(true);
	
	// (if ALLOW_ANY is false, the test domain must be in allowed domains)
	if($test_domain && $test_domain != '' && (ALLOW_ANY || in_array($test_domain, $allowed_domains)))
	{
		if(!cache_read($test_domain))
			get_report();
	}
	// Generate HTML code response
	elseif(GENERATE_FORM && $_POST['action']=='generate' && (ALLOW_ANY || in_array($_POST['domain'], $allowed_domains)))
	{	
		$test_domain = $_POST['domain'];
		$sm = false;		
		if($_POST['sm']=='true')
			$sm = true;
		?>
		<html>
		<head>
			<title>SSL Badge</title>
		</head>
		<body>
			<h2 align="center">SSL Badge</h3>
			<p align="center"><?php echo $test_domain; ?></p>
			<p align="center">HTML Code:<br /><textarea rows="6" cols="80" readonly><?php	
				echo htmlspecialchars(badge_html($test_domain, $sm));
			?></textarea></p>
			<p align="center">Daily Cron Command (clear cache and start new test):<br /><textarea rows="4" cols="80" readonly><?php	
				echo htmlspecialchars('wget -O - -q "');
				echo htmlspecialchars(badge_url($test_domain, false, true, true, true));
				echo htmlspecialchars('"');
			?></textarea></p>
			<p align="center">Daily Cron Command 2 (run 5 minutes later to get test result):<br /><textarea rows="4" cols="80" readonly><?php	
				echo htmlspecialchars('wget -O - -q "');
				echo htmlspecialchars(badge_url($test_domain, false, true, false, true));
				echo htmlspecialchars('"');
			?></textarea></p>
			<p align="center"><?php	
				echo badge_html($test_domain, $sm);	
			?></p>
			<p align="center">&nbsp;</p>
			<p align="center"><button onClick="window.history.back()">&lt; Back</button></p>
			<p align="center">&nbsp;</p>
			<p align="center"><?php echo info_messages() ?></p>
		</body>
		</html>
		<?php
	}
	// Generate HTML code Form
	elseif(GENERATE_FORM && $_POST['action']!='generate')
	{
		?>
		<html>
		<head>
			<title>SSL Badge</title>
		</head>
		<body>
			<form action="" method="post">
			<input type="hidden" name="action" value="generate">
			<h2 align="center">SSL Badge</h3>
			<p align="center">Enter domain:<br />
			<input type="text" name="domain" value=""></p>
			<p align="center"><input type="checkbox" name="sm" id="chk_sm" value="true"><label for="chk_sm">Small Image</label></p>
			<p align="center"><input type="submit" value="Generate"></p>
			</form>
			<p align="center">&nbsp;</p>
			<p align="center">Cached assessment reports will be used when available (max age <?php echo REPORT_CACHE_AGE; ?> hours).<br />
			Schedule the cron command to run daily to prevent it from showing "Testing".</p>
			<p align="center">&nbsp;</p>
			<p align="center"><b>Badges:</b><br /><?php
				echo inline_image(IMG_PATH . 'aplus.svg', 'A+') . "&nbsp;&nbsp;";
				echo inline_image(IMG_PATH . 'a.svg', 'A') . "&nbsp;&nbsp;";
				echo inline_image(IMG_PATH . 'aminus.svg', 'A-') . "&nbsp;&nbsp;";
				echo inline_image(IMG_PATH . 'b.svg', 'B') . "&nbsp;&nbsp;";
				echo inline_image(IMG_PATH . 'c.svg', 'C') . "&nbsp;&nbsp;";
				echo inline_image(IMG_PATH . 'd.svg', 'D') . "&nbsp;&nbsp;";
				echo inline_image(IMG_PATH . 'f.svg', 'F') . "&nbsp;&nbsp;";
				echo inline_image(IMG_PATH . 'm.svg', 'M (Certificate not valid for domain name)') . "&nbsp;&nbsp;";
				echo inline_image(IMG_PATH . 't.svg', 'T (Server certificate is not trusted)') . "&nbsp;&nbsp;";
				echo inline_image(IMG_PATH . 'calculating.svg', 'Testing') . "&nbsp;&nbsp;";
				echo inline_image(IMG_PATH . 'err.svg', 'Error') . "&nbsp;&nbsp;";
			?></p>
			<p align="center">&nbsp;</p>
			<p align="center"><b>Small Badges:</b><br /><?php
				echo inline_image(IMG_PATH_SM . 'aplus.svg', 'A+') . "&nbsp;&nbsp;";
				echo inline_image(IMG_PATH_SM . 'a.svg', 'A') . "&nbsp;&nbsp;";
				echo inline_image(IMG_PATH_SM . 'aminus.svg', 'A-') . "&nbsp;&nbsp;";
				echo inline_image(IMG_PATH_SM . 'b.svg', 'B') . "&nbsp;&nbsp;";
				echo inline_image(IMG_PATH_SM . 'c.svg', 'C') . "&nbsp;&nbsp;";
				echo inline_image(IMG_PATH_SM . 'd.svg', 'D') . "&nbsp;&nbsp;";
				echo inline_image(IMG_PATH_SM . 'f.svg', 'F') . "&nbsp;&nbsp;";
				echo inline_image(IMG_PATH_SM . 'm.svg', 'M (Certificate not valid for domain name)') . "&nbsp;&nbsp;";
				echo inline_image(IMG_PATH_SM . 't.svg', 'T (Server certificate is not trusted)') . "&nbsp;&nbsp;";
				echo inline_image(IMG_PATH_SM . 'calculating.svg', 'Testing') . "&nbsp;&nbsp;";
				echo inline_image(IMG_PATH_SM . 'err.svg', 'Error') . "&nbsp;&nbsp;";
			?></p>
			<p align="center">&nbsp;</p>
			<p align="center"><?php echo info_messages(); ?></p>
		</body>
		</html>
		<?php
	}
	// Unauthorized Access
	else
	{
		if(version_compare(phpversion(), '5.4.0', '>='))
			http_response_code(401);
		else
			header('HTTP/1.1 401 Unauthorized');
	?>
        <html>
        <head>
            <title>Access Denied</title>
        </head>
        <body>
            <h2>Access Denied</h2>
            <p><?php 
				if(!GENERATE_FORM && !ALLOW_ANY) {
					echo 'This script is restriced to defined domain names and the generate form is disabled!';
				} elseif(!ALLOW_ANY) {
					echo 'This script is restriced to defined domain names!';
				} elseif(!GENERATE_FORM) { 
	            	echo 'The generate form is disabled for this script!';
				} ?></p>
            <?php if(GENERATE_FORM && $_POST['action']=='generate') { ?>
			<p><button onClick="window.history.back()">&lt; Back</button></p>
            <?php } ?>
        </body>
        </html>
	<?php		
	}
	if(MYSQL_CACHE_AGE > 0)
	{
		// Closing connection
		mysql_close($sql_con);
	}

	function info_messages($include_version = true, $include_assessments = false)
	{
		global $api;
		$text = '';
		$info = $api->fetchApiInfo();
		$messages = $info->messages;
		for($i = 0; $i < count($messages); $i++) {
			//$text .= htmlspecialchars($messages[$i]) . '<br />';
			$text .= link_urls($messages[$i]) . '<br />';
		}
		$text .= 'This project is not affiliated with or officially supported by SSL Labs.<br />';
		if($include_version)
		{
			$text .= '<br />SSL Labs Engine v' . $info->engineVersion;
			$text .= '<br />Criteria Version ' . $info->criteriaVersion;
		}
		if($include_assessments)
		{
			$text .= '<br /><br />' . $info->currentAssessments . ' assessments (max ' . $info->maxAssessments . ')';
		}
		return $text;
	}
	
	function cache_read($in_domain)
	{
		global $apc_cached_grade, $mysql_cached_grade, $mysql_cached_expires;
		global $from_cache;
		
		// Check that MYSQL_CACHE_AGE > 0
		if(MYSQL_CACHE_AGE > 0)
		{
			// Don't use cached report if user requested a fresh report
			if($from_cache)
			{
				$query = 'SELECT * FROM `ssl_badge_cache` WHERE `expires` > NOW() AND `domain` = "' . $in_domain . '" ORDER BY `expires` LIMIT 0, 1';
				$result = mysql_query($query) or die('Query failed: ' . mysql_error());
				
				if (mysql_num_rows($result) > 0) 
				{
					$row = mysql_fetch_array($result, MYSQL_ASSOC);
					
					$grade = $row['grade'];
					$mysql_cached_grade = true;
					$mysql_cached_expires = strtotime($row['expires']);
					output_grade($grade);
					return true;
				}
				mysql_free_result($result);
			}
			else
			{
				// Clear entry from MySQL cache if user requested fresh report
				$query = 'DELETE FROM `ssl_badge_cache` WHERE `domain` = "' . $in_domain . '"';
				mysql_query($query);
			}
		}
		// Check that APC_CACHE_AGE > 0 and that APC is enabled
		elseif(APC_CACHE_AGE > 0 && extension_loaded('apc') && ini_get('apc.enabled'))
		{
			if(apc_exists(APC_PREFIX & $in_domain))
			{
				// Don't use cached report if user requested a fresh report
				if($from_cache)
				{
					 $grade = apc_fetch(APC_PREFIX & $in_domain, $success);
					 if($success)
					 {
						 $apc_cached_grade = true;
						 output_grade($grade);
						 return true;
					 }
				}
				else
				{
					// Clear entry from cache if user requested fresh report
					apc_delete($in_domain);
				}
			}
		}
		
		return false;
	}
	
	// Store the grade in the APC cache
	function cache_store($in_domain, $in_grade)
	{
		// Check that MYSQL_CACHE_AGE > 0
		if(MYSQL_CACHE_AGE > 0)
		{
			// Clear any entries from MySQL cache
			$query = 'DELETE FROM `ssl_badge_cache` WHERE `domain` = "' . $in_domain . '"';
			mysql_query($query);
			// Insert record
			$query = 'INSERT INTO `ssl_badge_cache` (`domain`, `grade`, `expires`) VALUES ("'. $in_domain . '", "' . $in_grade . '", DATE_ADD(NOW(), INTERVAL ' . MYSQL_CACHE_AGE . ' SECOND));';
			mysql_query($query);
		}
		// Check that APC_CACHE_AGE > 0 and that APC is enabled
		elseif(APC_CACHE_AGE > 0 && extension_loaded('apc') && ini_get('apc.enabled'))
		{
			apc_store(APC_PREFIX & $in_domain, $in_grade, APC_CACHE_AGE);
		}
	}
	
	// Get Report from SSL Labs API
	function get_report()
	{
		global $api;
		global $start_new;
		global $from_cache;
		global $rpt_cache_age;
		global $test_domain;
		
		// Get host information
		$ssl_host = $api->fetchHostInformation($test_domain, false, $start_new, $from_cache, $rpt_cache_age);
		
		// Process status
		if($ssl_host->status == 'READY') 
		{
			$ssl_endpoints = $ssl_host->endpoints;
		
			// Get Grade from first endpoint
			if($ssl_endpoints[0]->grade == NULL && $ssl_endpoints[0]->statusMessage == 'Certificate not valid for domain name')
			{
				cache_store($test_domain, 'M');
				output_grade('M');
			}
			else
			{
				cache_store($test_domain, $ssl_endpoints[0]->grade);
				output_grade($ssl_endpoints[0]->grade);
			}
		} 
		else 
		{
			output_status($ssl_host->status);
		}
	}
	
	// Output status as image or text
	function output_status($in_status)
	{
		global $image_path;
		global $as_text;
		
		switch($in_status) {
			case 'DNS':		// Initial DNS Lookup
			case 'IN_PROGRESS':		// Processing Test
				if($as_text)
					output_text('Testing...');
				else
					output_image($image_path . 'calculating.svg', false);
				break;
			default: // ERROR (default to this if something else is returned)
				if($as_text)
					output_text($in_status);
				else				
					output_image($image_path . 'err.svg', false);
		}		
	}
	
	// Output grade as image or text
	function output_grade($in_grade)
	{
		global $image_path;
		global $as_text;
		
		switch($in_grade) {
			case 'A+':
				if($as_text)
					output_text($in_grade);
				else
					output_image($image_path . 'aplus.svg');
				break;
			case 'A':
				if($as_text)
					output_text($in_grade);
				else
					output_image($image_path . 'a.svg');
				break;
			case 'A-':
				if($as_text)
					output_text($in_grade);
				else
					output_image($image_path . 'aminus.svg');
				break;
			case 'B':
				if($as_text)
					output_text($in_grade);
				else
					output_image($image_path . 'b.svg');
				break;
			case 'C':
				if($as_text)
					output_text($in_grade);
				else
					output_image($image_path . 'c.svg');
				break;
			case 'D':
				if($as_text)
					output_text($in_grade);
				else
					output_image($image_path . 'd.svg');
				break;
			case 'F':
				if($as_text)
					output_text($in_grade);
				else
					output_image($image_path . 'f.svg');
				break;
			case 'M':	// Domain does not match cert
				if($as_text)
					output_text($in_grade);
				else
					output_image($image_path . 'm.svg');
				break;
			case 'T':	// Trust Issues
				if($as_text)
					output_text($in_grade);
				else
					output_image($image_path . 't.svg');
				break;
			default: // ERROR (default to this if something else is returned)
				if($as_text)
					output_text($in_grade);
				else
					output_image($image_path . 'err.svg', false);
		}		
	}
	
	// Get URL for badge
	function badge_url($test_domain, $sm = false, $text = false, $new = false, $force_host = false)
	{
		$url = '';
		if($_SERVER['SERVER_NAME']!=$test_domain || $force_host)
		{
			if($_SERVER['HTTPS']!='' || $_SERVER["HTTP_X_FORWARDED_PROTO"] == 'https')
				$url .= 'https';
			else
				$url .= 'http';
			$url .= '://' . $_SERVER['SERVER_NAME'];
		}
		$url .= '/ssl_badge/?domain=' . $test_domain;	
		if($sm == true)
			$url .= '&sm=true';
		if($text == true)
			$url .= '&text=true';
		if($new == true)
			$url .= '&new=true';
		return $url;	
	}
	
	// Generated HTML code for badge
	function badge_html($test_domain, $sm)
	{
		$html = '<a href="https://www.ssllabs.com/ssltest/analyze.html?d=' . $test_domain . '&hideResults=on" target="_blank">' . "\r\n";
		$html .= '<img title="SSL Grade" src="';
		$html .= badge_url($test_domain, $sm);
		$html .= '" alt="SSL Grade" border="0">' . "\r\n" . '</a>';
		return $html;
	}
	
	// Convert SVG Image to PNG
	function convert_svg_png($img_file)
	{
		// Check for Imagick
		if(file_exists($img_file) && class_exists("Imagick"))
		{
			// Convert SVG Image to PNG using Imagick
			$image = new Imagick();
			$svg_file = file_get_contents($img_file);
			$svg_file = '<?xml version="1.0" encoding="UTF-8" standalone="no"?>'.$svg_file;
			$image->setBackgroundColor(new ImagickPixel('transparent'));
			$image->readImageBlob($svg_file);
			$image->setImageFormat("png32");
			return $image;
		}
		// Imagick not installed
		elseif(file_exists(str_ireplace('.svg', '.png', $img_file)))
		{
			// Return stored PNG image instead
			$png_file = file_get_contents(str_ireplace('.svg', '.png', $img_file));
			return $png_file;			
		}
		// Imagick not installed and PNG file does not exist
		elseif(file_exists($img_file))
		{
			// Return SVG image instead
			$svg_file = file_get_contents($img_file);
			return $svg_file;
		}
		else
		{
			return NULL;
		}
	}
	
	// Output image as inline html (base64)
	function inline_image($img_file, $img_alt = "")
	{
		$image = convert_svg_png($img_file);
		if(substr($image, 0, strlen(PNG_HEADER))==PNG_HEADER)
		{
			$html = '<img src="data:image/png;base64,';
			$html .= base64_encode($image);
			$html .= '" alt="' . $img_alt . '" title="' . $img_alt . '" />';
		}
		elseif(substr($image, 0, strlen(SVG_HEADER))==SVG_HEADER)
		{
			$html = $image;
		}	
		return $html;
	}
	
	// Output HTTP Cache Headers
	function cache_headers($use_cache = true)
	{		
		global $apc_cached_grade, $mysql_cached_grade, $mysql_cached_expires;
		
		if(BROWSER_CACHE_AGE > 0 && $use_cache==true) {
			// Cache badge in browswer
			header('Cache-Control: public, max-age=' . BROWSER_CACHE_AGE);
		} else {
			// Do not cache badge
			header('Cache-Control: no-cache, no-store');	
			header('Pragma: no-cache');			
		}
		// Extra headers with cache info
		if($mysql_cached_grade) {
			header('X-Cached-Result: true');
			header('X-Cached-Source: MySQL');
			header('X-Cached-Expires: ' . date('Y-m-d H:i:s', $mysql_cached_expires));	
		}
		elseif($apc_cached_grade) {
			header('X-Cached-Result: true');	
			header('X-Cached-Source: APC');
		}
		else {
			header('X-Cached-Result: false');	
		}
	}
	
	
	// Output text
	function output_text($text, $cache = true)
	{
		ob_clean();
		cache_headers($cache);
		header('Content-Disposition: inline; filename="ssl_badge.txt"');
		header('Content-Type: text/plain');			
		
		echo $text . "\r\n";
		exit;
	}
		
	// Output image over http stream
	function output_image($img_file, $cache = true)
	{		
		$image = convert_svg_png($img_file);
		ob_clean();
		cache_headers($cache);
		if(substr($image, 0, strlen(PNG_HEADER))==PNG_HEADER)
		{
			header('Content-Disposition: inline; filename="ssl_badge.png"');
			header('Content-Type: image/png');		
		}
		elseif(substr($image, 0, strlen(SVG_HEADER))==SVG_HEADER)
		{
			header('Content-Disposition: inline; filename="ssl_badge.svg"');
			header('Content-Type: image/svg+xml');		
		}			
		echo $image;
		exit;
	}

	// Add <a> links to detected URLs in a string
	function link_urls($s) {
		return preg_replace('/https?:\/\/[\w\-\.!~#?&=+\*\'"(),\/]+/','<a href="$0" target="_blank">$0</a>',$s);
	}
	
?>
