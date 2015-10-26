<?php

    namespace AloFramework\Session;

    use AloFramework\Cache\Clients\RedisClient;
    use AloFramework\Common\Alo;
    use AloFramework\Config\Configurable;
    use AloFramework\Config\ConfigurableTrait;
    use AloFramework\Log\Log;
    use Psr\Log\LoggerInterface;
    use SessionHandlerInterface;

    class RedisSession implements SessionHandlerInterface, Configurable {

        use ConfigurableTrait;

        /**
         * The Redis client
         * @var RedisClient
         */
        protected $client;

        /**
         * Logger instance
         * @var LoggerInterface
         */
        protected $log;

        /**
         * The configuration holder
         * @var Config
         */
        protected $config;

        /**
         * Constructor
         * @author Art <a.molcanovas@gmail.com>
         *
         * @param RedisClient     $redis  The Redis instance. If omitted, a new one will be created.
         * @param Config          $cfg    Your custom configuration
         * @param LoggerInterface $logger A logger object. If omitted, AloFramework\Log will be used.
         */
        function __construct(RedisClient $redis = null, Config $cfg = null, LoggerInterface $logger = null) {
            $this->client = Alo::ifnull($redis, new RedisClient());
            $this->config = Alo::ifnull($cfg, new Config());
            $this->log    = Alo::ifnull($logger, new Log());
        }

        /**
         * Close the session
         * @author Art <a.molcanovas@gmail.com>
         * @link   http://php.net/manual/en/sessionhandlerinterface.close.php
         * @return bool The return value (usually TRUE on success, FALSE on failure). Note this value is returned
         *              internally to PHP for processing.
         */
        public function close() {
            return true;
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
            return $this->client->delete($this->config->prefix . $sessionID);
        }

        /**
         * Cleanup old sessions. Not required for Redis sessions as the timeout manages this function.
         * @author Art <a.molcanovas@gmail.com>
         * @link   http://php.net/manual/en/sessionhandlerinterface.gc.php
         *
         * @param int $maxlifetime Sessions that have not updated for the last maxlifetime seconds will be removed.
         *
         * @return bool The return value (usually TRUE on success, FALSE on failure). Note this value is returned
         * internally to PHP for processing.
         */
        public function gc($maxlifetime) {
            return true;
        }

        /**
         * Initialize session
         * @author Art <a.molcanovas@gmail.com>
         * @link   http://php.net/manual/en/sessionhandlerinterface.open.php
         *
         * @param string $save_path  The path where to store/retrieve the session.
         * @param string $session_id The session id.
         *
         * @return bool <p>
         * The return value (usually TRUE on success, FALSE on failure).
         * Note this value is returned internally to PHP for processing.
         * </p>
         * @since  5.4.0
         */
        public function open($save_path, $session_id) {
            // TODO: Implement open() method.
        }

        /**
         * Read session data
         * @author Art <a.molcanovas@gmail.com>
         * @link   http://php.net/manual/en/sessionhandlerinterface.read.php
         *
         * @param string $session_id The session id to read data for.
         *
         * @return string <p>
         * Returns an encoded string of the read data.
         * If nothing was read, it must return an empty string.
         * Note this value is returned internally to PHP for processing.
         * </p>
         * @since  5.4.0
         */
        public function read($session_id) {
            // TODO: Implement read() method.
        }

        /**
         * Write session data
         * @author Art <a.molcanovas@gmail.com>
         * @link   http://php.net/manual/en/sessionhandlerinterface.write.php
         *
         * @param string $session_id   The session id.
         * @param string $session_data <p>
         *                             The encoded session data. This data is the
         *                             result of the PHP internally encoding
         *                             the $_SESSION superglobal to a serialized
         *                             string and passing it as this parameter.
         *                             Please note sessions use an alternative serialization method.
         *                             </p>
         *
         * @return bool <p>
         * The return value (usually TRUE on success, FALSE on failure).
         * Note this value is returned internally to PHP for processing.
         * </p>
         * @since  5.4.0
         */
        public function write($session_id, $session_data) {
            // TODO: Implement write() method.
        }

    }
