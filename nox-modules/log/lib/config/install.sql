CREATE TABLE IF NOT EXISTS `log_table` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `user_id` int(6) NOT NULL,
  `date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `type` varchar(20) NOT NULL,
  `action` varchar(20) NOT NULL,
  `item_id` int(10) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`)
) ENGINE=MYISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;