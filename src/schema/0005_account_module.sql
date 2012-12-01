-- Table: Account Urls (account_a)
DROP TABLE IF EXISTS `account_account`;
CREATE TABLE `account_account` (
  `account_a_id` INT AUTO_INCREMENT NOT NULL,
  `account_m_id` VARCHAR(32) NOT NULL,
  `account_a_name` VARCHAR(32) NOT NULL,
  `account_a_blz` VARCHAR(10) NOT NULL,
  `account_a_knr` VARCHAR(10) NOT NULL,
  `account_a_pin` VARCHAR(10) NOT NULL,
  `changed` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`account_a_id`)
) ENGINE=InnoDB DEFAULT CHARSET=ascii;
