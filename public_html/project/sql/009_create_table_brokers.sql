CREATE TABLE `IT202-M25-Brokers` (
    `id` int NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `name` varchar(120) NOT NULL UNIQUE,
    `rarity` int NOT NULL COMMENT 'Rarity level (1-5)',
    `life` int NOT NULL COMMENT 'Health points',
    `attack` int NOT NULL COMMENT 'Damage output',
    `defense` int NOT NULL COMMENT 'Damage mitigation',
    `power` int NOT NULL COMMENT 'Combat rating',
    `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `is_active` tinyint(1) DEFAULT 1,
    check (`rarity` BETWEEN 1 AND 5),
    check (`life` >= 0),
    check (`attack` >= 0),
    check (`defense` >= 0),
    check (`power` >= 0)
);