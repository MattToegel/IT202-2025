<?php
require(__DIR__ . "/../../../lib/functions.php");
session_start();
if(isset($_GET["id"])){
    $db = getDB();
    $query = "INSERT into `IT202-S25-UserCrypto` (user_id, crypto_id) VALUES (:user_id, :crypto_id)";
    try {
        $stmt = $db->prepare($query);
        $stmt->bindValue(":user_id", get_user_id(), PDO::PARAM_INT);
        $stmt->bindValue(":crypto_id", se($_GET, "id", -1, false), PDO::PARAM_INT);
        $stmt->execute();
        if ($stmt->rowCount()) {
            flash("Added record", "success");
        } else {
            flash("Error adding record", "danger");
        }
    } catch (PDOException $e) {
        $query = "DELETE FROM `IT202-S25-UserCrypto` WHERE user_id = :user_id AND crypto_id = :crypto_id";
        $stmt = $db->prepare($query);
        $stmt->bindValue(":user_id", get_user_id(), PDO::PARAM_INT);
        $stmt->bindValue(":crypto_id", se($_GET, "id", -1, false), PDO::PARAM_INT);
        $stmt->execute();
        if ($stmt->rowCount()) {
            flash("Removed record", "success");
        } else {
            flash("Error removing record", "danger");
        }
    }
}
redirect($_SESSION["last"]?? "crypto.php");