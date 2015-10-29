<?php

    namespace AloFramework\Session;

    /**
     * A version of the MySQL session handler when event handling is impossible. The use of this class is not
     * recommended.
     * @author Art <a.molcanovas@gmail.com>
     */
    class MySQLNoEventSession extends MySQLSession {

        /**
         * Cleanup old sessions.
         * @author Art <a.molcanovas@gmail.com>
         * @link   http://php.net/manual/en/sessionhandlerinterface.gc.php
         *
         * @param int $maxLifeTime Sessions that have not updated for the last maxlifetime seconds will be removed.
         *
         * @return bool The return value (usually TRUE on success, FALSE on failure). Note this value is returned
         * internally to PHP for processing.
         */
        function gc($maxLifeTime) {
            $maxLifeTime = (int)$maxLifeTime;
            $table       = $this->config->table;

            return $this->client->prepare('DELETE FROM `' . $table . '` WHERE DATE_ADD(`access`, INTERVAL ' .
                                          $maxLifeTime . ' SECOND) >= NOW()')->execute();
        }

        /**
         * Starts the session
         * @author Art <a.molcanovas@gmail.com>
         * @return self
         */
        function start() {
            ini_set('session.gc_probability', 1);
            ini_set('session.gc_divisor', (int)$this->config->gc);
            ini_set('session.gc_maxlifetime', (int)$this->config->timeout);

            return parent::start();
        }
    }
