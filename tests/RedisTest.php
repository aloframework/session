<?php

    namespace AloFramework\Session\Tests;

    use AloFramework\Session\RedisSession as Sess;

    class RedisTest extends AbstractSessionTest {

        /** @var \Redis */
        private $redis;

        function __construct($name = null, array $data = [], $dataName = '') {
            parent::__construct($name, $data, $dataName);

            $this->redis = new \Redis();
            $this->redis->connect('127.0.0.1');
        }

        function testRW() {
            $this->assertFalse(Sess::isActive());

            (new Sess($this->redis, $this->cfg))->start();
            $this->assertTrue(Sess::isActive());
            $prefix               = $this->cfg->prefix . session_id();
            $_SESSION[__METHOD__] = 'foo';
            session_write_close();
            $this->assertFalse(Sess::isActive());

            $this->assertEquals($_SESSION[__METHOD__], 'foo');
            $this->assertTrue($this->redis->exists($prefix));

            $sess = self::sessionUnserialize($this->redis->get($prefix));

            $this->assertTrue(array_key_exists($this->cfg->fingerprint, $sess));
            $this->assertEquals('s:3:"foo"', $sess[__METHOD__]);
        }

        function testDestroy() {
            $this->assertFalse(Sess::isActive());

            $session = new Sess($this->redis, $this->cfg);
            $session->start();
            $this->assertTrue(Sess::isActive());

            $_SESSION[__METHOD__] = 1;
            $session->write(session_id(), $_SESSION);

            $prefix = $this->cfg->prefix . session_id();
            $this->assertTrue($this->redis->exists($prefix));

            session_destroy();
            $this->assertFalse($this->redis->exists($prefix));

            $this->assertFalse(Sess::isActive());
        }
    }
