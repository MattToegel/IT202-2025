<?php
function generate_roman_numeral(int $number) {
    $map = [
        'M' => 1000,
        'CM' => 900,
        'D' => 500,
        'CD' => 400,
        'C' => 100,
        'XC' => 90,
        'L' => 50,
        'XL' => 40,
        'X' => 10,
        'IX' => 9,
        'V' => 5,
        'IV' => 4,
        'I' => 1,
    ];
    $result = '';
    foreach ($map as $roman => $value) {
        while ($number >= $value) {
            $result .= $roman;
            $number -= $value;
        }
    }
    return $result;
}

function generate_broker_name() {
    $titles = [
        "The Bullish",
        "The Bearish",
        "Chief Broker",
        "Broker",
        "Arbitrator",
        "Daytrader",
        "Shadow of Wall Street",
        "Duke",
        "Duchess",
        "Commodore",
        "The Insider",
        "The Analyst",
        "The Quant",
        "Rogue Trader",
        "The Speculator",
        "Hedge Priest",
        "Hedge Witch"
    ];

    $firstNames = [
        "Bulliam",
        "Bearnie",
        "Shorton",
        "Cashius",
        "Bondrew",
        "Quantin",
        "Levera",
        "Inflayla",
        "Crypteon",
        "Divina",
        "Satoshi",
        "Riskiya",
        "Fiscara",
        "Yieldwin",
        "Buffman",
        "Longevin"
    ];

    $lastNames = [
        "McMargin",
        "D’Value",
        "Tenderson",
        "Gainsworth",
        "Chartier",
        "Stoploss",
        "DeCoin",
        "Volatyle",
        "Portefeuille",
        "DeRivus",
        "Liqwick",
        "Bankrow",
        "Pumpfield",
        "Hedgely",
        "Stonkmore"
    ];

    $suffixes = [
        "the Leveraged",
        "of Margins",
        "of the Dow",
        "Slayer of Shorts",
        "Whisperer of Charts",
        "from the Fed",
        "the Insider",
        "Lord of Limit Orders",
        "of the Black Candle",
        "of the Dip",
        "the Liquidator",
        "Seer of Signals",
        "the Whale",
        "Master of Derivatives",
        "from Main Street",
        "of Stonks Past",
        "the Bagholder"
    ];

    // Randomize components=

    $title = rand(0, 1) ? $titles[array_rand($titles)] : null;
    $first = $firstNames[array_rand($firstNames)];
    $last = $lastNames[array_rand($lastNames)];
    $suffix = rand(0, 1) ? $suffixes[array_rand($suffixes)] : null;

    // TODO: Do this outside of this function since the name is needed first
    // Roman numeral based on existing count (0-indexed)
    //$roman = generateRomanNumeral($existingCount + 1);

    // Assemble name
    $parts = [];
    if ($title) $parts[] = $title;
    $parts[] = $first;
    $parts[] = $last;
    if ($suffix) $parts[] = $suffix;

    return implode(' ', $parts);
}

