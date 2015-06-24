# SSL-Badge
PHP web application that displays SSL Labs grades as a badge.  Cached assessment reports will be used when available (max age 24 hours).  Schedule the cron command to run daily to prevent it from showing "Testing".

**Demo:** https://timwells.net/ssl_badge/ &nbsp;&nbsp;(Use **timwells.net** as the domain)

### Badges (90x30)

![A+](https://timwells.net/ssl_badge/images/aplus.png)
![A](https://timwells.net/ssl_badge/images/a.png)
![A-](https://timwells.net/ssl_badge/images/aminus.png)
![B](https://timwells.net/ssl_badge/images/b.png)
![C](https://timwells.net/ssl_badge/images/c.png)
![D](https://timwells.net/ssl_badge/images/d.png)

![F](https://timwells.net/ssl_badge/images/f.png)
![M](https://timwells.net/ssl_badge/images/m.png)
![T](https://timwells.net/ssl_badge/images/t.png)
![Testing](https://timwells.net/ssl_badge/images/calculating.png)
![Error](https://timwells.net/ssl_badge/images/err.png)

### Small Badges (80x15)

![A+](https://timwells.net/ssl_badge/images/sm/aplus.png)
![A](https://timwells.net/ssl_badge/images/sm/a.png)
![A-](https://timwells.net/ssl_badge/images/sm/aminus.png)
![B](https://timwells.net/ssl_badge/images/sm/b.png)
![C](https://timwells.net/ssl_badge/images/sm/c.png)
![D](https://timwells.net/ssl_badge/images/sm/d.png)

![F](https://timwells.net/ssl_badge/images/sm/f.png)
![M](https://timwells.net/ssl_badge/images/sm/m.png)
![T](https://timwells.net/ssl_badge/images/sm/t.png)
![Testing](https://timwells.net/ssl_badge/images/sm/calculating.png)
![Error](https://timwells.net/ssl_badge/images/sm/err.png)

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

The badge images are stored as SVG files.  The php script converts the SVG files into PNG files using ImageMagick and outputs the PNG file over the http stream. 

If ImageMagick is not installed, the script will use the PNG image (if it exists).  Even if ImageMagick is installed, the script can work with the PNGimages instead of SVG images.  The script will auto detect which image file is installed.  If the PNG file does not exist and ImageMagick is not installed, then it will just use the SVG image.

### Dependencies
* **ImageMagick** is recommended to be installed on the server
* **PHP-SSLLabs-API Libarary** from [github.com/bjoernr-de/php-ssllabs-api](https://github.com/bjoernr-de/php-ssllabs-api) (included as sslLabsApi.php)


### SSL Labs API

This script uses the SSL Labs API (provided free of charge by Qualys SSL Labs).

[SSL Labs API Documentation](https://github.com/ssllabs/ssllabs-scan/blob/master/ssllabs-api-docs.md)

[SSL Labs API Terms and conditions](https://www.ssllabs.com/about/terms.html)
