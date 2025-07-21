CREATE TABLE `IT202-M25-UserStats` (
    `id` int NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `user_id` int NOT NULL UNIQUE,
    `wins` int NOT NULL DEFAULT 0,
    `losses` int NOT NULL DEFAULT 0,
    `points` int NOT NULL DEFAULT 0,
    `brokers` int NOT NULL DEFAULT 0,
    `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`user_id`) REFERENCES `Users` (`id`) ON DELETE CASCADE,
    check (`points` >= 0)
);