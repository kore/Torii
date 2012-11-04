-- Table: Calendar Module (calendar_m)
DROP TABLE IF EXISTS `calendar_module`;
CREATE TABLE `calendar_module` (
  `calendar_m_id` VARCHAR(32) NOT NULL,
  `calendar_m_settings` TEXT NOT NULL,
  `changed` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`calendar_m_id`)
) ENGINE=InnoDB DEFAULT CHARSET=ascii;

-- Table: Calendar Urls (calendar_u)
DROP TABLE IF EXISTS `calendar_url`;
CREATE TABLE `calendar_url` (
  `calendar_u_id` INT AUTO_INCREMENT NOT NULL,
  `calendar_m_id` VARCHAR(32) NOT NULL,
  `calendar_u_url` VARCHAR(4096) NOT NULL,
  `calendar_u_update` BIGINT NOT NULL,
  `calendar_u_status` INT NOT NULL,
  `changed` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`calendar_u_id`),
  KEY(`calendar_u_url`),
  KEY(`calendar_u_update`)
) ENGINE=InnoDB DEFAULT CHARSET=ascii;
