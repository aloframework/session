<?php

    namespace AloFramework\Session\Tests;

    use AloFramework\Session\MySQLSession as Sess;
    use PDO;

    class MySQLTest extends AbstractSessionTest {

        /** @var PDO */
        private $client;

        function __construct($name = null, array $data = [], $dataName = '') {
            parent::__construct($name, $data, $dataName);

            $this->client = new PDO('mysql:dbname=phpunit;host=localhost;charset=utf8mb4;port=3306', 'root', '');
            $this->client->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->client->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
            $this->client->query('CREATE TABLE IF NOT EXISTS `alo_session` (
                                  `id`     CHAR(128)
                                           CHARACTER SET ascii NOT NULL,
                                  `data`   TEXT                NOT NULL,
                                  `access` TIMESTAMP           NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                                  PRIMARY KEY (`id`),
                                  KEY `access` (`access`)
                                )
                                  ENGINE = InnoDB
                                  DEFAULT CHARSET = utf8mb4;')->execute();
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

            $sess = self::sessionUnserialize($select[0]['data']);

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