function generate_broker($_rarity = null) {
    //TODO: Probably should enforce incoming $_rarity to abide by 1-5 range
    // At this moment chose not to in case I wanted special brokers created
    if ($_rarity != null) {
        if (!is_int($_rarity) && !is_numeric($_rarity)) {
            error_log("generate_broker() reset parameter to null since wasn't a number: " . var_export($_rarity, true));
            $_rarity = null;
        }
    }
    $rarity = $_rarity == null ? rand(1, 5) : $_rarity;
    $name = generate_broker_name();
    $similar = 0;
    // check for possible duplicate names
    try {
        $db = getDB();
        $stmt = $db->prepare("SELECT count(1) as similar FROM `IT202-M25-Brokers` WHERE name like :name");
        $stmt->execute([":name" => "%$name%"]);
        $r = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($r) {
            $similar = $r["similar"];
        }
    } catch (PDOException $e) {
        error_log("Error fetching record: " . var_export($e, true));
        flash("Error fetching record", "danger");
    }
    if ($similar > 0) {
        $name .= " " . generate_roman_numeral($similar + 1);
    }
    // fetch up to x random stocks
    $stocks = [];
    try {
        $db = getDB();
        // $rarity is generated so should be safe to inject
        // Select only one row per unique symbol before randomizing and limiting
        $subquery = "(SELECT * FROM `IT202-M25-Stocks` GROUP BY symbol ORDER BY id DESC)";
        $stmt = $db->prepare("SELECT id, symbol, price, volume, low, high, '1' as shares FROM $subquery AS t ORDER BY RAND() LIMIT $rarity");
        $stmt->execute();
        $r = $stmt->fetchAll(PDO::FETCH_ASSOC);
        if ($r) {
            $stocks = $r;
        } else {
            throw new Exception("No stocks found for broker generation");
        }
    } catch (PDOException $e) {
        error_log("Error fetching record: " . var_export($e, true));
        flash("Error fetching record", "danger");
    }

    // fetch base stats and modifiers
    $broker = calculate_stats($rarity, $stocks);
    $broker["name"] = $name;
    $broker["rarity"] = $rarity;

    // insert into db
    $db = getDB();
    $stmt = $db->prepare("INSERT INTO `IT202-M25-Brokers` (name, rarity, life, attack, defense, power) 
    VALUES (:name, :rarity, :life, :attack, :defense, :power)");
    foreach ($broker as $key => $value) {
        $type = is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR;
        $stmt->bindValue(":$key", $value, $type);
    }
    // set stocks after insert to keep it dynamic
    $broker["stocks"] = $stocks;
    try {
        $stmt->execute();
        $id = $db->lastInsertId();
        $broker["id"] = $id;
        if ($id) {
            // insert stocks into db
            foreach ($stocks as $stock) {
                $stmt = $db->prepare("INSERT INTO `IT202-M25-BrokerStocks` (broker_id, stock_id, shares) VALUES (:broker_id, :stock_id,1)");
                $stmt->bindValue(":broker_id", $id, PDO::PARAM_INT);
                $stmt->bindValue(":stock_id", $stock["id"], PDO::PARAM_INT);
                $stmt->execute();
            }
        } else {
            throw new Exception("Failed to insert broker into database");
        }
    } catch (PDOException $e) {
        error_log("Error inserting record: " . var_export($broker, true) . "\n" . var_export($e, true));
        flash("Error inserting record", "danger");
    }
    return $broker;
}

function calculate_stat($base, $stocks, $type, $mod) {
    $bonus = 0;
    foreach ($stocks as $stock) {
        $q = max(1, (int)($stock['quantity'] ?? 1));
        $p = max(0.01, (float)$stock['price']);   // avoid div-by-zero
        $v = max(0, (float)$stock['volume']);
        $h = (float)$stock['high'];
        $l = (float)$stock['low'];

        switch ($type) {
            case 'life':
                $volBonus = $v / 1_000_000;
                $volatility = max(0, ($h - $l) / $p);
                $bonus += $q * ($volBonus + $volatility * $mod);
                break;

            case 'attack':
                $spike = max(0, ($h - $p) / $p); // prevent negative spike
                $bonus += $q * $spike * $mod;
                break;

            case 'defense':
                $stability = max(0, ($p - $l) / $p); // prevent negative defense
                $bonus += $q * $stability * $mod;
                break;
        }
    }

    $final = $base + max(0, $bonus); // ensure always positive
    return max(1, ceil($final));           // optional: force stat floor of 1
}


function calculate_stats($rarity, $stocks) {
    $db = getDB();
    $stmt = $db->prepare("SELECT property, value FROM `IT202-M25-BrokerProperties`
        WHERE property in ('base_life', 'base_attack', 'base_defense', 'mod_life', 'mod_attack', 'mod_defense')");
    $stmt->execute();
    $r = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $base = [];
    if ($r) {
        foreach ($r as $row) {
            $base[$row["property"]] = $row["value"];
        }
    } else {
        throw new Exception("No base properties found for broker generation");
    }
    $life = calculate_stat($base["base_life"], $stocks, "life", $base["mod_life"]);
    $attack = calculate_stat($base["base_attack"], $stocks, "attack", $base["mod_attack"]);
    $defense = calculate_stat($base["base_defense"], $stocks, "defense", $base["mod_defense"]);
    $power = ceil(sqrt($life) * ($attack + $defense) * (1 + 0.1 * $rarity));
    $broker = [
        //"name" => $name,
        "rarity" => $rarity,
        "life" => $life,
        "attack" => $attack,
        "defense" => $defense,
        "power" => $power

    ];
    return $broker;
}

function refresh_broker($id) {
    //fetch broker
    $broker = [];
    $db = getDB();
    $query = "SELECT id, name, rarity, life, attack, defense FROM `IT202-M25-Brokers` WHERE id = :id";
    try {
        $stmt = $db->prepare($query);
        $stmt->execute([":id" => $id]);
        $r = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($r) {
            $broker = $r;
        } else {
            throw new Exception("No broker found for id: $id");
        }
    } catch (PDOException $e) {
        error_log("Error fetching record: " . var_export($e, true));
        flash("Error fetching record", "danger");
    }

    // fetch stocks
    $stocks = [];
    $query = "SELECT stock_id, quantity, price, volume, low, high FROM `IT202-M25-BrokerStocks` bs join `IT202-M25-Stocks` s ON bs.stock_id = s.id  
    WHERE broker_id = :id";
    try {
        $stmt = $db->prepare($query);
        $stmt->execute([":id" => $id]);
        $r = $stmt->fetchAll(PDO::FETCH_ASSOC);
        if ($r) {
            foreach ($r as $row) {
                $stocks[] = $row;
            }
        } else {
            throw new Exception("No stocks found for broker id: $id");
        }
    } catch (PDOException $e) {
        error_log("Error fetching record: " . var_export($e, true));
        flash("Error fetching record", "danger");
    }
    // calculate stats
    $updates = calculate_stats($broker["rarity"], $stocks);
    $needsUpdate = false;
    if ($updates["life"] != $broker["life"]) {
        $needsUpdate = true;
        $broker["life"] = $updates["life"];
    }
    if ($updates["attack"] != $broker["attack"]) {
        $needsUpdate = true;
        $broker["attack"] = $updates["attack"];
    }
    if ($updates["defense"] != $broker["defense"]) {
        $needsUpdate = true;
        $broker["defense"] = $updates["defense"];
    }
    if ($updates["power"] != $broker["power"]) {
        $needsUpdate = true;
        $broker["power"] = $updates["power"];
    }
    if ($needsUpdate) {
        // update db
        $stmt = $db->prepare("UPDATE `IT202-M25-Brokers` SET life = :life, attack = :attack, defense = :defense, power = :power WHERE id = :id");
        $stmt->bindValue(":life", $broker["life"], PDO::PARAM_INT);
        $stmt->bindValue(":attack", $broker["attack"], PDO::PARAM_INT);
        $stmt->bindValue(":defense", $broker["defense"], PDO::PARAM_INT);
        $stmt->bindValue(":power", $broker["power"], PDO::PARAM_INT);
        $stmt->bindValue(":id", $id, PDO::PARAM_INT);
        try {
            $stmt->execute();
            flash("Updated broker stats", "success");
        } catch (PDOException $e) {
            error_log("Error updating record: " . var_export($e, true));
            flash("Error updating record", "danger");
        }
    }
    return $broker;
}
