<?php

/**
 * Inserts a single record or multiple records into a specified database table.
 * Throws exceptions for invalid input or database errors.
 * Important: Intentionally doesn't support JSON fields, only basic data types.
 *
 * @param string $table_name The sanitized name of the database table.
 * @param array $data Either:
 * - A single associative array: ['column1' => 'value1', 'column2' => 'value2']
 * - Or an array of associative arrays for bulk insert:
 *   [
 *     ['column1' => 'value1', 'column2' => 'value2'],
 *     ['column1' => 'value3', 'column2' => 'value4']
 *   ]
 * @param array $opts Options including 'debug' flag, 'update_duplicate', and 'columns_to_update'.
 * @return array The last insert ID and number of rows affected for insert.
 * @throws InvalidArgumentException If input data is not valid.
 * @throws Exception For database-related errors.
 * 
 * @author Matt Toegel
 * @version 0.3 04/12/2025
 */
function insert($table_name, $data, $opts = ["debug" => false, "update_duplicate" => false, "columns_to_update" => []])
{
    if (!is_array($data)) {
        throw new InvalidArgumentException("Data must be an array");
    }
    if (empty($data)) {
        throw new InvalidArgumentException("Data cannot be empty");
    }
    if (empty($table_name)) {
        throw new InvalidArgumentException("Table name cannot be empty");
    }
    if (!is_string($table_name)) {
        throw new InvalidArgumentException("Table name must be a string");
    }

    $is_debug = isset($opts["debug"]) && $opts["debug"];
    $update_duplicate = isset($opts["update_duplicate"]) && $opts["update_duplicate"];
    $columns_to_update = isset($opts["columns_to_update"]) ? $opts["columns_to_update"] : [];
    $table_name_escaped = "`" . str_replace("`", "``", $table_name) . "`";

    //Normalize data to always be a list of associative arrays
    $is_indexed = array_keys($data) === range(0, count($data) - 1);
    $records = $is_indexed ? $data : [$data];

    // Validate structure of each record
    foreach ($records as $i => $record) {
        if (!is_array($record)) {
            throw new Exception("Each item in the data array must be an associative array.");
        }
        if (array_keys($record) === range(0, count($record) - 1)) {
            throw new Exception("Each record must be an associative array (not indexed).");
        }
        foreach ($record as $key => $value) {
            if (!is_string($key)) {
                throw new Exception("Record keys must be strings.");
            }
            if (is_array($value) || is_object($value)) {
                throw new Exception("Record values must be basic data types.");
            }
        }
    }

    // Extract and sort keys for consistency
    $sortedKeys = array_keys($records[0]);
    sort($sortedKeys);
    $escapedKeys = array_map(fn($key) => "`" . str_replace("`", "``", $key) . "`", $sortedKeys);
    $columns = join(", ", $escapedKeys);
    // Verify record consistency
    foreach ($records as $i => $record) {
        $recordKeys = array_keys($record);
        sort($recordKeys);
        if ($recordKeys !== $sortedKeys) {
            throw new InvalidArgumentException("All records must have the same keys in the same order");
        }
    }
    $valuesClause = [];
    $updateClause = [];

    // Generate placeholders
    foreach ($records as $index => $record) {
        ksort($record);
        $placeholders = join(", ", array_map(fn($key) => ":{$key}_{$index}", array_keys($record)));
        $valuesClause[] = "($placeholders)";
    }

    $query = "INSERT INTO $table_name_escaped ($columns) VALUES " . join(", ", $valuesClause);
    // Handle duplicates if enabled
    if ($update_duplicate) {
        if (empty($columns_to_update)) {
            $columns_to_update = $sortedKeys;
        }
        $invalidColumns = array_diff($columns_to_update, $sortedKeys);
        if (!empty($invalidColumns)) {
            throw new InvalidArgumentException("Invalid columns in columns_to_update: " . implode(", ", $invalidColumns));
        }
        foreach ($columns_to_update as $column) {
            $escaped = "`" . str_replace("`", "``", $column) . "`";
            $updateClause[] = "$escaped = VALUES($escaped)";
        }
        $query .= " ON DUPLICATE KEY UPDATE " . join(", ", $updateClause);
    }

    $db = getDB();
    $stmt = $db->prepare($query);
    if ($is_debug) {
        error_log("Query: " . $query);
    }

    try {
        // Unified binding logic
        foreach ($records as $index => $record) {
            foreach ($record as $key => $value) {
                // determine type for binding
                $type = match (true) {
                    is_int($value)   => PDO::PARAM_INT,
                    is_bool($value)  => PDO::PARAM_BOOL,
                    is_null($value)  => PDO::PARAM_NULL,
                    default          => PDO::PARAM_STR,
                };
                // bind under key and index (for multiple records)
                $stmt->bindValue(":{$key}_{$index}", $value, $type);
                if ($is_debug) {
                    error_log("Binding value for :{$key}_{$index}: " . var_export($value, true));
                }
            }
        }
        $stmt->execute();
        return ["rowCount" => $stmt->rowCount(), "lastInsertId" => $db->lastInsertId()];
    } catch (PDOException $e) {
        throw $e;
    } catch (Exception $e) {
        throw $e;
    }
}
/**
 * Updates a record in a specified database table using values from the given data.
 * Automatically builds the WHERE clause based on a key or keys (default: ['id']).
 * Throws exceptions for invalid input or database errors.
 *
 * Example:
 * update('users', [
 *     'id' => 5,
 *     'email' => 'new@example.com',
 * ]);
 *
 * Resulting SQL:
 * UPDATE `users` SET `email` = :set_email WHERE `id` = :where_id
 *
 * @param string $table_name The name of the table to update.
 * @param array $data An associative array of data containing:
 *   - Keys to update (e.g., 'email', 'status')
 *   - Key(s) for the WHERE clause (default: ['id'])
 *     Example:
 *     [
 *       'id' => 42,                 // used in WHERE clause
 *       'email' => 'new@site.com', // used in SET clause
 *     ]
 * @param array $whereKeys One or more keys in $data that will be used in the WHERE clause (default: ['id']).
 * @param array $opts Options including 'debug' => true for logging SQL and bindings.
 * @return array ['rowCount' => int] Number of rows affected.
 * @throws InvalidArgumentException If input data or keys are invalid.
 * @throws Exception For database-related errors.
 */
