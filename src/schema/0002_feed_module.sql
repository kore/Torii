-- Table: Feed Module (f_m)
DROP TABLE IF EXISTS `feed_module`;
CREATE TABLE `feed_module` (
  `f_m_id` VARCHAR(32) NOT NULL,
  `u_id` INT NOT NULL,
  `f_m_settings` TEXT NOT NULL,
  `changed` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`f_m_id`),
  KEY(`u_id`)
) ENGINE=InnoDB DEFAULT CHARSET=ascii;

-- Table: Feed Module Url relation table
DROP TABLE IF EXISTS `f_m_u_rel`;
CREATE TABLE `f_m_u_rel` (
  `f_m_id` INT AUTO_INCREMENT NOT NULL,
  `f_u_id` INT NOT NULL,
  `changed` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`f_m_id`, `f_u_id`)
) ENGINE=InnoDB DEFAULT CHARSET=ascii;

-- Table: Feed Urls (f_u)
DROP TABLE IF EXISTS `feed_url`;
CREATE TABLE `feed_url` (
  `f_u_id` INT AUTO_INCREMENT NOT NULL,
  `f_u_url` VARCHAR(255) NOT NULL,
  `f_u_update` BIGINT NOT NULL,
  `f_u_status` INT NOT NULL,
  `changed` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`f_u_id`),
  KEY(`f_u_update`)
) ENGINE=InnoDB DEFAULT CHARSET=ascii;

-- Table: Feed Data (f_d)
DROP TABLE IF EXISTS `feed_data`;
CREATE TABLE `feed_data` (
  `f_u_id` INT AUTO_INCREMENT NOT NULL,
  `f_d_time` BIGINT NOT NULL,
  `f_d_data` TEXT NOT NULL,
  `changed` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`f_u_id`),
  KEY(`f_d_time`)
) ENGINE=InnoDB DEFAULT CHARSET=ascii;

