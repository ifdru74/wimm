delimiter $$

CREATE TABLE `m_budget` (
  `budget_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `budget_name` varchar(200) NOT NULL,
  `currency_id` int(10) unsigned NOT NULL,
  `open_date` datetime NOT NULL,
  `close_date` datetime DEFAULT NULL,
  `parent_id` int(10) unsigned NOT NULL,
  `user_id` int(10) unsigned NOT NULL,
  `security` varchar(45) NOT NULL,
  `budget_descr` varchar(200) DEFAULT ' ',
  PRIMARY KEY (`budget_id`),
  KEY `IDX_PARENT` (`parent_id`),
  KEY `FK_budget_2_usr` (`user_id`),
  CONSTRAINT `FK_budget_2_usr` FOREIGN KEY (`user_id`) REFERENCES `m_users` (`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8$$

delimiter $$

CREATE TABLE `m_currency` (
  `currency_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `currency_name` varchar(200) NOT NULL,
  `currency_abbr` varchar(10) NOT NULL,
  `currency_sign` varchar(10) CHARACTER SET latin1 NOT NULL,
  `open_date` datetime NOT NULL,
  `close_date` datetime DEFAULT NULL,
  `user_id` int(10) unsigned NOT NULL DEFAULT '1',
  `security` varchar(45) DEFAULT NULL,
  PRIMARY KEY (`currency_id`),
  KEY `currency_2_users` (`user_id`),
  CONSTRAINT `currency_2_users` FOREIGN KEY (`user_id`) REFERENCES `m_users` (`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8 COMMENT='Валюта'$$

delimiter $$

CREATE TABLE `m_currency_rate` (
  `currency_from` int(10) unsigned NOT NULL,
  `currency_to` int(10) unsigned NOT NULL,
  `exchange_rate_from` decimal(10,4) NOT NULL,
  `exchange_rate_to` decimal(10,4) NOT NULL,
  `open_date` datetime NOT NULL,
  `close_date` datetime DEFAULT NULL,
  `rate_bits` tinyint(1) NOT NULL,
  `place_id` int(10) unsigned NOT NULL DEFAULT '1',
  `user_id` int(10) unsigned NOT NULL DEFAULT '1',
  `security` varchar(45) DEFAULT NULL,
  PRIMARY KEY (`currency_from`,`currency_to`,`open_date`,`place_id`),
  KEY `FK_currency_rate_to` (`currency_to`),
  KEY `FK_currency_rate_usr` (`user_id`),
  KEY `FK_currency_rate_place` (`place_id`),
  CONSTRAINT `FK_currency_rate_from` FOREIGN KEY (`currency_from`) REFERENCES `m_currency` (`currency_id`),
  CONSTRAINT `FK_currency_rate_place` FOREIGN KEY (`place_id`) REFERENCES `m_places` (`place_id`),
  CONSTRAINT `FK_currency_rate_to` FOREIGN KEY (`currency_to`) REFERENCES `m_currency` (`currency_id`),
  CONSTRAINT `FK_currency_rate_usr` FOREIGN KEY (`user_id`) REFERENCES `m_users` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Курсы обмена'$$

delimiter $$

CREATE TABLE `m_loans` (
  `loan_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `place_id` int(11) unsigned NOT NULL COMMENT 'reference to m_places',
  `loan_name` varchar(128) NOT NULL COMMENT 'display name',
  `start_date` datetime NOT NULL COMMENT 'date when we have got money',
  `end_date` datetime DEFAULT NULL COMMENT 'date when we must return all money',
  `loan_rate` decimal(5,2) NOT NULL COMMENT 'how much does it costs (yearly)',
  `loan_type` int(11) NOT NULL COMMENT 'loan type 0 - ordinary bank loan with rate and end date, 1 - friend or family loan with end date and without rate',
  `open_date` datetime NOT NULL,
  `close_date` datetime DEFAULT NULL COMMENT 'date when we really returns all money',
  `user_id` int(11) unsigned NOT NULL,
  `loan_sum` decimal(12,2) DEFAULT '0.00' COMMENT 'summ total',
  `budget_id` int(10) NOT NULL COMMENT 'budget reference',
  `currency_id` int(10) NOT NULL COMMENT 'currency reference',
  PRIMARY KEY (`loan_id`),
  KEY `xloans2places` (`place_id`),
  KEY `xloans_dates` (`start_date`,`end_date`),
  KEY `xloans_closed` (`close_date`),
  KEY `x_loans_users` (`user_id`),
  KEY `fk_loans_users` (`user_id`),
  KEY `fk_loans_places` (`place_id`),
  CONSTRAINT `fk_loans_places` FOREIGN KEY (`place_id`) REFERENCES `m_places` (`place_id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `fk_loans_users` FOREIGN KEY (`user_id`) REFERENCES `m_users` (`user_id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COMMENT='loans issued for user'$$

delimiter $$

CREATE TABLE `m_olap` (
  `OLAP_ID` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Первичный ключ',
  `OLAP_DATE` datetime NOT NULL COMMENT 'Месяц, за который посчитана сумма',
  `MONEY_SUM` double NOT NULL DEFAULT '0' COMMENT 'Сумма',
  `alter_date` datetime NOT NULL COMMENT 'Дата обновления',
  PRIMARY KEY (`OLAP_ID`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=latin1 COMMENT='Переходящий остаток'$$

delimiter $$

CREATE TABLE `m_places` (
  `place_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `place_name` varchar(100) NOT NULL,
  `open_date` datetime NOT NULL,
  `close_date` datetime DEFAULT NULL,
  `place_descr` varchar(200) NOT NULL,
  `user_id` int(10) unsigned NOT NULL DEFAULT '1',
  `security` varchar(45) DEFAULT NULL,
  `place_bits` int(11) NOT NULL DEFAULT '0',
  `inn` varchar(12) DEFAULT NULL,
  PRIMARY KEY (`place_id`),
  KEY `FK_places_2_usr` (`user_id`),
  CONSTRAINT `FK_places_2_usr` FOREIGN KEY (`user_id`) REFERENCES `m_users` (`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=253 DEFAULT CHARSET=utf8 COMMENT='Места затрат'$$

delimiter $$

CREATE TABLE `m_transaction_types` (
  `t_type_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `t_type_name` varchar(200) NOT NULL,
  `parent_type_id` int(10) unsigned NOT NULL DEFAULT '1',
  `Type_sign` decimal(10,0) NOT NULL DEFAULT '0',
  `open_date` datetime NOT NULL,
  `close_date` datetime DEFAULT NULL,
  `type_bits` int(10) unsigned NOT NULL DEFAULT '0',
  `period` varchar(100) DEFAULT NULL,
  `user_id` int(10) unsigned NOT NULL DEFAULT '1',
  `security` varchar(45) DEFAULT NULL,
  PRIMARY KEY (`t_type_id`),
  KEY `IDX_PARENT` (`parent_type_id`),
  KEY `FK_ransaction_types_2_usr` (`user_id`),
  CONSTRAINT `FK_ransaction_types_2_usr` FOREIGN KEY (`user_id`) REFERENCES `m_users` (`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=40 DEFAULT CHARSET=utf8 COMMENT='transaction types'$$

delimiter $$

CREATE TABLE `m_transactions` (
  `transaction_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `transaction_name` varchar(45) NOT NULL,
  `t_type_id` int(10) unsigned NOT NULL DEFAULT '1',
  `currency_id` int(10) unsigned NOT NULL DEFAULT '1',
  `transaction_sum` decimal(10,2) NOT NULL DEFAULT '0.00',
  `transaction_date` datetime NOT NULL,
  `user_id` int(10) unsigned NOT NULL DEFAULT '1',
  `open_date` datetime NOT NULL,
  `close_date` datetime DEFAULT NULL,
  `place_id` int(10) unsigned NOT NULL DEFAULT '1',
  `budget_id` int(10) unsigned NOT NULL DEFAULT '1',
  PRIMARY KEY (`transaction_id`),
  KEY `FK_transactions_types` (`t_type_id`),
  KEY `FK_transactions_currency` (`currency_id`),
  KEY `FK_transactions_usr` (`user_id`),
  KEY `FK_transactions_2_place` (`place_id`),
  KEY `FK_transactions_budget` (`budget_id`),
  CONSTRAINT `FK_transactions_2_place` FOREIGN KEY (`place_id`) REFERENCES `m_places` (`place_id`),
  CONSTRAINT `FK_transactions_budget` FOREIGN KEY (`budget_id`) REFERENCES `m_budget` (`budget_id`),
  CONSTRAINT `FK_transactions_currency` FOREIGN KEY (`currency_id`) REFERENCES `m_currency` (`currency_id`),
  CONSTRAINT `FK_transactions_usr` FOREIGN KEY (`user_id`) REFERENCES `m_users` (`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=6196 DEFAULT CHARSET=utf8 COMMENT='Суммарные затраты'$$

delimiter $$

CREATE TABLE `m_users` (
  `user_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_name` varchar(200) NOT NULL,
  `user_login` varchar(45) NOT NULL,
  `user_password` varchar(50) NOT NULL,
  `open_date` datetime NOT NULL,
  `close_date` datetime DEFAULT NULL,
  `last_update` datetime NOT NULL,
  `security` varchar(45) NOT NULL,
  PRIMARY KEY (`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8 COMMENT='Пользователи'$$

