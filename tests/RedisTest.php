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

use AloFramework\Session\Config as Cfg;
use AloFramework\Session\RedisSession as Sess;
use PHPUnit_Framework_TestCase;

require_once 'AbstractSessionTest.php';

class RedisTest extends PHPUnit_Framework_TestCase {

    /** @var Cfg */
    protected $cfg;
    /** @var \Redis */
    private $client;

    function __construct($name = null, array $data = [], $dataName = '') {
        parent::__construct($name, $data, $dataName);

        $this->client = new \Redis();
        $this->client->connect('127.0.0.1');
        $this->cfg = new Cfg([Cfg::CFG_SECURE => false,
                              Cfg::CFG_SAVE_CLI => true]);
    }

    function testRW() {
        $this->assertFalse(Sess::isActive());

        (new Sess($this->client, $this->cfg))->start();
        $this->assertTrue(Sess::isActive());
        $prefix               = $this->cfg->prefix . session_id();
        $_SESSION[__METHOD__] = 'foo';
        session_write_close();
        $this->assertFalse(Sess::isActive());

        $this->assertEquals($_SESSION[__METHOD__], 'foo');
        $this->assertTrue($this->client->exists($prefix));

        $sess = AbstractSessionTest::sessionUnserialize($this->client->get($prefix));

        $this->assertTrue(array_key_exists($this->cfg->fingerprint, $sess));
        $this->assertEquals('s:3:"foo"', $sess[__METHOD__]);
    }

    function testDestroy() {
        $this->assertFalse(Sess::isActive());

        $session = new Sess($this->client, $this->cfg);
        $session->start();
        $this->assertTrue(Sess::isActive());

        $_SESSION[__METHOD__] = 1;
        $session->write(session_id(), $_SESSION);

        $prefix = $this->cfg->prefix . session_id();
        $this->assertTrue($this->client->exists($prefix));

        session_destroy();
        $this->assertFalse($this->client->exists($prefix));

        $this->assertFalse(Sess::isActive());
    }
}
