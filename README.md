# SSL-Badge
PHP web application that displays SSL Labs grades as a badge.  Cached assessment reports will be used when available (max age 24 hours).  Schedule the cron command to run daily to prevent it from showing "Testing".

### Badges (90x30)

![A+](https://timwells.net/ssl_badge/?preview=A%2B)
![A](https://timwells.net/ssl_badge/?preview=A)
![A-](https://timwells.net/ssl_badge/?preview=A-)
![B](https://timwells.net/ssl_badge/?preview=B)
![C](https://timwells.net/ssl_badge/?preview=C)
![D](https://timwells.net/ssl_badge/?preview=D)

![F](https://timwells.net/ssl_badge/?preview=F)
![M](https://timwells.net/ssl_badge/?preview=M)
![T](https://timwells.net/ssl_badge/?preview=T)
![Testing](https://timwells.net/ssl_badge/?preview_status=IN_PROGRESS)
![Error](https://timwells.net/ssl_badge/?preview_status=ERROR)

### Small Badges (80x15)

![A+](https://timwells.net/ssl_badge/?preview=A%2B&sm=true)
![A](https://timwells.net/ssl_badge/?preview=A&sm=true)
![A-](https://timwells.net/ssl_badge/?preview=A-&sm=true)
![B](https://timwells.net/ssl_badge/?preview=B&sm=true)
![C](https://timwells.net/ssl_badge/?preview=C&sm=true)
![D](https://timwells.net/ssl_badge/?preview=D&sm=true)

![F](https://timwells.net/ssl_badge/?preview=F&sm=true)
![M](https://timwells.net/ssl_badge/?preview=M&sm=true)
![T](https://timwells.net/ssl_badge/?preview=T&sm=true)
![Testing](https://timwells.net/ssl_badge/?preview_status=IN_PROGRESS&sm=true)
![Error](https://timwells.net/ssl_badge/?preview_status=ERROR&sm=true)

### URL Parameters

* **domain** - Domain of website to test
* **sm=true** - Use small badges
* **new=true** - Ignore cached assessment results and start a new assessment
* **text=true** - Output grade only as plain text

If the **domain** parameter is not specified, a form will allow the user to generate the HTML code (if enabled).  This also generates a daily cron command to update the cached report using wget.

### Config Variables

These config variables can be found in the config.php script

* **$public** - Specifies if the script can be used for any website (true) or restricted to the domains in $allowed_domains [Boolean]
* **$generate_form** - Specifies if the HTML code generator form should be allowed [Boolean]
* **$allowed_domains** - Allowed domains (if $public = false) [String Array]
* **$img_path** - Path to large SVG images [String]
* **$img_path_sm** - Path to small SVG images [String]
* **$cache_age** - Maximum cached report age (in hours) [Integer]

### Badge Images

The badge images are stored as SVG files.  The php script converts the SVG files into PNG files and outputs the PNG file over the http stream.

### Dependencies
* **ImageMagick** must be installed on the server
* **PHP-SSLLabs-API Libarary** from [github.com/bjoernr-de/php-ssllabs-api](https://github.com/bjoernr-de/php-ssllabs-api) (included as sslLabsApi.php)


### SSL Labs API

This script uses the SSL Labs API (provided free of charge by Qualys SSL Labs).

[SSL Labs API Documentation](https://github.com/ssllabs/ssllabs-scan/blob/master/ssllabs-api-docs.md)

[SSL Labs API Terms and conditions](https://www.ssllabs.com/about/terms.html)
