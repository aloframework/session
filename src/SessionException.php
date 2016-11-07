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
