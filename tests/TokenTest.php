<?php

    namespace AloFramework\Session\Tests;

    use AloFramework\Session\Config;
    use AloFramework\Session\RedisSession as Sess;
    use AloFramework\Session\Token;
    use PHPUnit_Framework_TestCase;
    use Redis;

    class TokenTest extends PHPUnit_Framework_TestCase {

        /**
         * @var Redis
         */
        private $redis;

        /**
         * @var string
         */
        private $key;

        /** @var Config */
        private $cfg;

        function __construct($name = null, array $data = [], $dataName = '') {
            parent::__construct($name, $data, $dataName);
            $this->redis = new Redis();
            $this->redis->connect('127.0.0.1');
            $this->cfg = new Config();
            $this->key = $this->cfg->tokenKey;
        }

        function testConstructWithConfigObject() {
            $tok = new TokenProtectedMethods(__METHOD__, $this->cfg);

            $this->assertEquals($this->key, $tok->getKeyTest());
            $this->assertEquals(__METHOD__, $tok->getNameTest());
        }

        function testConstructWithSessionObject() {
            $tok = new TokenProtectedMethods(__METHOD__, $this->sess());
            $this->assertEquals($this->key, $tok->getKeyTest());
        }

        function testConstructNull() {
            $this->sess();
            $tok = new TokenProtectedMethods(__METHOD__);
            $this->assertEquals($this->key, $tok->getKeyTest());
        }

        function testConstructNullNoSession() {
            Sess::destroySafely();
            $this->setExpectedException('\InvalidArgumentException');
            new TokenProtectedMethods(__METHOD__);
        }

        /** @dataProvider methodSessionWarningsProvider */
        function testMethodSessionWarnings($method) {
            Sess::destroySafely();
            $this->setExpectedException('\PHPUnit_Framework_Error');
            $tok = new Token('foo', $this->cfg);

            call_user_func([$tok, $method]);
        }

        function testCreate() {
            $this->sess();
            $tok = new Token(__METHOD__, $this->cfg);
            $this->assertFalse(array_key_exists($this->key, $_SESSION));

            $token = $tok->create('sha512');
            $this->assertEquals(strlen(hash('sha512', 1)), strlen($token));
            $this->assertTrue(array_key_exists($this->key, $_SESSION));

            $this->assertTrue(array_key_exists(__METHOD__, $_SESSION[$this->key]));
            $this->assertEquals($token, $_SESSION[$this->key][__METHOD__]);
        }

        function testGet() {
            $this->sess();
            $tok = new Token(__METHOD__, $this->cfg);
            $this->assertTrue($tok->get() === null);

            $create = $tok->create();

            $this->assertEquals($create, $tok->get());
        }

        function testRemove() {
            $this->sess();
            $tok = new Token(__METHOD__, $this->cfg);

            $this->assertFalse($tok->remove());
            $tok->create();
            $this->assertTrue($tok->remove());
        }

        function testRemoveMultiple() {
            $this->sess();
            $tok1 = new Token(__METHOD__ . '1', $this->cfg);
            $tok2 = new Token(__METHOD__ . '2', $this->cfg);

            $this->assertFalse(array_key_exists($this->key, $_SESSION));

            $tok1->create();
            $tok2->create();

            $this->assertTrue(array_key_exists($this->key, $_SESSION));
            $this->assertTrue(array_key_exists(__METHOD__ . '1', $_SESSION[$this->key]));
            $this->assertTrue(array_key_exists(__METHOD__ . '2', $_SESSION[$this->key]));

            $tok1->remove();
            $this->assertTrue(array_key_exists($this->key, $_SESSION));
            $this->assertFalse(array_key_exists(__METHOD__ . '1', $_SESSION[$this->key]));
            $this->assertTrue(array_key_exists(__METHOD__ . '2', $_SESSION[$this->key]));

            $tok2->remove();
            $this->assertFalse(array_key_exists($this->key, $_SESSION));
        }

        function testRemoveAllTokens() {
            $this->sess();
            /** @var Token[] $toks */
            $toks = [];

            $this->assertFalse(array_key_exists($this->key, $_SESSION));

            for ($i = 0; $i < 5; $i++) {
                $tok = new Token(__METHOD__ . $i, $this->cfg);
                $tok->create('md5');
                $toks[] = $tok;
            }

            foreach ($toks as $tok) {
                $this->assertEquals(32, strlen($tok->get()));
            }

            $this->assertTrue($toks[0]->removeAllTokens());
            $this->assertFalse($toks[0]->removeAllTokens());
            $this->assertFalse(array_key_exists($this->key, $_SESSION));
        }

        function testGetAndRemove() {
            $this->sess();
            $tok = new Token(__METHOD__, $this->cfg);
            $this->assertTrue($tok->get() === null);

            $create = $tok->create();
            $this->assertEquals($create, $tok->getAndRemove());

            $this->assertTrue($tok->get() === null);
        }

        function methodSessionWarningsProvider() {
            return [['get'],
                    ['create'],
                    ['remove'],
                    ['removeAllTokens'],
                    ['getAndRemove']];
        }

        private function sess() {
            Sess::destroySafely();

            return (new Sess($this->redis))->start();
        }
    }

    class TokenProtectedMethods extends Token {

        function getNameTest() {
            return $this->getName();
        }

        function getKeyTest() {
            return $this->getTokenKey();
        }
    }
