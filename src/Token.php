<?php

namespace AloFramework\Session;

use AloFramework\Common\Alo;
use AloFramework\Session\AbstractSession as Sess;
use InvalidArgumentException;
use JsonSerializable;

/**
 * CSRF token management
 * @author Art <a.molcanovas@gmail.com>
 * @since  1.2.1 is json serializable<br/>
 *         1.2
 */
class Token implements JsonSerializable {

    /**
     * Token key in the session array
     * @var string
     */
    private $tokenKey;

    /**
     * Token name
     * @var string
     */
    private $name;

    /**
     * Constructor
     * @author Art <a.molcanovas@gmail.com>
     *
     * @param string $name Token name/ID
     * @param Sess|Config $cfg The instance of the currently active session. If omitted,
     *                          AbstractSession::getLastActiveSession() will be used
     *
     * @throws InvalidArgumentException if $cfg isn't an instance of Config or AbstractSession
     */
    function __construct($name, $cfg = null) {
        if ($cfg instanceof Config) {
            $this->tokenKey = $cfg->tokenKey;
        } elseif ($cfg instanceof Sess) {
            $this->tokenKey = $cfg->getConfig(Config::CFG_TOKEN);
        } elseif ($cfg === null && $lastSession = Sess::getLastActiveSession()) {
            $this->tokenKey = $lastSession->getConfig(Config::CFG_TOKEN);
        } else {
            throw new InvalidArgumentException('$cfg must be an instance of ' . __NAMESPACE__ . '\\Config or ' .
                                               __NAMESPACE__ . '\\AbstractSession');
        }

        $this->name = $name;
    }

    /**
     * Returns a json-serializable version of this object
     * @author Art <a.molcanovas@gmail.com>
     * @return array
     */
    function jsonSerialize() {
        return ['name' => $this->name,
                'key' => $this->tokenKey];
    }

    /**
     * Returns a previously generated token
     * @author Art <a.molcanovas@gmail.com>
     *
     * @return string|null
     */
    function get() {
        if (!Sess::isActive()) {
            self::sessionRequiredWarning(__METHOD__);

            // @codeCoverageIgnoreStart
            return null;
            // @codeCoverageIgnoreEnd
        }

        return Alo::nullget($_SESSION[$this->tokenKey][$this->name]);
    }

    /**
     * Trigger an error when an operation requires an active session, but one isn't active
     * @author Art <a.molcanovas@gmail.com>
     *
     * @param string $method The method used
     */
    private static function sessionRequiredWarning($method) {
        trigger_error($method . ' failed: the session must be started first', E_USER_WARNING);
        //@codeCoverageIgnoreStart
    }
    //@codeCoverageIgnoreEnd

    /**
     * Creates and returns a token
     * @author Art <a.molcanovas@gmail.com>
     *
     * @param string $hash Hash algorithm to use for the token
     *
     * @return string|null
     */
    function create($hash = 'sha256') {
        if (!Sess::isActive()) {
            self::sessionRequiredWarning(__METHOD__);

            // @codeCoverageIgnoreStart
            return null;
            // @codeCoverageIgnoreEnd
        }

        $entry = &$_SESSION[$this->tokenKey];

        if (!Alo::get($entry) || !is_array($entry)) {
            $entry = [];
        }

        $entry[$this->name] = Alo::getUniqid($hash, __METHOD__);

        return $entry[$this->name];
    }

    /**
     * Removes all the stored tokens
     * @author Art <a.molcanovas@gmail.com>
     * @return bool
     */
    function removeAllTokens() {
        if (!Sess::isActive()) {
            self::sessionRequiredWarning(__METHOD__);
            //@codeCoverageIgnoreStart
        }
        //@codeCoverageIgnoreEnd

        if (isset($_SESSION[$this->tokenKey])) {
            unset($_SESSION[$this->tokenKey]);

            return true;
        }

        return false;
    }

    /**
     * Gets a token and removes it from the session
     * @author Art <a.molcanovas@gmail.com>
     *
     * @return mixed|null The token
     */
    function getAndRemove() {
        if (!Sess::isActive()) {
            self::sessionRequiredWarning(__METHOD__);

            // @codeCoverageIgnoreStart
            return null;
            // @codeCoverageIgnoreEnd
        }

        $tok = Alo::nullget($_SESSION[$this->tokenKey][$this->name]);
        $this->remove();

        return $tok;
    }

    /**
     * Removes a token
     * @author Art <a.molcanovas@gmail.com>
     *
     * @return bool
     */
    function remove() {
        if (!Sess::isActive()) {
            self::sessionRequiredWarning(__METHOD__);
        } elseif (isset($_SESSION[$this->tokenKey][$this->name])) {
            unset($_SESSION[$this->tokenKey][$this->name]);

            if (empty($_SESSION[$this->tokenKey])) {
                unset($_SESSION[$this->tokenKey]);
            }

            return true;
        }

        return false;
    }

    /**
     * Returns the token name
     * @author Art <a.molcanovas@gmail.com>
     * @return string
     */
    protected function getName() {
        return $this->name;
    }

    /**
     * Returns the token key
     * @author Art <a.molcanovas@gmail.com>
     * @return string
     */
    protected function getTokenKey() {
        return $this->tokenKey;
    }
}
