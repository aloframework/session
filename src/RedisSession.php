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

    use AloFramework\Session\SessionException as SEx;
    use Psr\Log\LoggerInterface;
    use Redis;

    /**
     * Redis-based session handler
     *
     * @author Art <a.molcanovas@gmail.com>
     */
    class RedisSession extends AbstractSession {

        /**
         * The Redis client
         *
         * @var Redis
         */
        protected $client;

        /**
         * Constructor
         *
         * @author Art <a.molcanovas@gmail.com>
         *
         * @param Redis           $redis  The Redis instance with an active connection. If omitted, a new one will be
         *                                created and an attempt to connect to localhost with default settings will
         *                                be made.
         * @param Config          $cfg    Your custom configuration
         * @param LoggerInterface $logger A logger object. If omitted, AloFramework\Log will be used.
         *
         * @throws SEx When $redis isn't supplied and we're unable to connect to localhost.
         */
        public function __construct(Redis $redis = null, Config $cfg = null, LoggerInterface $logger = null) {
            //@codeCoverageIgnoreStart
            if (!$redis) {
                $redis = new Redis();
                if (!$redis->connect('127.0.0.1')) {
                    throw new SEx('Unable to connect to Redis @ 127.0.0.1');
                }
            }
            //@codeCoverageIgnoreEnd

            $this->client = $redis;

            //Parent constructor must be called after $this->client is set
            parent::__construct($cfg, $logger);
        }

        /**
         * Destroy a session
         *
         * @author Art <a.molcanovas@gmail.com>
         * @link   http://php.net/manual/en/sessionhandlerinterface.destroy.php
         *
         * @param string $sessionID The session ID being destroyed.
         *
         * @return bool The return value (usually TRUE on success, FALSE on failure). Note this value is returned
         * internally to PHP for processing.
         */
        public function destroy($sessionID) {
            $parent = parent::destroy($sessionID);
            $this->client->delete($this->config->prefix . $sessionID);

            return $parent;
        }

        /**
         * Read session data
         *
         * @author Art <a.molcanovas@gmail.com>
         * @link   http://php.net/manual/en/sessionhandlerinterface.read.php
         *
         * @param string $sessionID The session id to read data for.
         *
         * @return string Returns an encoded string of the read data. If nothing was read, it must return an empty
         *                string. Note this value is returned internally to PHP for processing.
         */
        public function read($sessionID) {
            $get = $this->client->get($this->config->prefix . $sessionID);

            return is_string($get) ? $get : '';
        }

        /**
         * Write session data
         *
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
        public function write($sessionID, $sessionData) {
            if ($this->shouldBeSaved()) {
                return $this->client->setex($this->config->prefix . $sessionID,
                                            $this->config->timeout,
                                            $sessionData);
            }

            // @codeCoverageIgnoreStart
            return true;
            // @codeCoverageIgnoreEnd
        }

        /**
         * Check if the given session ID exists
         *
         * @author Art <a.molcanovas@gmail.com>
         *
         * @param string $sessionID The session ID
         *
         * @return bool
         */
        protected function idExists($sessionID) {
            return $this->client->exists($sessionID);
        }

    }
