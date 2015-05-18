<?php
/**
 * GuzzleJsonAuth
 *
 * Licensed under the MIT license.
 * For full copyright and license information, please see the LICENSE.txt.
 *
 * @copyright Marc Würth
 * @license http://www.opensource.org/licenses/mit-license.php MIT License
 * @author Marc Würth <ravage@bluewin.ch>
 */

if (defined('GJAUTH_VERSION')) {
    // Do not load this more than once
    return 1;
}

define('GJAUTH_VERSION', '0.1.0');

// Registration of the extension credits, see Special:Version.
$wgExtensionCredits['other'][] = array(
    'path' => __FILE__,
    'name' => 'Guzzle JSON Auth',
    'version' => GJAUTH_VERSION,
    'author' => array(
        '[https://github.com/ravage84/ Marc Würth]',
        '[https://github.com/ravage84/mediawiki-guzzle-json-auth/graphs/contributors Other contributors]'
    ),
    'url' => 'https://github.com/ravage84/mediawiki-guzzle-json-auth/',
    'descriptionmsg' => 'Authenticate with a JSON endpoint using a configurable Guzzle client',
    'license-name'   => 'MIT',
);
