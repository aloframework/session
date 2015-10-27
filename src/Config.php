<?php

    namespace AloFramework\Session;

    use AloFramework\Config\AbstractConfig;

    /**
     * Configuration class
     * @author Art <a.molcanovas@gmail.com>
     *
     * @property int    $cleanupFrequency Probability of a session cleanup to be called on request. Entering 100 would
     *                                 mean
     *                                 that there is a 1/100 chance.
     * @property int    $timeout          Session timeout
     * @property string $cookie           Session cookie name
     * @property string $fingerprint      Session fingerprint name. This is used to prevent session hijacking and you
     *           must not set any session values using this key.
     * @property string $prefix           How to prefix session keys if using cache engine-based handlers
     * @property bool   $secure           If set to true the session cookie will only be sent via HTTPS connections
     * @property string $sessionAlgo      Session ID generator hash algorithm
     */
    class Config extends AbstractConfig {

        /**
         * Probability of a session cleanup to be called on request. Entering 100
         * would mean that there is a 1/100 chance.
         * @var string
         */
        const CFG_CLEANUP_FREQUENCY = 'cleanupFrequency';

        /**
         * Session timeout
         * @var string
         */
        const CFG_TIMEOUT = 'timeout';

        /**
         * Session cookie name
         * @var string
         */
        const CFG_COOKIE_NAME = 'cookie';

        /**
         * Session fingerprint name. This is used to prevent session hijacking and you must not set any session
         * values using this key.
         * @var string
         */
        const CFG_FINGERPRINT_NAME = 'fingerprint';

        /**
         * How to prefix session keys if using cache engine-based handlers
         * @var string
         */
        const CFG_PREFIX = 'prefix';

        /**
         * If set to true the session cookie will only be sent via HTTPS connections
         * @var string
         */
        const CFG_SECURE = 'secure';

        /**
         * Session ID generator hash algorithm
         * @var string
         */
        const CFG_SESSION_ID_ALGO = 'sessionAlgo';

        /**
         * Default settings array
         * @var array
         */
        private static $defaults;

        /**
         * Constructor
         * @author Art <a.molcanovas@gmail.com>
         *
         * @param array $cfg Your custom config overrides
         */
        function __construct(array $cfg = []) {
            self::setDefaultConfig();
            parent::__construct(self::$defaults, $cfg);
        }

        /**
         * Sets the default configuration array
         * @author Art <a.molcanovas@gmail.com>
         */
        private static function setDefaultConfig() {
            if (!self::$defaults) {
                self::$defaults = [self::CFG_CLEANUP_FREQUENCY => 100,
                                   self::CFG_TIMEOUT           => 300,
                                   self::CFG_COOKIE_NAME       => 'AloSession',
                                   self::CFG_FINGERPRINT_NAME  => '_fp_',
                                   self::CFG_PREFIX            => '_alo_sess_',
                                   self::CFG_SESSION_ID_ALGO => 'sha512',
                                   self::CFG_SECURE            => true];
            }
        }
    }
