<?php
// require functions.php to pull in flash()
require(__DIR__ . "/../../lib/functions.php");
session_start(); // starts/gets the active session
session_unset(); // good practice to clear session variables
session_destroy(); // destroys the session on the server
session_start(); // start a new session for flash to carry messages
flash("You have been logged out","success");
header("Location: login.php"); // redirect back to login