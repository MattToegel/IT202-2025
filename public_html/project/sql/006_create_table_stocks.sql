CREATE TABLE `IT202-M25-Stocks` (
  `id` int NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `symbol` varchar(6) NOT NULL UNIQUE,
  `open` decimal(7,2) NOT NULL,
  `high` decimal(7,2) NOT NULL,
  `low` decimal(7,2) NOT NULL,
  `price` decimal(7,2) NOT NULL,
  `change_percent` decimal(3,2) NOT NULL,
  `volume` int NOT NULL,
  `latest_trading_day` date NOT NULL,
  `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `is_api` tinyint(1) DEFAULT '1'
)