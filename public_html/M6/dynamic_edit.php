<?php
require("nav.php");

$id = se($_GET, "id", -1, false);
if ($id === -1) {
    echo 'You must pass a query parameter ($_GET) of the id you want to work with.';
    return;
}
$table_name = "Samples"; //TODO change table name to test others you have (don't make this user-entered)
$db = getDB();
//check if there's a change (remember, save the change, then select the fresh data, if this is swapped you'll get stale data)
if (isset($_POST["submit"])) {
    $columns = array_keys($_POST);
    foreach ($columns as $index => $value) {
        //Note: normally it's bad practice to remove array elements during iteration

        //remove id, we'll use this for the WHERE not for the SET
        //remove submit, it's likely not in your table
        if ($value === "id" || $value === "submit") {
            unset($columns[$index]);
        }
    }
    // echo "<pre>" . var_export($columns, true) . "</pre>";
    $query = "UPDATE `$table_name` SET "; 
    $total = count($columns);
    foreach ($columns as $index => $col) {
        $query .= "$col = :$col";
        if ($index < $total) {
            $query .= ", ";
        }
    }
    $query .= " WHERE id = :id";

    $params = [":id" => $_GET["id"]];
    foreach ($columns as $col) {
        // potential SQL injection opportunity, use a whitelist to skip invalid keys
        $params[":$col"] = se($_POST, $col, "", false);
    }
    echo var_export($query, true);
    echo "<br>";
    echo var_export($params, true);
    echo "<br>";
    $stmt = $db->prepare($query);
    try {
        $stmt->execute($params);
        echo "Successfully updated record";
    } catch (PDOException $e) {
        echo "<pre>" . var_export($e->errorInfo, true) . "</pre>";
    }
}
// fetch table definition
$query = "SHOW COLUMNS from `$table_name`"; 
$stmt = $db->prepare($query);
$columns = [];
try {
    $stmt->execute();
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "<pre>" . var_export($e, true) . "</pre>";
}

//select the id and only editable fields
$query = "SELECT * from `$table_name` where id = :id";
$stmt = $db->prepare($query);
$result = [];
try {
    $stmt->execute([":id" => $id]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "<pre>" . var_export($e, true) . "</pre>";
}
$remove_columns = ["created", "modified"];
// remove columns that are not editable
foreach ($remove_columns as $col) {
    if (isset($result[$col])) {
        unset($result[$col]);
    }
}

// utilities
// Despite these utilities, notice the behavior of checkboxes
function get_input_type($column_name) {
    $type = get_column_type($column_name);
    if (!$type) {
        return "text"; // default type
    }
    return map_sql_type_to_input_type($type);
}
function get_column_type($column_name) {
    global $columns;
    foreach ($columns as $col) {
        if (isset($col['Field']) && $col['Field'] === $column_name) {
            return isset($col['Type']) ? $col['Type'] : null;
        }
    }
    // echo "<!-- Column $column_name not found in table definition -->";// debugging
    return null;
}
function map_sql_type_to_input_type($sql_type) {
    $type = strtolower($sql_type);
    //echo "<!-- Type: $type -->";// debugging
    if (strpos($type, "int") !== false) {
        if($type === "tinyint(1)"){
            return "checkbox"; // special case for boolean
        }
        return "number";
    }
    
    if (strpos($type, "bool") !== false) {
        return "checkbox";
    }
    if (strpos($type, "date") !== false && strpos($type, "time") === false) {
        return "date";
    }
    if (strpos($type, "datetime") !== false || strpos($type, "timestamp") !== false) {
        return "datetime-local";
    }
    if (strpos($type, "time") !== false && strpos($type, "date") === false) {
        return "time";
    }
    if (strpos($type, "text") !== false) {
        return "textarea";
    }
    if (strpos($type, "float") !== false || strpos($type, "double") !== false || strpos($type, "decimal") !== false) {
        return "number";
    }
    return "text";
}

?>
<h3>Edit Sample</h3>
<form method="post">
    <?php if (!$result) : ?>
        <p>No record found</p>
    <?php else : ?>
        <?php foreach ($result as $column => $value) : ?>
            <?php if ($column === "id") : ?>
                <?php /* we have a choice here. We can either make a hidden 
            field so id is in $_POST, or we can use the $_GET variable defined in the url that was used for the lookup */ ?>
                <input type="hidden" name="id" value="<?php se($value); ?>" />
            <?php else : ?>
                <label for="<?php se($column); ?>"><?php se($column); ?></label>
                <input id="<?php se($column); ?>" type="<?php echo get_input_type($column);?>" name="<?php se($column); ?>" value="<?php se($value); ?>" />
            <?php endif; ?>
        <?php endforeach; ?>
        <input type="submit" value="Save" name="submit" />
    <?php endif; ?>
</form>