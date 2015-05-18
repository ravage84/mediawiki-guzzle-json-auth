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

// Pretend there is a MediaWiki installation
define('MEDIAWIKI', '' );
$GLOBALS['wgVersion'] = '1.22';

// Load the Composer autoloader
require_once dirname(__FILE__) . '/../vendor/autoload.php';

/**
 * A stub function of the mediawiki core function
 *
 * @return string Always '2015-05-12 20 20:10:53'
 */
function wfTimestampNow() {
    return '2015-05-12 20 20:10:53';
}

/**
 * A stub class for the mediawiki core class
 */
class AuthPlugin {}

/**
 * A stub class of the mediawiki core class
 */
class MediaWikiUser {
}
