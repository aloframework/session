<?php

    namespace AloFramework\Session;

    use AloFramework\Session\SessionException as SEx;
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
         * @param Redis $redis            The Redis instance with an active connection. If omitted, a new one will be
         *                                created and an attempt to connect to localhost with default settings will
         *                                be made.
         * @param Config          $cfg    Your custom configuration
         * @param LoggerInterface $logger A logger object. If omitted, AloFramework\Log will be used.
         *
         * @throws SEx When $redis isn't supplied and we're unable to connect to localhost.
         */
        function __construct(Redis $redis = null, Config $cfg = null, LoggerInterface $logger = null) {
            parent::__construct($cfg, $logger);

            //@codeCoverageIgnoreStart
            if (!$redis) {
                $redis = new Redis();
                if (!$redis->connect('127.0.0.1')) {
                    throw new SEx('Unable to connect to Redis @ 127.0.0.1');
                }
            }
            //@codeCoverageIgnoreEnd

            $this->client = $redis;
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
            $this->client->delete($this->config->prefix . $sessionID);

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
            $get = $this->client->get($this->config->prefix . $sessionID);

            return is_string($get) ? $get : '';
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
