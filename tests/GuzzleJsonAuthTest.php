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

namespace MediaWiki\GuzzleJsonAuth\Tests;

require dirname(__FILE__) . '/../vendor/autoload.php';

use MediaWiki\GuzzleJsonAuth\GuzzleJsonAuth;

/**
 * Tests for GuzzleJsonAuth
 *
 * @coversDefaultClass MediaWiki\GuzzleJsonAuth\GuzzleJsonAuth
 */
class GuzzleJsonAuthTest extends \PHPUnit_Framework_TestCase {

    /**
     * The object under test
     *
     * @var null|GuzzleJsonAuth
     */
    protected $_guzzleJsonAuth = null;
    /**
     * Setup of the object under test and the global state
     */
    protected function setUp() {
        $GLOBALS['wgGuzzleJsonAuthUrl'] = 'http://127.0.0.1';

        $GLOBALS['wgGuzzleJsonRequestBaseKey'] = 'ReqBaseKey';
        $GLOBALS['wgGuzzleJsonRequestUsernameKey'] = 'ReqUsernameKey';
        $GLOBALS['wgGuzzleJsonRequestPasswordKey'] = 'ReqPasswordKey';

        $GLOBALS['wgGuzzleJsonResponseBaseKey'] = 'RespBaseKey';
        $GLOBALS['wgGuzzleJsonResponseUsernameKey'] = 'RespUsernameKey';
        $GLOBALS['wgGuzzleJsonResponseRealNameKey'] = 'RespRealNameKey';
        $GLOBALS['wgGuzzleJsonResponseEmailKey'] = 'RespEmailKey';

        $this->_guzzleJsonAuth = new GuzzleJsonAuth();
        parent::setUp();
    }

    /**
     * Tests the userExists method
     *
     * @return void
     * @covers ::userExists
     */
    public function testUserExists() {
        $result = $this->_guzzleJsonAuth->userExists('username');
        $this->assertTrue($result);
    }

    /**
     * Tests the authenticate method with a successful authentication
     *
     * @return void
     * @covers ::authenticate
     * @covers ::_setAuthData
     * @covers ::_extractUserData
     * @covers ::_populateUserArray
     * @covers ::_getUserField(
     */
    public function testAuthenticateSuccessful() {
        $username = 'user123';
        $password = 'password456';

        $options = array(
            'json' => array(
                $GLOBALS['wgGuzzleJsonRequestBaseKey'] => array(
                    $GLOBALS['wgGuzzleJsonRequestUsernameKey'] => $username,
                    $GLOBALS['wgGuzzleJsonRequestPasswordKey'] => $password,
                ),
            ),
            'headers' => array('content-type' => 'application/json'),
        );

        $jsonResponse = array(
            $GLOBALS['wgGuzzleJsonResponseBaseKey'] => array(
                $GLOBALS['wgGuzzleJsonResponseUsernameKey'] => 'Username',
                $GLOBALS['wgGuzzleJsonResponseRealNameKey'] => 'Real Name',
                $GLOBALS['wgGuzzleJsonResponseEmailKey'] => 'Email',
            )
        );

        $this->_guzzleJsonAuth = $this->getMock(
            'MediaWiki\GuzzleJsonAuth\GuzzleJsonAuth',
            array('_getGuzzleClient')
        );
        $clientMock = $this->getMock('\GuzzleHttp\ClientInterface');
        $responseMock = $this->getMock('\GuzzleHttp\Message\ResponseInterface');

        $this->_guzzleJsonAuth->expects($this->once())
            ->method('_getGuzzleClient')
            ->will($this->returnValue($clientMock));

        $clientMock->expects($this->once())
            ->method('post')
            ->with(
                $this->equalTo($GLOBALS['wgGuzzleJsonAuthUrl']),
                $this->equalTo($options)
            )->will($this->returnValue($responseMock));

        $responseMock->expects($this->once())
            ->method('json')
            ->will($this->returnValue($jsonResponse));

        $result = $this->_guzzleJsonAuth->authenticate($username, $password);

        $this->assertTrue($result);

        $expectedUserArray = array(
            'username' => 'Username',
            'realName' => 'Real Name',
            'email' => 'Email'
        );
        $this->assertAttributeEquals($expectedUserArray, '_user', $this->_guzzleJsonAuth);
    }

    /**
     * Tests the authenticate method with a failed authentication
     *
     * @return void
     * @covers ::authenticate
     * @covers ::_setAuthData
     * @covers ::_extractUserData
     * @covers ::_populateUserArray
     * @covers ::_getUserField(
     */
    public function testAuthenticateFailed() {
        $username = 'user123';
        $password = 'password456';

        $jsonResponse = array(
            $GLOBALS['wgGuzzleJsonResponseBaseKey'] => array()
        );

        $this->_guzzleJsonAuth = $this->getMock(
            'MediaWiki\GuzzleJsonAuth\GuzzleJsonAuth',
            array('_getGuzzleClient')
        );
        $clientMock = $this->getMock('\GuzzleHttp\ClientInterface');
        $responseMock = $this->getMock('\GuzzleHttp\Message\ResponseInterface');

        $this->_guzzleJsonAuth->expects($this->once())
            ->method('_getGuzzleClient')
            ->will($this->returnValue($clientMock));

        $clientMock->expects($this->once())
            ->method('post')
            ->will($this->returnValue($responseMock));

        $responseMock->expects($this->once())
            ->method('json')
            ->will($this->returnValue($jsonResponse));

        $result = $this->_guzzleJsonAuth->authenticate($username, $password);

        $this->assertFalse($result);

        $expectedUserArray = array(
            'username' => '',
            'realName' => '',
            'email' => ''
        );
        $this->assertAttributeEquals($expectedUserArray, '_user', $this->_guzzleJsonAuth);
    }

