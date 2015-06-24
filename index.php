<?php	
	// Config Variables
	require_once 'config.php';
	
	// Variables
	define('PNG_HEADER', "\211PNG\r\n\032\n");
	define('SVG_HEADER', "<svg");
	
	// Parameters
	$test_domain = $_GET['domain'];
	// Image Path (default to large badges, if &sm=true query string, then use the small badges)
	$image_path = $img_path;
	if($_GET['sm']=='true')
		$image_path = $img_path_sm;
	// As plain text instead of image (default=false)
	$as_text = false;
	if($_GET['text']=='true')
		$as_text = true;
	// Start new (ignore cache / default uses cache)
	$start_new = false;
	$from_cache = true;
	if($_GET['new']=='true')
	{
		$start_new = true;
		$from_cache = false;
		$cache_age = NULL;
	}		

	// API
	require_once 'sslLabsApi.php';
	$api = new sslLabsApi(true);
	
	// (if public is false, the test domain must be in allowed domains)
	if($test_domain && $test_domain != '' && ($public || in_array($test_domain, $allowed_domains)))
	{
		// Get host information
		$ssl_host = $api->fetchHostInformation($test_domain, false, $start_new, $from_cache, $cache_age);
		
		// Process status
		if($ssl_host->status == 'READY') {
			process_report($ssl_host);
		} else {
			output_status($ssl_host->status);
		}
	}
	// Generate HTML code
	elseif($generate_form && $_POST['action']=='generate' && ($public || in_array($_POST['domain'], $allowed_domains)))
	{	
		$test_domain = $_POST['domain'];
		$sm = false;		
		if($_POST['sm']=='true')
			$sm = true;
		?>
		<html>
		<head>
			<title>SSL Labs Badge</title>
		</head>
		<body>
			<h2 align="center">SSL Labs Badge</h3>
			<p align="center"><?php echo $test_domain; ?></p>
			<p align="center">HTML Code:<br /><textarea rows="6" cols="80" readonly><?php	
				echo htmlspecialchars(badge_html($test_domain, $sm));
			?></textarea></p>
			<p align="center">Daily Cron Command (to update cached report):<br /><textarea rows="4" cols="80" readonly><?php	
				echo htmlspecialchars('wget -O - -q "');
				echo htmlspecialchars(badge_url($test_domain, false, true, true, true));
				echo htmlspecialchars('"');
			?></textarea></p>
			<p align="center"><?php	
				echo badge_html($test_domain, $sm);	
			?></p>
			<p align="center">&nbsp;</p>
			<p align="center"><button onclick="window.history.back()">&lt; Back</button></p>
			<p align="center">&nbsp;</p>
			<p align="center"><?php echo info_messages() ?></p>
		</body>
		</html>
		<?php
	}
	// Generate HTML code Form
	elseif($generate_form && $_POST['action']!='generate')
	{
		?>
		<html>
		<head>
			<title>SSL Labs Badge</title>
		</head>
		<body>
			<form action="" method="post">
			<input type="hidden" name="action" value="generate">
			<h2 align="center">SSL Labs Badge</h3>
			<p align="center">Enter domain:<br />
			<input type="text" name="domain" value=""></p>
			<p align="center"><input type="checkbox" name="sm" id="chk_sm" value="true"><label for="chk_sm">Small Image</label></p>
			<p align="center"><input type="submit" value="Generate"></p>
			</form>
			<p align="center">&nbsp;</p>
			<p align="center">Cached assessment reports will be used when available (max age <?php echo $cache_age; ?> hours).<br />
			Schedule the cron command to run daily to prevent it from showing "Testing".</p>
			<p align="center">&nbsp;</p>
			<p align="center"><b>Badges:</b><br /><?php
				echo inline_image($img_path . 'aplus.svg', 'A+') . "&nbsp;&nbsp;";
				echo inline_image($img_path . 'a.svg', 'A') . "&nbsp;&nbsp;";
				echo inline_image($img_path . 'aminus.svg', 'A-') . "&nbsp;&nbsp;";
				echo inline_image($img_path . 'b.svg', 'B') . "&nbsp;&nbsp;";
				echo inline_image($img_path . 'c.svg', 'C') . "&nbsp;&nbsp;";
				echo inline_image($img_path . 'd.svg', 'D') . "&nbsp;&nbsp;";
				echo inline_image($img_path . 'f.svg', 'F') . "&nbsp;&nbsp;";
				echo inline_image($img_path . 'm.svg', 'M (Certificate not valid for domain name)') . "&nbsp;&nbsp;";
				echo inline_image($img_path . 't.svg', 'T (Server certificate is not trusted)') . "&nbsp;&nbsp;";
				echo inline_image($img_path . 'calculating.svg', 'Testing') . "&nbsp;&nbsp;";
				echo inline_image($img_path . 'err.svg', 'Error') . "&nbsp;&nbsp;";
			?></p>
			<p align="center">&nbsp;</p>
			<p align="center"><b>Small Badges:</b><br /><?php
				echo inline_image($img_path_sm . 'aplus.svg', 'A+') . "&nbsp;&nbsp;";
				echo inline_image($img_path_sm . 'a.svg', 'A') . "&nbsp;&nbsp;";
				echo inline_image($img_path_sm . 'aminus.svg', 'A-') . "&nbsp;&nbsp;";
				echo inline_image($img_path_sm . 'b.svg', 'B') . "&nbsp;&nbsp;";
				echo inline_image($img_path_sm . 'c.svg', 'C') . "&nbsp;&nbsp;";
				echo inline_image($img_path_sm . 'd.svg', 'D') . "&nbsp;&nbsp;";
				echo inline_image($img_path_sm . 'f.svg', 'F') . "&nbsp;&nbsp;";
				echo inline_image($img_path_sm . 'm.svg', 'M (Certificate not valid for domain name)') . "&nbsp;&nbsp;";
				echo inline_image($img_path_sm . 't.svg', 'T (Server certificate is not trusted)') . "&nbsp;&nbsp;";
				echo inline_image($img_path_sm . 'calculating.svg', 'Testing') . "&nbsp;&nbsp;";
				echo inline_image($img_path_sm . 'err.svg', 'Error') . "&nbsp;&nbsp;";
			?></p>
			<p align="center">&nbsp;</p>
			<p align="center"><?php echo info_messages(); ?></p>
		</body>
		</html>
		<?php
	}
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
				if(!$generate_form && !$public) {
					echo 'This script is restriced to defined domain names and the generate form is disabled!';
				} elseif(!$public) {
					echo 'This script is restriced to defined domain names!';
				} elseif(!$generate_form) { 
	            	echo 'The generate form is disabled for this script!';
				} ?></p>
            <?php if($generate_form && $_POST['action']=='generate') { ?>
			<p><button onclick="window.history.back()">&lt; Back</button></p>
            <?php } ?>
        </body>
        </html>
	<?php		
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
		
	// Process ready report
	function process_report($ssl_host)
	{
		$ssl_endpoints = $ssl_host->endpoints;
		
		// Get Grade from first endpoint
		if($ssl_endpoints[0]->grade == NULL && $ssl_endpoints[0]->statusMessage == 'Certificate not valid for domain name')
			output_grade('M');
		else
			output_grade($ssl_endpoints[0]->grade);
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
			/*case 'READY':	// Report is ready
				process_report($ssl_host);
				break;*/
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
		$html .= '<img title="SSL Labs Grade" src="';
		$html .= badge_url($test_domain, $sm);
		$html .= '" alt="SSL Labs Grade" border="0">' . "\r\n" . '</a>';
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
	
	// Output text
	function output_text($text, $cache = true)
	{
		ob_clean();
		if($cache==true) {
			header('Cache-Control: public, max-age=86400');
		} else {
			header('Cache-Control: no-cache, no-store');	
			header('Pragma: no-cache');			
		}
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
		if($cache==true) {
			header('Cache-Control: public, max-age=86400');
		} else {
			header('Cache-Control: no-cache, no-store');	
			header('Pragma: no-cache');			
		}
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
