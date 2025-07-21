<?php

function redirect($path) { //header headache
    //https://www.php.net/manual/en/function.headers-sent.php#90160
    /*headers are sent at the end of script execution otherwise they are sent when the buffer reaches it's limit and emptied */
    $url = get_url($path);
    if (!headers_sent()) {
        //php redirect
        die(header("Location: $url"));
    }
    // Escape URL for output
    $safe_url = htmlspecialchars($url, ENT_QUOTES, 'UTF-8');
    // JavaScript redirect
    echo "<script>window.location.href='{$safe_url}';</script>";
    // Meta refresh for no-JS
    echo "<noscript><meta http-equiv=\"refresh\" content=\"0;url={$safe_url}\"/></noscript>";
    die();
}
