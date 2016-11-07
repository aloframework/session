# AloFramework | Sessions #

MySQL & Redis-based session management

Latest release API documentation: [https://aloframework.github.io/session/](https://aloframework.github.io/session/)

![License](https://poser.pugx.org/aloframework/session/license?format=plastic)
[![Latest Stable Version](https://poser.pugx.org/aloframework/session/v/stable?format=plastic)](https://packagist.org/packages/aloframework/session)
[![Total Downloads](https://poser.pugx.org/aloframework/session/downloads?format=plastic)](https://packagist.org/packages/aloframework/session)

|                                                                                         dev-develop                                                                                         |                                                                                   Latest release                                                                                   |
|:-------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------:|:----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------:|
|                              [![Dev Build Status](https://travis-ci.org/aloframework/session.svg?branch=develop)](https://travis-ci.org/aloframework/session)                             |                        [![Release Build Status](https://travis-ci.org/aloframework/session.svg?branch=master)](https://travis-ci.org/aloframework/session)                       |
| [![Coverage Status](https://coveralls.io/repos/aloframework/session/badge.svg?branch=develop&amp;service=github)](https://coveralls.io/github/aloframework/session?branch=develop)        | [![Coverage Status](https://coveralls.io/repos/aloframework/session/badge.svg?branch=master&amp;service=github)](https://coveralls.io/github/aloframework/session?branch=master) |

## Installation ##
Installation is available via Composer:

    composer require aloframework/session

### Additional steps for MySQL ###
MySQL-based sessions require an additional step which is described in [setup/MySQL.md](https://github.com/aloframework/session/blob/master/setup/MySQL.md).

## Usage ##
All sessions use the same interface (bar the constructor), in this example Redis will be used.

```php
<?php
    
    use AloFramework\Session\RedisSession;
    
    //Make our Redis connection
    $redis = new Redis();
    $redis->connect('127.0.0.1');
    
    //Start our session. The redis parameter can be omitted, in which case the code above will be run automatically
    // within the class
    $sess = (new RedisSession($redis))->start();
    
    //That's it - you can now use the handler just like you would use a regular PHP session.
    $_SESSION['foo'] = 'bar';
    unset($_SESSION['qux']);
    echo $_SESSION['baz'];
    
    //Additionally, you can work directly with the RedisSession object via the ArrayAccess interface and magic
    // getter+setter:
    $sess->foo   = 'bar';
    $sess['foo'] = 'bar';
    unset($sess['foo']);
    echo $sess->foo;
    echo $_SESSION['foo'];
```

### Logging ###
An instance of `\Psr\Log\LoggerInterface` should be passed on to the constructor to make use of basic logging (almost everything is debug-level). If one isn't passed on, an instance of `\AloFramework\Log\Log` will be created with default parameters.

## Configuration ##
Configuration is done via the [Configuration class](https://github.com/aloframework/config).

 - `Config::CFG_TIMEOUT` - session lifetime in seconds (defaults to 300)
 - `Config::CFG_COOKIE_NAME` - how the session cookie will be named (defaults to AloSession)
 - `Config::CFG_FINGERPRINT_NAME` - the session key which will hold the session-hijacking prevention fingerprint. You cannot set any session keys with the same name as that would invalidate the session. Defaults to \_fp_.
 - `Config::CFG_PREFIX` - how to prefix session keys if using cache-based handlers. Defaults to \_alo_sess_.
 - `Config::CFG_SESSION_ID_ALGO` - hashing algorithm to use for session IDs. Defaults to sha512.
 - `Config::CFG_TABLE` - table to use if using MySQL-based handlers. Defaults to alo_session.
 - `Config::CFG_SECURE` - if set to true, the session cookie will only be sent via HTTPS connections (defaults to `true`).
 - `Config::CFG_GC` - garbage collection probability. If set to 100 (default) there is a 1/100 (i.e. 1% chance) that a garbage collection event will occur on session start. **This is only used with `MySQLNoEventSession`**.
 - `Config::CFG_SAVE_CLI` - whether to save/write session data in CLI mode (default: false)
 - `Config::CFG_TOKEN` - The session key to identify token data. You must not set any session values using this key as that would invalidate the tokens. Defaults to \_tk_.
