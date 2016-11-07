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
    use PHPUnit_Framework_TestCase;

    class ConfigTest extends PHPUnit_Framework_TestCase {

        function testConfig() {
            $expected = [Cfg::CFG_TIMEOUT          => 300,
                         Cfg::CFG_COOKIE_NAME      => 'AloSession',
                         Cfg::CFG_FINGERPRINT_NAME => '_fp_',
                         Cfg::CFG_PREFIX           => '_alo_sess_',
                         Cfg::CFG_SESSION_ID_ALGO  => 'sha512',
                         Cfg::CFG_SECURE           => true,
                         Cfg::CFG_TABLE            => 'alo_session',
                         Cfg::CFG_GC               => 100,
                         Cfg::CFG_SAVE_CLI         => false,
                         Cfg::CFG_TOKEN            => '_tk_'];

            $this->assertEquals($expected, (new Cfg())->getAll());
        }
    }
