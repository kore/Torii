-- Table: Feed Module (feed_m)
DROP TABLE IF EXISTS `feed_module`;
CREATE TABLE `feed_module` (
  `feed_m_id` VARCHAR(32) NOT NULL,
  `feed_m_settings` TEXT NOT NULL,
  `changed` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`feed_m_id`)
) ENGINE=InnoDB DEFAULT CHARSET=ascii;

-- Table: Feed Module Url relation table
DROP TABLE IF EXISTS `feed_m_u_rel`;
CREATE TABLE `feed_m_u_rel` (
  `feed_m_id` VARCHAR(32) NOT NULL,
  `feed_u_id` INT NOT NULL,
  `changed` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY (`feed_m_id`, `feed_u_id`)
) ENGINE=InnoDB DEFAULT CHARSET=ascii;

-- Table: Feed Urls (feed_u)
DROP TABLE IF EXISTS `feed_url`;
CREATE TABLE `feed_url` (
  `feed_u_id` INT AUTO_INCREMENT NOT NULL,
  `feed_u_url` VARCHAR(4096) NOT NULL,
  `feed_u_update` BIGINT NOT NULL,
  `feed_u_status` INT NOT NULL,
  `changed` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`feed_u_id`),
  KEY(`feed_u_url`),
  KEY(`feed_u_update`)
) ENGINE=InnoDB DEFAULT CHARSET=ascii;

-- Table: Feed Data (feed_d)
DROP TABLE IF EXISTS `feed_data`;
CREATE TABLE `feed_data` (
  `feed_d_id` INT AUTO_INCREMENT NOT NULL,
  `feed_d_url` VARCHAR(64) NOT NULL,
  `feed_u_id` INT NOT NULL,
  `feed_d_time` BIGINT NOT NULL,
  `feed_d_data` TEXT NOT NULL,
  `changed` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`feed_d_id`),
  UNIQUE KEY (`feed_d_url`),
  KEY(`feed_d_time`)
) ENGINE=InnoDB DEFAULT CHARSET=ascii;

-- Table: Feed Module Data relation table
DROP TABLE IF EXISTS `feed_m_d_rel`;
CREATE TABLE `feed_m_d_rel` (
  `feed_m_id` VARCHAR(32) NOT NULL,
  `feed_d_id` INT NOT NULL,
  `feed_m_d_read` INT DEFAULT 0,
  `changed` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`feed_m_id`, `feed_d_id`),
  KEY (`feed_m_d_read`)
) ENGINE=InnoDB DEFAULT CHARSET=ascii;

