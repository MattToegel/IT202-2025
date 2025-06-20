<?php
function reset_session()
{
    // ensure session is started before attempting to reset
    // just because it's not active doesn't mean it doesn't exist
    if(session_status() !== PHP_SESSION_ACTIVE){
        session_start();
        error_log("Session was not active, started a new session.");
    }
    session_unset();
    session_destroy();
    session_start();
}