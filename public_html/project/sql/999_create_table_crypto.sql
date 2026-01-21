CREATE TABLE `IT202-S25-Crypto` (
  `id` int NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `currency_symbol` varchar(6) NOT NULL,
  `currency_name` varchar(20) NOT NULL,
  `average` decimal(9,2) NOT NULL,
  `date` DATE NOT NULL,
  `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `is_api` tinyint(1) DEFAULT '1',
  UNIQUE KEY (`currency_symbol`, `date`)
)