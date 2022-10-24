# inotes-server
A simple server for [inotes](https://github.com/ArnNied/inotes-client) made with [CodeIgniter 4](https://codeigniter.com/).

## Step

1. Migrate database with:
```
php spark migrate
```
2. Create a `.env` file on project root with these required variables.
```
PHPMAILER_HOST=             # SMTP host
PHPMAILER_USERNAME=         # SMTP username
PHPMAILER_PASSWORD=         # SMTP password
```
3. Run the server with:
```
php spark serve
```

## Server Requirements

PHP version 7.4 or higher is required, with the following extensions installed:

- [intl](http://php.net/manual/en/intl.requirements.php)
- [libcurl](http://php.net/manual/en/curl.requirements.php) if you plan to use the HTTP\CURLRequest library

Additionally, make sure that the following extensions are enabled in your PHP:

- json (enabled by default - don't turn it off)
- [mbstring](http://php.net/manual/en/mbstring.installation.php)
- [mysqlnd](http://php.net/manual/en/mysqlnd.install.php)
- xml (enabled by default - don't turn it off)
