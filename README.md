# mediawiki-guzzle-json-auth
Authenticate your MediaWiki with a JSON endpoint using a configurable Guzzle client

[![Latest Version](https://img.shields.io/github/release/ravage84/mediawiki-guzzle-json-auth.svg?style=flat-square)](https://github.com/ravage84/mediawiki-guzzle-json-auth/releases)
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE.md)
[![Build Status](https://img.shields.io/travis/ravage84/mediawiki-guzzle-json-auth/master.svg?style=flat-square)](https://travis-ci.org/ravage84/mediawiki-guzzle-json-auth)
[![Coverage Status](https://img.shields.io/scrutinizer/coverage/g/ravage84/mediawiki-guzzle-json-auth.svg?style=flat-square)](https://scrutinizer-ci.com/g/ravage84/mediawiki-guzzle-json-auth/code-structure)
[![Quality Score](https://img.shields.io/scrutinizer/g/ravage84/mediawiki-guzzle-json-auth.svg?style=flat-square)](https://scrutinizer-ci.com/g/ravage84/mediawiki-guzzle-json-auth)
[![Total Downloads](https://img.shields.io/packagist/dt/mediawiki/guzzle-json-auth.svg?style=flat-square)](https://packagist.org/packages/mediawiki/guzzle-json-auth)

## Install

### Requirements

- PHP >= 5.4
- Composer
- MediaWiki 1.22

### Via Composer

``` bash
$ composer require mediawiki/guzzle-json-auth
```

## Usage

To Setup the authentication, add the following lines to your
LocalSettings.php and change the values to your needs:

```` php
# Setup GuzzleJsonAuth
$wgGuzzleJsonAuthUrl = 'http://yourapplication.com/your_json_endpoint';

$wgGuzzleJsonRequestBaseKey = 'User';
$wgGuzzleJsonRequestUsernameKey = 'username';
$wgGuzzleJsonRequestPasswordKey = 'password';

$wgGuzzleJsonResponseBaseKey = 'user';
$wgGuzzleJsonResponseUsernameKey = 'username';
$wgGuzzleJsonResponseRealNameKey = 'real_name';
$wgGuzzleJsonResponseEmailKey = 'email';

require_once("$IP/extensions/GuzzleJsonAuth/src/GuzzleJsonAuth.php");
use \MediaWiki\GuzzleJsonAuth\GuzzleJsonAuth;
$wgAuth = new GuzzleJsonAuth();
````

Authenticated users will be created in your Wikis database,
but without password.

The authentication endpoint needs to return at least tne
username, real name and email of the authenticated user as result.
If the authentication failed, it must not return the username,
otherwise it the user will be logged in nonetheless.

This extension will fall back to local authentication,
if the user could not be authenticated externally.

## Change log

Please see [CHANGELOG.md](CHANGELOG.md) for more information what has changed recently.

## Testing

``` bash
$ phpunit
```

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Security

If you discover any security related issues, please email ravage@bluewin.ch instead of using the issue tracker.

## Credits

- [Marc Würth](https://github.com/ravage84)
- [All Contributors](https://github.com/ravage84/mediawiki-guzzle-json-auth/graphs/contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.txt) for more information.

