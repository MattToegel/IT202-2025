<?php

/**
 * Sets a flash message in the session.
 * Flash messages are temporary messages that are stored in the session
 * and displayed to the user on the next page load.
 * @param string $msg The message text to display.
 * @param string $color The color of the message, defaults to "info".
 */
function flash($msg = "", $color = "info")
{
    if(session_status() !== PHP_SESSION_ACTIVE){
        error_log("Flash messages require an active session.");
        return;
    }
    $message = ["text" => $msg, "color" => $color];
    if (isset($_SESSION['flash'])) {
        array_push($_SESSION['flash'], $message);
    } else {
        $_SESSION['flash'] = array();
        array_push($_SESSION['flash'], $message);
    }
}

/**
 * Retrieves and clears flash messages from the session.
 * @return array An array of flash messages, each containing 'text' and 'color'.
 * If no flash messages are set, an empty array is returned.
 */
function getMessages()
{
    if(session_status() !== PHP_SESSION_ACTIVE){
        error_log("Flash messages require an active session.");
        return [];
    }
    if (isset($_SESSION['flash'])) {
        $flashes = $_SESSION['flash'];
        $_SESSION['flash'] = array();
        return $flashes;
    }
    return array();
}