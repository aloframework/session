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

    namespace AloFramework\Session;

    /**
     * A version of the MySQL session handler when event handling is impossible. The use of this class is not
     * recommended.
     *
     * @author Art <a.molcanovas@gmail.com>
     */
    class MySQLNoEventSession extends MySQLSession {

        /**
         * Cleanup old sessions.
         *
         * @author Art <a.molcanovas@gmail.com>
         * @link   http://php.net/manual/en/sessionhandlerinterface.gc.php
         *
         * @param int $maxLifeTime Sessions that have not updated for the last maxlifetime seconds will be removed.
         *
         * @return bool The return value (usually TRUE on success, FALSE on failure). Note this value is returned
         * internally to PHP for processing.
         */
        public function gc($maxLifeTime) {
            $maxLifeTime = (int)$maxLifeTime;
            $table = $this->config->table;

            return $this->client->prepare('DELETE FROM `' . $table . '` WHERE DATE_ADD(`access`, INTERVAL ' .
                                          $maxLifeTime . ' SECOND) < NOW()')->execute();
        }

        /**
         * Starts the session
         *
         * @author Art <a.molcanovas@gmail.com>
         * @return self
         */
        public function start() {
            ini_set('session.gc_probability', 1);
            ini_set('session.gc_divisor', (int)$this->config->gc);
            ini_set('session.gc_maxlifetime', (int)$this->config->timeout);

            return parent::start();
        }
    }
