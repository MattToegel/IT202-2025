INSERT INTO
    `IT202-M25-BrokerProperties` (`id`, `property`, `value`)
VALUES (-1, 'base_life', 10),
    (-2, 'base_attack', 10),
    (-3, 'base_defense', 10),
    (-4, 'mod_life', 10),
    (-5, 'mod_attack', 100),
    (-6, 'mod_defense', 50)
ON DUPLICATE KEY UPDATE
    `property` = VALUES(`property`),
    `value` = VALUES(`value`);