<?php
session_start(); // starts/gets the active session
session_unset(); // good practice to clear session variables
session_destroy(); // destroys the session on the server
header("Location: login.php"); // redirect back to login