    /**
     * Tests the initUser method
     *
     * @return void
     * @covers ::initUser
     */
    public function testInitUser() {
        $realName = 'Hans Mustermann';
        $email = 'hans.mustermann@example.com';
        $this->_guzzleJsonAuth = $this->getMock('MediaWiki\GuzzleJsonAuth\GuzzleJsonAuth', array('_getUserField'));
        $this->_guzzleJsonAuth->expects($this->at(0))
            ->method('_getUserField')
            ->with($this->equalTo('realName'))
            ->will($this->returnValue($realName));
        $this->_guzzleJsonAuth->expects($this->at(1))
            ->method('_getUserField')
            ->with($this->equalTo('email'))
            ->will($this->returnValue($email));

        $user = $this->getMock('MediaWikiUser', array('setRealName', 'setEmail'));
        $user->expects($this->at(0))
            ->method('setRealName')
            ->with($this->equalTo($realName));
        $user->expects($this->at(1))
            ->method('setEmail')
            ->with($this->equalTo($email));

        $result = $this->_guzzleJsonAuth->initUser($user);
        $this->assertNull($result);
        $this->assertAttributeEquals('2015-05-12 20 20:10:53', 'mEmailAuthenticated', $user);
    }

    /**
     * Tests the updateUser method
     *
     * @return void
     * @covers ::updateUser
     */
    public function testUpdateUser() {
        $realName = 'Hans Mustermann';
        $email = 'hans.mustermann@example.com';
        $this->_guzzleJsonAuth = $this->getMock('MediaWiki\GuzzleJsonAuth\GuzzleJsonAuth', array('_getUserField'));
        $this->_guzzleJsonAuth->expects($this->at(0))
            ->method('_getUserField')
            ->with($this->equalTo('realName'))
            ->will($this->returnValue($realName));
        $this->_guzzleJsonAuth->expects($this->at(1))
            ->method('_getUserField')
            ->with($this->equalTo('email'))
            ->will($this->returnValue($email));

        $user = $this->getMock('MediaWikiUser', array('setRealName', 'setEmail', 'saveSettings'));
        $user->expects($this->at(0))
            ->method('setRealName')
            ->with($this->equalTo($realName));
        $user->expects($this->at(1))
            ->method('setEmail')
            ->with($this->equalTo($email));
        $user->expects($this->once())
            ->method('saveSettings');

        $result = $this->_guzzleJsonAuth->updateUser($user);
        $this->assertTrue($result);
        $this->assertAttributeEquals('2015-05-12 20 20:10:53', 'mEmailAuthenticated', $user);
    }

    /**
     * Tests the autoCreate method
     *
     * @return void
     * @covers ::autoCreate
     */
    public function testAutoCreate() {
        $result = $this->_guzzleJsonAuth->autoCreate();
        $this->assertTrue($result);
    }

    /**
     * Tests the allowPropChange method
     *
     * @return void
     * @covers ::allowPropChange
     */
    public function testAllowPropChange() {
        $result = $this->_guzzleJsonAuth->allowPropChange();
        $this->assertFalse($result);
    }

    /**
     * Tests the allowPasswordChange method
     *
     * @return void
     * @covers ::allowPasswordChange
     */
    public function testAllowPasswordChange() {
        $result = $this->_guzzleJsonAuth->allowPasswordChange();
        $this->assertFalse($result);
    }

    /**
     * Tests the allowSetLocalPassword method
     *
     * @return void
     * @covers ::allowSetLocalPassword
     */
    public function testAllowSetLocalPassword() {
        $result = $this->_guzzleJsonAuth->allowSetLocalPassword();
        $this->assertFalse($result);
    }

    /**
     * Tests the setPassword method
     *
     * @return void
     * @covers ::setPassword
     */
    public function testSetPassword() {
        $result = $this->_guzzleJsonAuth->setPassword('username', 'password');
        $this->assertFalse($result);
    }

    /**
     * Tests the updateExternalDB method
     *
     * @return void
     * @covers ::updateExternalDB
     */
    public function testUpdateExternalDB() {
        $user = ''; // TODO Create Mock
        // TODO Set expectations
        $result = $this->_guzzleJsonAuth->updateExternalDB($user);
        $this->assertTrue($result);
    }

    /**
     * Tests the updateExternalDBGroups method
     *
     * @return void
     * @covers ::updateExternalDBGroups
     */
    public function testUpdateExternalDBGroups() {
        $user = ''; // TODO Create Mock
        $addgroup = '';
        // TODO Set expectations
        $result = $this->_guzzleJsonAuth->updateExternalDBGroups($user, $addgroup);
        $this->assertTrue($result);
    }

    /**
     * Tests the canCreateAccounts method
     *
     * @return void
     * @covers ::canCreateAccounts
     */
    public function testCanCreateAccounts() {
        $result = $this->_guzzleJsonAuth->canCreateAccounts();
        $this->assertFalse($result);
    }

    /**
     * Tests the addUser method
     *
     * @return void
     * @covers ::addUser
     */
    public function testAddUser() {
        $result = $this->_guzzleJsonAuth->addUser('username', 'password');
        $this->assertFalse($result);
    }

    /**
     * Tests the strict method
     *
     * @return void
     * @covers ::strict
     */
    public function testStrict() {
        $result = $this->_guzzleJsonAuth->strict();
        $this->assertFalse($result);
    }

    /**
     * Tests the strictUserAuth method
     *
     * @return void
     * @covers ::strictUserAuth
     */
    public function testStrictUserAuth() {
        $result = $this->_guzzleJsonAuth->strictUserAuth('username');
        $this->assertFalse($result);
    }

}