function update(
    string $table_name,
    array $data,
    array $whereKeys = ["id"],
    array $opts = ["debug" => false]
): array
{
    if (empty($table_name)) {
        throw new InvalidArgumentException("Table name cannot be empty");
    }
    if (!is_array($data) || empty($data)) {
        throw new InvalidArgumentException("Data must be a non-empty array");
    }

    if (empty($whereKeys)) {
        throw new InvalidArgumentException("You must provide at least one key for the WHERE clause");
    }

    $is_debug = $opts["debug"] ?? false;
    $table_name_escaped = "`" . str_replace("`", "``", $table_name) . "`";

    $setClause = [];
    $whereClause = [];
    $bindings = [];

    foreach ($data as $key => $value) {
        if (!is_string($key)) {
            throw new InvalidArgumentException("All keys in data must be strings");
        }

        $escaped = "`" . str_replace("`", "``", $key) . "`";
        $paramName = in_array($key, $whereKeys, true) ? "where_$key" : "set_$key";

        if (in_array($key, $whereKeys, true)) {
            $whereClause[] = "$escaped = :$paramName";
        } else {
            $setClause[] = "$escaped = :$paramName";
        }

        $bindings[$paramName] = $value;
    }

    if (empty($setClause)) {
        throw new InvalidArgumentException("No fields left to update after excluding WHERE keys");
    }

    $query = "UPDATE $table_name_escaped SET " . join(", ", $setClause) . " WHERE " . join(" AND ", $whereClause);

    $db = getDB();
    $stmt = $db->prepare($query);

    if ($is_debug) {
        error_log("Query: " . $query);
    }

    foreach ($bindings as $param => $value) {
        $type = match (true) {
            is_int($value)   => PDO::PARAM_INT,
            is_bool($value)  => PDO::PARAM_BOOL,
            is_null($value)  => PDO::PARAM_NULL,
            default          => PDO::PARAM_STR,
        };
        $stmt->bindValue(":$param", $value, $type);
        if ($is_debug) {
            error_log("Binding :$param = " . var_export($value, true));
        }
    }

    try {
        $stmt->execute();
        return ["rowCount" => $stmt->rowCount()];
    } catch (PDOException $e) {
        throw $e;
    } catch (Exception $e) {
        throw $e;
    }
}