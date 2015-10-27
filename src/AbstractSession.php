<?php

    namespace AloFramework\Session;

    use AloFramework\Common\Alo;
    use AloFramework\Config\Configurable;
    use AloFramework\Config\ConfigurableTrait;
    use AloFramework\Log\Log;
    use Psr\Log\LoggerInterface;
    use SessionHandlerInterface;

    /**
     * Abstract session operations
     * @author Art <a.molcanovas@gmail.com>
     * @property Config $config
     */
    abstract class AbstractSession implements SessionHandlerInterface, Configurable {

        use ConfigurableTrait;

        /**
         * Logger instance
         * @var LoggerInterface
         */
        protected $log;

        /**
         * Constructor
         * @author Art <a.molcanovas@gmail.com>
         *
         * @param Config          $cfg    Your custom configuration
         * @param LoggerInterface $logger A logger object. If omitted, AloFramework\Log will be used.
         */
        function __construct(Config $cfg = null, LoggerInterface $logger = null) {
            $this->config = Alo::ifnull($cfg, new Config());
            $this->log    = Alo::ifnull($logger, new Log());
            $this->setID();
        }

        /**
         * Sets the session ID variable & the cookie
         *
         * @author Art <a.molcanovas@gmail.com>
         * @return string The generated ID
         */
        protected function setID() {
            $c = Alo::nullget($_COOKIE[$this->config->cookie]);
            if ($c && strlen($c) == strlen(hash($this->config->sessionAlgo, 1))) {
                $sid = $c;
            } else {
                do {
                    $sid = Alo::getUniqid($this->config->sessionAlgo, 'session' . Alo::getFingerprint('md5'));
                } while ($this->idExists($sid));
            }

            session_id($sid);

            $this->log->debug('Session ID set to ' . $sid);

            return $sid;
        }

        /**
         * Check if the given session ID exists
         * @author Art <a.molcanovas@gmail.com>
         *
         * @param string $sessionID The session ID
         *
         * @return bool
         */
        protected abstract function idExists($sessionID);

        /**
         * Only calls session_destroy() if a session is active
         *
         * @author Art <a.molcanovas@gmail.com>
         * @return bool
         */
        static function destroySafely() {
            if (self::isActive()) {
                session_destroy();

                return true;
            } else {
                return false;
            }
        }

        /**
         * Checks whether a session is currently active
         * @author Art <a.molcanovas@gmail.com>
         * @return bool
         */
        static function isActive() {
            return session_status() === PHP_SESSION_ACTIVE;
        }

        /**
         * Starts the session
         * @author Art <a.molcanovas@gmail.com>
         * @return self
         */
        function start() {
            if (!self::isActive()) {
                session_set_cookie_params($this->config->timeout, '/', null, $this->config->secure, true);
                session_name($this->config->cookie);

                session_set_save_handler($this, false);
                session_start();
                $this->identityCheck();
            } else {
                trigger_error('A session has already been started', E_USER_WARNING);
            }

            return $this;
        }

        /**
         * Checks if the session hasn't been hijacked
         *
         * @author Art <a.molcanovas@gmail.com>
         * @return boolean TRUE if the check has passed, FALSE if not and the session has been terminated.
         */
        private function identityCheck() {
            $fingerprint = self::getFingerprint();

            if (!Alo::nullget($_SESSION[$this->config->fingerprint])) {
                $_SESSION[$this->config->fingerprint] = $fingerprint;
            } elseif ($fingerprint !== $_SESSION[$this->config->fingerprint]) {
                $this->handleIdentityCheckFailure(session_id());

                return false;
            }
            $this->log->debug('Identity check passed for session ID ' . session_id());

            return true;
        }

        /**
         * Generates a session token
         *
         * @author Art <a.molcanovas@gmail.com>
         * @return string
         */
        private static function getFingerprint() {
            return md5('AloSession' . Alo::getFingerprint('md5'));
        }

        /**
         * What to do when an identity check fails
         * @author Art <a.molcanovas@gmail.com>
         *
         * @param string $sessionID The session ID that failed
         */
        protected function handleIdentityCheckFailure($sessionID) {
            $this->log->notice('Session identity check failed for session ID ' . $sessionID);
            $this->destroy($sessionID);
        }

        /**
         * Destroy a session
         * @author Art <a.molcanovas@gmail.com>
         * @link   http://php.net/manual/en/sessionhandlerinterface.destroy.php
         *
         * @param string $sessionID The session ID being destroyed.
         *
         * @return bool The return value (usually TRUE on success, FALSE on failure). Note this value is returned
         * internally to PHP for processing.
         */
        function destroy($sessionID) {
            return setcookie($this->config->cookie, '', time() - 3, null, null, $this->config->secure, true);
        }

        /**
         * Cleanup old sessions.
         * @author Art <a.molcanovas@gmail.com>
         * @link   http://php.net/manual/en/sessionhandlerinterface.gc.php
         *
         * @param int $maxlifetime Sessions that have not updated for the last maxlifetime seconds will be removed.
         *
         * @return bool The return value (usually TRUE on success, FALSE on failure). Note this value is returned
         * internally to PHP for processing.
         * @codeCoverageIgnore
         */
        function gc($maxlifetime) {
            return true;
        }

        /**
         * Initialize session
         * @author Art <a.molcanovas@gmail.com>
         * @link   http://php.net/manual/en/sessionhandlerinterface.open.php
         *
         * @param string $savePath  The path where to store/retrieve the session.
         * @param string $sessionID The session id.
         *
         * @return bool The return value (usually TRUE on success, FALSE on failure). Note this value is returned
         *              internally to PHP for processing.
         * @codeCoverageIgnore
         */
        function open($savePath, $sessionID) {
            return true;
        }

        /**
         * Close the session
         * @author Art <a.molcanovas@gmail.com>
         * @link   http://php.net/manual/en/sessionhandlerinterface.close.php
         * @return bool The return value (usually TRUE on success, FALSE on failure). Note this value is returned
         *              internally to PHP for processing.
         * @codeCoverageIgnore
         */
        function close() {
            return true;
        }

        /**
         * Saves session data
         * @author Art <a.molcanovas@gmail.com>
         */
        function __destruct() {
            if (self::isActive()) {
                session_write_close();
            }
        }
    }
