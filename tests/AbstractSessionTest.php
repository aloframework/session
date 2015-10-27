<?php

    namespace AloFramework\Session\Tests;

    use AloFramework\Session\Config as Cfg;
    use AloFramework\Session\RedisSession as Sess;
    use PHPUnit_Framework_TestCase;

    class AbstractSessionTest extends PHPUnit_Framework_TestCase {

        /** @var Cfg */
        protected $cfg;

        function __construct($name = null, array $data = [], $dataName = '') {
            parent::__construct($name, $data, $dataName);
            $this->cfg = new Cfg([Cfg::CFG_SECURE => false]);
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
    }
