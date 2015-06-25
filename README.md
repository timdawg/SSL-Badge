# SSL-Badge
PHP web application that displays SSL Labs grades as a badge.

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

If the **domain** parameter is not specified, a form will allow the user to generate the HTML code (if enabled).  This also generates a cron command to update the cached result using wget.

### Configuration Constants

These config variables can be found in the config.php script.  If a constant is not defined, the default value will be used.

* **ALLOW_ANY** - Specifies if the script can be used for any website (true) or restricted to the domains in ALLOWED_DOMAINS [Boolean]
* **GENERATE_FORM** - Specifies if the HTML code generator form should be allowed [Boolean]
* **ALLOWED_DOMAINS** - Allowed domains (if ALLOW_ANY = false) [String Array]
* **IMG_PATH** - Path to large SVG images [String]
* **IMG_PATH_SM** - Path to small SVG images [String]
* **REPORT_CACHE_AGE** - Maximum cached report age (in hours) [Integer]
* **BROWSER_CACHE_AGE** - How long the browser should cache the image / 0 disables browser cache (seconds) [Integer]
* **APC_CACHE_AGE** - How long the APC should cache the results / 0 disables APC cache (seconds) [Integer]

### Caching

Cached assessment reports from the API server will be used when available (default max age 24 hours).  The APC cache (if available) will also cache the grades to reduce API calls (default for 24 hours).  The vistor's browser will also cache the image (default for 24 hours).  The "Testing" and "Err" badges will not be cached in the APC or browser cache.  When the **new=true** URL parameter is specified, it forces the API server to re-test the website and clears the result from the APC cache.  I recommend scheduling a cron job to run the generated cron command daily, to prevent visitors from seeing the "Testing" badge.

### Badge Images

The badge images are stored as SVG files.  The php script converts the SVG files into PNG files using ImageMagick and outputs the PNG file over the http stream. 

If ImageMagick is not installed, the script will use the PNG image (if it exists).  Even if ImageMagick is installed, the script can work with the PNG images instead of SVG images.  The script will auto detect which image file is installed.  If the PNG file does not exist and ImageMagick is not installed, then it will just use the SVG image.

### Dependencies
* **PHP-SSLLabs-API Libarary** from [github.com/bjoernr-de/php-ssllabs-api](https://github.com/bjoernr-de/php-ssllabs-api) (included as sslLabsApi.php)
* **ImageMagick** is recommended to be installed on the server (not required)


### SSL Labs API

This script uses the SSL Labs API (provided free of charge by Qualys SSL Labs).

This project is not affiliated with or officially supported by SSL Labs.

[SSL Labs API Documentation](https://github.com/ssllabs/ssllabs-scan/blob/master/ssllabs-api-docs.md)

[SSL Labs API Terms and conditions](https://www.ssllabs.com/about/terms.html)
