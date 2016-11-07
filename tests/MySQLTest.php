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
use AloFramework\Session\MySQLSession as Sess;
use PDO;
use PHPUnit_Framework_TestCase;

require_once 'AbstractSessionTest.php';

class MySQLTest extends PHPUnit_Framework_TestCase {

    /** @var PDO */
    protected $client;

    /** @var Cfg */
    protected $cfg;

    function __construct($name = null, array $data = [], $dataName = '') {
        parent::__construct($name, $data, $dataName);
        $this->cfg = new Cfg([Cfg::CFG_SECURE => false,
                              Cfg::CFG_SAVE_CLI => true]);

        $this->client = self::initPDO();
    }

    public static function initPDO() {
        $pdo = new PDO('mysql:dbname=phpunit;host=localhost;charset=utf8mb4;port=3306', 'root', '');
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
        $pdo->query('CREATE TABLE IF NOT EXISTS `alo_session` (
                                  `id`     CHAR(128)
                                           CHARACTER SET ascii NOT NULL,
                                  `data`   TEXT                NOT NULL,
                                  `access` TIMESTAMP           NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                                  PRIMARY KEY (`id`),
                                  KEY `access` (`access`)
                                )
                                  ENGINE = InnoDB
                                  DEFAULT CHARSET = utf8mb4;')->execute();
        return $pdo;
    }

    function testRW() {
        $this->assertFalse(Sess::isActive());

        (new Sess($this->client, $this->cfg))->start();
        $this->assertTrue(Sess::isActive());
        $prefix               = session_id();
        $_SESSION[__METHOD__] = 'foo';
        session_write_close();
        $this->assertFalse(Sess::isActive());

        $this->assertEquals($_SESSION[__METHOD__], 'foo');
        $select =
            $this->client->query('SELECT `data` FROM `' . $this->cfg->table . '` WHERE `id`=\'' . $prefix . '\'')
                         ->fetchAll(PDO::FETCH_ASSOC);

        $this->assertNotEmpty($select);

        $sess = AbstractSessionTest::sessionUnserialize($select[0]['data']);

        $this->assertTrue(array_key_exists($this->cfg->fingerprint, $sess));
        $this->assertEquals('s:3:"foo"', $sess[__METHOD__]);
    }

    function testDestroy() {
        $this->assertFalse(Sess::isActive());

        $session = new Sess($this->client, $this->cfg);
        $session->start();
        $this->assertTrue(Sess::isActive());

        $_SESSION[__METHOD__] = 1;
        $session->write(session_id(), serialize($_SESSION));

        $prefix = session_id();

        $this->assertNotEmpty($this->client->query('SELECT `data` FROM `' . $this->cfg->table . '` WHERE `id`=\'' .
                                                   $prefix . '\'')->fetchAll(PDO::FETCH_ASSOC));

        session_destroy();
        $this->assertEmpty($this->client->query('SELECT `data` FROM `' . $this->cfg->table . '` WHERE `id`=\'' .
                                                $prefix . '\'')->fetchAll(PDO::FETCH_ASSOC));

        $this->assertFalse(Sess::isActive());
    }
}
