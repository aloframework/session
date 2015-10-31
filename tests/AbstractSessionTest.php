<?php

    namespace AloFramework\Session\Tests;

    use AloFramework\Session\Config as Cfg;
    use AloFramework\Session\RedisSession as Sess;
    use PHPUnit_Framework_TestCase;

    class ProtectedChecks extends Sess {

        function idCheck($sessionID) {
            parent::handleIdentityCheckFailure($sessionID);
        }

        function shouldBeSavedCheck() {
            return $this->shouldBeSaved();
        }
    }

    class AbstractSessionTest extends PHPUnit_Framework_TestCase {

        /** @var Cfg */
        protected $cfg;

        function __construct($name = null, array $data = [], $dataName = '') {
            parent::__construct($name, $data, $dataName);
            $this->cfg = new Cfg([Cfg::CFG_SECURE   => false,
                                  Cfg::CFG_SAVE_CLI => true]);
        }

        function checkSouldBeSaved() {
            $red = new \Redis();
            $red->connect('127.0.0.1');

            $sess = new ProtectedChecks($red, $this->cfg);
            $this->assertTrue($sess->shouldBeSavedCheck());
            $sess->addConfig(Cfg::CFG_SAVE_CLI, false);
            $this->assertFalse($sess->shouldBeSavedCheck());
        }

        function testDestruct() {
            $red = new \Redis();
            $red->connect('127.0.0.1');
            $sess = new Sess($red, $this->cfg);

            $this->assertFalse(Sess::isActive());
            $sess->start();
            $this->assertTrue(Sess::isActive());

            $key = $this->cfg->prefix . session_id();
            $this->assertFalse($red->exists($key));

            $_SESSION[__METHOD__] = 'foo';
            $sess->__destruct();
            $this->assertEquals('s:3:"foo"', self::sessionUnserialize($red->get($key))[__METHOD__]);
        }

        protected static function sessionUnserialize($str) {
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
            $red = new \Redis();
            $red->connect('127.0.0.1');

            (new Sess($red, $this->cfg))->start();
            (new Sess($red, $this->cfg))->start();
        }

        /**
         * @expectedException \PHPUnit_Framework_Error
         */
        function testSessionRequiredWarningIsset() {
            $s = new Sess(null, $this->cfg);

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
            $red = new \Redis();
            $red->connect('127.0.0.1');

            $this->assertFalse(Sess::isActive());
            $sess = (new ProtectedChecks($red, $this->cfg))->start();
            $sid                  = session_id();
            $prefix               = $this->cfg->prefix . $sid;
            $_SESSION[__METHOD__] = 1;
            $sess->write($sid, $_SESSION);

            $this->assertTrue(Sess::isActive());
            $this->assertTrue($red->exists($prefix));

            $sess->idCheck($sid);

            $this->assertFalse(Sess::isActive());
            $this->assertFalse($red->exists($prefix));
        }

        function testMagic() {
            $red = new \Redis();
            $red->connect('127.0.0.1');

            $sess        = (new Sess($red, $this->cfg))->start();
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
