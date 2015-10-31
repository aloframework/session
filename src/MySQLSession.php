<?php

    namespace AloFramework\Session;

    use AloFramework\Session\SessionException as SEx;
    use PDO;
    use PDOException;
    use Psr\Log\LoggerInterface;

    /**
     * MySQL-based session handler
     * @author Art <a.molcanovas@gmail.com>
     */
    class MySQLSession extends AbstractSession {

        /**
         * The PDO instance
         * @var PDO
         */
        protected $client;

        /**
         * Constructor
         * @author Art <a.molcanovas@gmail.com>
         *
         * @param PDO             $pdo    PDO instance to use
         * @param Config          $cfg    Your custom configuration
         * @param LoggerInterface $logger A logger object. If omitted, AloFramework\Log will be used.
         */
        function __construct(PDO $pdo, Config $cfg = null, LoggerInterface $logger = null) {
            $this->client = $pdo;

            //Parent constructor must be called after $this->client is set
            parent::__construct($cfg, $logger);
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
            try {
                $sql  = $this->client->prepare('DELETE FROM `' . $this->config->table . '` WHERE `id`=? LIMIT 1');
                $exec = $sql->execute([$sessionID]);

                return $exec;
                //@codeCoverageIgnoreStart
            } catch (PDOException $e) {
                $this->log->error('Failed to remove session ' . $sessionID . ' from database: ' . $e->getMessage());

                return false;
            }
            //@codeCoverageIgnoreEnd
        }

        /**
         * Check if the given session ID exists
         * @author Art <a.molcanovas@gmail.com>
         *
         * @param string $sessionID The session ID
         *
         * @return bool
         * @throws SEx On PDOException or general query failure
         */
        protected function idExists($sessionID) {
            try {
                $sql =
                    $this->client->prepare('SELECT COUNT(*) FROM `' . $this->config->table . '` WHERE `id`=? LIMIT 0');

                //@codeCoverageIgnoreStart
                if (!$sql->execute([$sessionID])) {
                    throw new SEx('Failed to check if the session ID ' . $sessionID .
                                  ' exists: $sql->execute() returned ' . 'false', Sex::E_SECURITY_ERROR);
                } else {
                    //@codeCoverageIgnoreEnd
                    $exec = $sql->fetchAll(PDO::FETCH_NUM);

                    return empty($exec) ? false : $exec[0] != 0;
                }
                //@codeCoverageIgnoreStart
            } catch (PDOException $e) {
                throw new SEx('Failed to check if the session ID ' . $sessionID . ' exists: ' . $e->getMessage(),
                              SEx::E_PDO_FORWARD,
                              $e);
            }
            //@codeCoverageIgnoreEnd
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
            try {
                $sql = $this->client->prepare('SELECT `data` FROM `' . $this->config->table . '` WHERE `id`=? LIMIT 1');

                if ($sql->execute([$sessionID])) {
                    $exec = $sql->fetchAll(PDO::FETCH_COLUMN, 0);

                    //@codeCoverageIgnoreStart
                    if (!empty($exec)) {
                        return $exec[0];
                    }
                    //@codeCoverageIgnoreEnd
                }
                //@codeCoverageIgnoreStart
            } catch (PDOException $e) {
                $this->log->error('Error while fetching session data for ' . $sessionID . ': ' . $e->getMessage());

                return '';
            }

            //@codeCoverageIgnoreEnd

            return '';
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
            if ($this->shouldBeSaved()) {
                try {
                    $sql  =
                        $this->client->prepare('REPLACE INTO `' . $this->config->table . '`(`id`,`data`) VALUES(?,?)');
                    $exec = $sql->execute([$sessionID, $sessionData]);

                    return $exec;
                    //@codeCoverageIgnoreStart
                } catch (PDOException $e) {
                    $this->log->error('Failed to write session data for ' . $sessionID . ': ' . $e->getMessage());

                    return false;
                }
            }

            return false;
        }
        //@codeCoverageIgnoreEnd

    }
