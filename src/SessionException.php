<?php

    namespace AloFramework\Session;

    /**
     * Session exceptions
     * @author Art <a.molcanovas@gmail.com>
     */
    class SessionException extends \Exception {

        /**
         * Code when we're unable to connect to Redis
         * @var int
         */
        const E_REDIS_NO_CONNECT = 1;

        /**
         * Code when a critical security error occurs
         * @var int
         */
        const E_SECURITY_ERROR = 2;

        /**
         * A forwarding code for PDO exceptions
         * @var int
         */
        const E_PDO_FORWARD = 3;
    }
