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
    }
