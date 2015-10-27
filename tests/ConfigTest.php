<?php

    namespace AloFramework\Session\Tests;

    use AloFramework\Session\Config as Cfg;
    use PHPUnit_Framework_TestCase;

    class ConfigTest extends PHPUnit_Framework_TestCase {

        function testConfig() {
            $expected = [Cfg::CFG_TIMEOUT          => 300,
                         Cfg::CFG_COOKIE_NAME      => 'AloSession',
                         Cfg::CFG_FINGERPRINT_NAME => '_fp_',
                         Cfg::CFG_PREFIX           => '_alo_sess_',
                         Cfg::CFG_SESSION_ID_ALGO  => 'sha512',
                         Cfg::CFG_SECURE           => true];

            $this->assertEquals($expected, (new Cfg())->getAll());
        }
    }