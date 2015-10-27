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

DROP EVENT IF EXISTS `clean_alo_session`;
DELIMITER $$
CREATE EVENT `clean_alo_session`
  ON SCHEDULE EVERY 60 SECOND
  ON COMPLETION PRESERVE
ENABLE
DO
  DELETE FROM `alo_session`
  WHERE DATE_ADD(`access`, INTERVAL 300 SECOND) >= NOW()$$
DELIMITER ;
