# MySQL setup #

This setup is only valid for the default configuration; if you change the table from `alo_session` to something else you will need to overwrite the default config when starting a MySQL session. You also need to make sure that the `INTERVAL` is **not lower than your cookie lifetime**.

The following schema needs to be implemented in order to use MySQL-based sessions:

    -- Create the session table
    DROP TABLE IF EXISTS `alo_session`;
    CREATE TABLE `alo_session` (
      `id`     CHAR(128)
               CHARACTER SET ascii NOT NULL,
      `data`   TEXT                NOT NULL,
      `access` TIMESTAMP           NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
      PRIMARY KEY (`id`),
      KEY `access` (`access`)
    )
      ENGINE = InnoDB
      DEFAULT CHARSET = utf8mb4;
    
    -- Create the garbage collection event
    DROP EVENT IF EXISTS `clean_alo_session`;
    DELIMITER $$
    CREATE EVENT `clean_alo_session`
      ON SCHEDULE EVERY 60 SECOND
      ON COMPLETION PRESERVE
    ENABLE
    DO
      DELETE FROM `alo_session`
      WHERE DATE_ADD(`access`, INTERVAL 300 SECOND) < NOW()$$
    DELIMITER ;

In addition, MySQL events must be turned on. If you cannot do this, please use the `MySQLNoEventSession` class instead.
