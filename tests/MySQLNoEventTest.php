<?php

    namespace AloFramework\Session\Tests;

    use AloFramework\Session\MySQLNoEventSession as Sess;
    use AloFramework\Session\Config as Cfg;
    use PDO;

    require_once 'MySQLTest.php';

    class MySQLNoEventTest extends MySQLTest {

        function testStart() {
            $this->assertFalse(Sess::isActive());
            (new Sess($this->client, $this->cfg))->start();

            $this->assertEquals(1, ini_get('session.gc_probability'));
            $this->assertEquals((int)$this->cfg->gc, ini_get('session.gc_divisor'));
            $this->assertEquals((int)$this->cfg->timeout, ini_get('session.gc_maxlifetime'));
        }

        function testGc() {
            $gc  = 1;
            $cfg = new Cfg([Cfg::CFG_SECURE  => false,
                            Cfg::CFG_TIMEOUT => $gc,
                            Cfg::CFG_GC      => $gc]);

            $sess                 = (new Sess($this->client, $cfg))->start();
            $id                   = session_id();
            $_SESSION[__METHOD__] = 'foo';
            session_write_close();

            $this->assertNotEmpty($this->client->query('SELECT `data` FROM `' . $this->cfg->table . '` WHERE `id`=\'' .
                                                       $id . '\'')->fetchAll(PDO::FETCH_ASSOC));
            sleep(2);
            $sess->gc($gc);
            $this->assertEmpty($this->client->query('SELECT `data` FROM `' . $this->cfg->table . '` WHERE `id`=\'' .
                                                    $id . '\'')->fetchAll(PDO::FETCH_ASSOC));
        }
    }
