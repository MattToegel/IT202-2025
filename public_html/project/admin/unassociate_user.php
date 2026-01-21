<?php
require(__DIR__."/../../../lib/functions.php");
session_start();
if(!has_role("Admin")){
    flash("You don't have permission to do this", "warning");
    redirect($_SESSION["last"]?? "brokers.php");

}
$user_id = $_GET["id"]??get_user_id();// only logged in user

if($user_id){
    $db = getDB();
    $query = "DELETE FROM `IT202-S25-UserBrokers` WHERE user_id = :user_id";
    try{
        $stmt = $db->prepare($query);
        $stmt->execute([":user_id"=>$user_id]);
        flash("Deleted all user's associations", "success");
    }
    catch(PDOException $e){
        error_log("Error deleting associations: " . var_export($e));
        flash("Error deleting user associations", "danger");
    }
}
redirect($_SESSION["last"]?? "brokers.php");