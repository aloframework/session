<?php

    namespace AloFramework\Session;

    use AloFramework\Config\AbstractConfig;

    /**
     * Configuration class
     * @author Art <a.molcanovas@gmail.com>
     * @since  1.1 saveCLI added
     *
     * @property int    $timeout          Session timeout
     * @property string $cookie           Session cookie name
     * @property string $fingerprint      Session fingerprint name. This is used to prevent session hijacking and you
     *           must not set any session values using this key.
     * @property string $prefix           How to prefix session keys if using cache engine-based handlers
     * @property bool   $secure           If set to true the session cookie will only be sent via HTTPS connections
     * @property string $sessionAlgo      Session ID generator hash algorithm
     * @property string $table            Which table to use if using SQL-based handlers
     * @property int    $gc               Garbage collection probability. If set to 100 (default) there is a 1/100
     *           (i.e. 1% chance) that a garbage collection event will occur on session start.
     * @property bool   $saveCLI          Whether to save sessions in CLI mode. Defaults to false.
     */
    class Config extends AbstractConfig {

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
         * Table to use if using SQL-based handlers
         * @var string
         */
        const CFG_TABLE = 'table';

        /**
         * Garbage collection probability. If set to 100 (default) there is a 1/100 (i.e. 1% chance) that a garbage
         * collection event will occur on session start.
         * @var string
         */
        const CFG_GC = 'gc';

        /**
         * Whether to save sessions in CLI mode. Defaults to false.
         * @var string
         * @since 1.1
         */
        const CFG_SAVE_CLI = 'saveCLI';

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
                self::$defaults = [self::CFG_TIMEOUT          => 300,
                                   self::CFG_COOKIE_NAME      => 'AloSession',
                                   self::CFG_FINGERPRINT_NAME => '_fp_',
                                   self::CFG_PREFIX           => '_alo_sess_',
                                   self::CFG_SESSION_ID_ALGO  => 'sha512',
                                   self::CFG_TABLE            => 'alo_session',
                                   self::CFG_SECURE           => true,
                                   self::CFG_GC               => 100,
                                   self::CFG_SAVE_CLI         => false];
            }
        }
    }
