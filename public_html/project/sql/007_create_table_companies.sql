CREATE TABLE `IT202-M25-Companies` (
  `id` int NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `symbol` varchar(6) NOT NULL UNIQUE,
  `name` VARCHAR(100) NOT NULL UNIQUE,
  `type` varchar(30) NOT NULL,
  `region` varchar(50) NOT NULL,
  `currency` VARCHAR(4) NOT NULL,
  `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `is_api` tinyint(1) DEFAULT '1'
)