<?php
    /**
 *    Copyright (c) Arturas Molcanovas <a.molcanovas@gmail.com> 2016.
 *    https://github.com/aloframework/session
 *
 *    Licensed under the Apache License, Version 2.0 (the "License");
 *    you may not use this file except in compliance with the License.
 *    You may obtain a copy of the License at
 *
 *        http://www.apache.org/licenses/LICENSE-2.0
 *
 *    Unless required by applicable law or agreed to in writing, software
 *    distributed under the License is distributed on an "AS IS" BASIS,
 *    WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 *    See the License for the specific language governing permissions and
 *    limitations under the License.
 */

namespace AloFramework\Session\Tests;

use AloFramework\Common\Alo;
use AloFramework\Session\Config as Cfg;
use AloFramework\Session\RedisSession as Sess;
use PHPUnit_Framework_TestCase;
use Redis;

if (file_exists('vendor/autoload.php')) {
    require_once 'vendor/autoload.php';
} else {
    require_once '../vendor/autoload.php';
}

class AbstractSessionTest extends PHPUnit_Framework_TestCase {

    /** @var Cfg */
    protected $cfg;

    /** @var Redis */
    private $redis;

    function __construct($name = null, array $data = [], $dataName = '') {
        parent::__construct($name, $data, $dataName);
        $this->cfg   = new Cfg([Cfg::CFG_SECURE => false,
                                Cfg::CFG_SAVE_CLI => true]);
        $this->redis = new Redis();
        $this->redis->connect('127.0.0.1');
    }

    function checkSouldBeSaved() {
        $sess = new ProtectedChecks($this->redis, $this->cfg);
        $this->assertTrue($sess->shouldBeSavedCheck());
        $sess->addConfig(Cfg::CFG_SAVE_CLI, false);
        $this->assertFalse($sess->shouldBeSavedCheck());
    }

    function testgetLastActiveSession() {
        $this->assertNull(Sess::getLastActiveSession());

        (new Sess($this->redis, $this->cfg))->start();
        $this->assertTrue(Sess::getLastActiveSession() instanceof Sess);
        session_write_close();
        $this->assertNull(Sess::getLastActiveSession());

        (new Sess($this->redis, $this->cfg))->start();
        $this->assertTrue(Sess::getLastActiveSession() instanceof Sess);
        session_destroy();
        $this->assertNull(Sess::getLastActiveSession());
    }

    function testDestruct() {
        $sess = new Sess($this->redis, $this->cfg);

        $this->assertFalse(Sess::isActive());
        $sess->start();
        $this->assertTrue(Sess::isActive());

        $key = $this->cfg->prefix . session_id();
        $this->assertFalse($this->redis->exists($key));

        $_SESSION[__METHOD__] = 'foo';
        $sess->__destruct();
        $this->assertEquals('s:3:"foo"', self::sessionUnserialize($this->redis->get($key))[__METHOD__]);
    }

    public static function sessionUnserialize($str) {
        $spl = explode(';', $str);

        foreach ($spl as $k => $s) {
            if (trim($s)) {
                $s          = explode('|', $s);
                $spl[$s[0]] = $s[1];
            }
            unset($spl[$k]);
        }

        return $spl;
    }

    function testJsonSerializeNoSession() {
        $sess = new Sess($this->redis, $this->cfg);
        $enc  = json_encode($sess);
        $this->assertEquals(json_encode([]), $enc);
    }

    function testJsonSerializeSession() {
        $sess = new Sess($this->redis, $this->cfg);
        $sess->start();
        $finger               = (new Cfg())->get(Cfg::CFG_FINGERPRINT_NAME);
        $_SESSION[__METHOD__] = 'bar';
        $code                 = json_decode(json_encode($sess), true);

        $this->assertEquals('bar', Alo::get($code[__METHOD__]));
        $this->assertTrue(isset($code[$finger]));
    }

    function testDestroySafely() {
        $this->assertFalse(Sess::isActive());
        $this->assertFalse(Sess::destroySafely());
        (new Sess(null, $this->cfg))->start();
        $this->assertTrue(Sess::isActive());
        $this->assertTrue(Sess::destroySafely());
        $this->assertFalse(Sess::isActive());
    }

    /**
     * @expectedException \PHPUnit_Framework_Error
     */
    function testStartDuplicate() {
        (new Sess($this->redis, $this->cfg))->start();
        (new Sess($this->redis, $this->cfg))->start();
    }

    /**
     * @expectedException \PHPUnit_Framework_Error
     */
    function testSessionRequiredWarningIsset() {
        $s = new Sess(null, $this->cfg);

        /** @noinspection PhpExpressionResultUnusedInspection */
        isset($s['foo']);
    }

    /**
     * @expectedException \PHPUnit_Framework_Error
     */
    function testSessionRequiredWarningUnset() {
        $s = new Sess(null, $this->cfg);

        unset($s['foo']);
    }

    /**
     * @expectedException \PHPUnit_Framework_Error
     */
    function testSessionRequiredWarningOffsetGet() {
        $s = new Sess(null, $this->cfg);

        $s['foo'];
    }

    /**
     * @expectedException \PHPUnit_Framework_Error
     */
    function testSessionRequiredWarningMagicGet() {
        $s = new Sess(null, $this->cfg);

        $s->foo;
    }

    /**
     * @expectedException \PHPUnit_Framework_Error
     */
    function testSessionRequiredWarningOffsetSet() {
        $s = new Sess(null, $this->cfg);

        $s['foo'] = 'bar';
    }

    /**
     * @expectedException \PHPUnit_Framework_Error
     */
    function testSessionRequiredWarningMagicSet() {
        $s = new Sess(null, $this->cfg);

        $s->foo = 'bar';
    }

    function testBadIdentityCheck() {
        /** @var ProtectedChecks $sess */

        $this->assertFalse(Sess::isActive());
        $sess                 = (new ProtectedChecks($this->redis, $this->cfg))->start();
        $sid                  = session_id();
        $prefix               = $this->cfg->prefix . $sid;
        $_SESSION[__METHOD__] = 1;
        $sess->write($sid, $_SESSION);

        $this->assertTrue(Sess::isActive());
        $this->assertTrue($this->redis->exists($prefix));

        $sess->idCheck($sid);

        $this->assertFalse(Sess::isActive());
        $this->assertFalse($this->redis->exists($prefix));
    }

    function testMagic() {
        $sess        = (new Sess($this->redis, $this->cfg))->start();
        $sess['one'] = 'foo';
        $sess->two   = 'bar';

        $this->assertTrue(isset($sess['one']));
        $this->assertTrue(isset($_SESSION['one']));

        $this->assertEquals('foo', $sess->{'one'});
        $this->assertEquals('bar', $sess['two']);
        $this->assertEquals('foo', $_SESSION['one']);
        $this->assertEquals('bar', $_SESSION['two']);

        unset($sess['two']);
        $this->assertFalse(isset($_SESSION['two']));
    }
}

class ProtectedChecks extends Sess {

    function idCheck($sessionID) {
        parent::handleIdentityCheckFailure($sessionID);
    }

    function shouldBeSavedCheck() {
        return $this->shouldBeSaved();
    }
}
