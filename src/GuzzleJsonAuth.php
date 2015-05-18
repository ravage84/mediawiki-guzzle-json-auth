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

namespace MediaWiki\GuzzleJsonAuth;

use \GuzzleHttp\Client;
use \GuzzleHttp\Message\ResponseInterface;

/**
 * Authenticate with a JSON endpoint using a configurable Guzzle client
 *
 * To Setup the authentication, add the following lines to your
 * LocalSettings.php and change the values to your needs:
 *
 * # Setup GuzzleJsonAuth
 * $wgGuzzleJsonAuthUrl = 'http://yourapplication.com/your_json_endpoint';
 *
 * $wgGuzzleJsonRequestBaseKey = 'User';
 * $wgGuzzleJsonRequestUsernameKey = 'username';
 * $wgGuzzleJsonRequestPasswordKey = 'password';
 *
 * $wgGuzzleJsonResponseBaseKey = 'user';
 * $wgGuzzleJsonResponseUsernameKey = 'username';
 * $wgGuzzleJsonResponseRealNameKey = 'real_name';
 * $wgGuzzleJsonResponseEmailKey = 'email';
 *
 * // Load and initialize the GuzzleJsonAuth extension
 * require_once("$IP/extensions/GuzzleJsonAuth/GuzzleJsonAuth.php");
 * $wgAuth = new \MediaWiki\GuzzleJsonAuth\GuzzleJsonAuth();
 *
 * Authenticated users will be created in your Wikis database,
 * but without password.
 *
 * The authentication endpoint needs to return at least tne
 * username, real name and email of the authenticated user as result.
 * If the authentication failed, it must not return the username,
 * otherwise it the user will be logged in nonetheless.
 *
 * This extension will fall back to local authentication,
 * if the user could not be authenticated externally.
 *
 * @link http://www.mediawiki.org/wiki/Extension:Typo3Auth Inspired by
 * @todo Setup default values for the config settings where sensible
 */
class GuzzleJsonAuth extends \AuthPlugin
{
    /**
     * Contains the data of the authenticated user
     *
     * @var null|array
     */
    protected $_user = null;

    /**
     * Simply state the user in question exists without checking
     *
     * {@inheritDoc}
     */
    public function userExists($username)
    {
        return true;
    }

    /**
     * Authenticate with a JSON endpoint using a configurable Guzzle client
     *
     * {@inheritDoc}
     * @SuppressWarnings(PHPMD.Superglobals)
     */
    public function authenticate($username, $password)
    {
        $authUrl = $GLOBALS['wgGuzzleJsonAuthUrl'];

        // Prepare request
        $authData = $this->_setAuthData($username, $password);
        $options = array(
            'json' => $authData,
            'headers' => array('content-type' => 'application/json'),
        );

        // Send request
        $client = $this->_getGuzzleClient();
        $response = $client->post($authUrl, $options);

        // Extract user data from response
        $this->_extractUserData($response);

        // Authentication is successful if a username is returned
        if ($this->_getUserField('username')) {
            return true;
        }

        return false;
    }

    /**
     * Initialize the authenticated user with the data from the external DB
     *
     * {@inheritDoc}
     */
    public function initUser(&$user, $autocreate = false)
    {
        $user->setRealName($this->_getUserField('realName'));
        $user->setEmail($this->_getUserField('email'));
        $user->mEmailAuthenticated = wfTimestampNow();
    }

    /**
     * Update the authenticated user with the data from the external DB
     *
     * {@inheritDoc}
     */
    public function updateUser(&$user)
    {
        $user->setRealName($this->_getUserField('realName'));
        $user->setEmail($this->_getUserField('email'));
        $user->mEmailAuthenticated = wfTimestampNow();
        $user->saveSettings();
        return true;
    }

    /**
     * Auto create a new local account automatically
     * when asked to login a user who doesn't exist
     * locally but does in the external auth database
     *
     * {@inheritDoc}
     */
    public function autoCreate()
    {
        return true;
    }

    /**
     * Do not allow any property changes
     *
     * {@inheritDoc}
     */
    public function allowPropChange($prop = '')
    {
        return false;
    }

    /**
     * Do not allow to change the password
     *
     * {@inheritDoc}
     */
    public function allowPasswordChange()
    {
        return false;
    }

