<?php

    namespace AloFramework\Session;

    use AloFramework\Common\Alo;
    use Psr\Log\LoggerInterface;
    use Redis;

    /**
     * Redis-based session handler
     * @author Art <a.molcanovas@gmail.com>
     */
    class RedisSession extends AbstractSession {

        /**
         * The Redis client
         * @var Redis
         */
        protected $client;

        /**
         * Constructor
         * @author Art <a.molcanovas@gmail.com>
         *
         * @param Redis $redis The Redis instance. If omitted, a new one will be created.
         * @param Config          $cfg    Your custom configuration
         * @param LoggerInterface $logger A logger object. If omitted, AloFramework\Log will be used.
         */
        function __construct(Redis $redis = null, Config $cfg = null, LoggerInterface $logger = null) {
            parent::__construct($cfg, $logger);
            $this->client = Alo::ifnull($redis, new Redis());
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
            parent::destroy($sessionID);

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
        function gc($maxlifetime) {
            return true;
        }

        /**
         * Read session data
         * @author Art <a.molcanovas@gmail.com>
         * @link   http://php.net/manual/en/sessionhandlerinterface.read.php
         *
         * @param string $sessionID The session id to read data for.
         *
         * @return string Returns an encoded string of the read data. If nothing was read, it must return an empty
         *                string. Note this value is returned internally to PHP for processing.
         */
        function read($sessionID) {
            return Alo::ifnull($this->client->get($this->config->prefix . $sessionID), '', true);
        }

        /**
         * Write session data
         * @author Art <a.molcanovas@gmail.com>
         * @link   http://php.net/manual/en/sessionhandlerinterface.write.php
         *
         * @param string $sessionID    The session id.
         * @param string $sessionData  The encoded session data. This data is the result of the PHP internally
         *                             encoding the $_SESSION superglobal to a serialized string and passing it as
         *                             this parameter. Please note sessions use an alternative serialization method.
         *
         * @return bool The return value (usually TRUE on success, FALSE on failure). Note this value is returned
         *              internally to PHP for processing.
         */
        function write($sessionID, $sessionData) {
            return $this->client->setex($this->config->prefix . $sessionID, $this->config->timeout, $sessionData);
        }

    }