    /**
     * Do not store the password in the local DB
     *
     * {@inheritDoc}
     */
    public function allowSetLocalPassword()
    {
        return false;
    }

    /**
     * Disable setting the password
     *
     * {@inheritDoc}
     */
    public function setPassword($user, $password)
    {
        return false;
    }

    /**
     * Disable updating the external DB
     *
     * {@inheritDoc}
     */
    public function updateExternalDB($user)
    {
        return true;
    }

    /**
     * Disable updating the groups in the external DB
     *
     * {@inheritDoc}
     */
    public function updateExternalDBGroups($user, $addgroups, $delgroups = array())
    {
        return true;
    }

    /**
     * Disable creating accounts in the external DB
     *
     * {@inheritDoc}
     */
    public function canCreateAccounts()
    {
        return false;
    }

    /**
     * Disable adding user to the external DB
     *
     * {@inheritDoc}
     */
    public function addUser($user, $password, $email = '', $realname = '')
    {
        return false;
    }

    /**
     * Allow certain local user accounts, such as the Wiki Admin, to login
     *
     * {@inheritDoc}
     */
    public function strict()
    {
        return false;
    }

    /**
     * Disable strict user auth for all users
     *
     * {@inheritDoc}
     *
     * @todo Consider adding a configurable array of users who are allowed to be authenticated locally, set strict() to true then
     */
    public function strictUserAuth($username)
    {
        return false;
    }

    /**
     * Set the authentication data for the request.
     *
     * @param string $username The username to authenticate.
     * @param string $password The password to authenticate.
     * @return array The set authentication data.
     * @SuppressWarnings(PHPMD.Superglobals)
     */
    protected function _setAuthData($username, $password)
    {
        $requestBaseKey = $GLOBALS['wgGuzzleJsonRequestBaseKey'];
        $usernameKey = $GLOBALS['wgGuzzleJsonRequestUsernameKey'];
        $passwordKey = $GLOBALS['wgGuzzleJsonRequestPasswordKey'];

        $authData = array(
            $usernameKey => $username,
            $passwordKey => $password,
        );
        if ($requestBaseKey) {
            $authData = array($requestBaseKey => $authData);
        }
        return $authData;
    }

    /**
     * Get a Guzzle Client
     *
     * @return Client The Guzzle client.
     */
    protected function _getGuzzleClient()
    {
        return new Client();
    }

    /**
     * Extract the user data from the response
     *
     * @param ResponseInterface $response The Guzzle response object.
     * @return array The extracted user data with 'username', 'realName' & 'email'.
     * @SuppressWarnings(PHPMD.Superglobals)
     */
    protected function _extractUserData(ResponseInterface $response)
    {
        $responseJson = $response->json();

        $ResponseBaseKey = $GLOBALS['wgGuzzleJsonResponseBaseKey'];
        $usernameKey = $GLOBALS['wgGuzzleJsonResponseUsernameKey'];
        $realNameKey = $GLOBALS['wgGuzzleJsonResponseRealNameKey'];
        $emailKey = $GLOBALS['wgGuzzleJsonResponseEmailKey'];

        if (!empty($ResponseBaseKey)) {
            $responseJson = $responseJson[$ResponseBaseKey];
        }

        if (empty($responseJson)) {
            return $this->_populateUserArray();
        }

        return $this->_populateUserArray(
            $responseJson[$usernameKey],
            $responseJson[$realNameKey],
            $responseJson[$emailKey]
        );
    }

    /**
     * Populate the user array
     *
     * @param string $username The username of the user.
     * @param string $realName The real name of the user.
     * @param string $email The email of the user.
     * @return array The populated user array with 'username', 'realName' & 'email'.
     */
    protected function _populateUserArray($username = '', $realName = '', $email = '')
    {
        $this->_user = array(
            'username' => $username,
            'realName' => $realName,
            'email' => $email,
        );

        return $this->_user;
    }

    /**
     * Get a given user field
     *
     * @param string $fieldName The name of the field.
     * @return string The requested field.
     * @throws \BadMethodCallException If an invalid field is requested.
     */
    protected function _getUserField($fieldName)
    {
        if (!isset($this->_user[$fieldName])) {
            throw new \BadMethodCallException('Invalid field name ' . $fieldName);
        }
        return $this->_user[$fieldName];
    }
}
