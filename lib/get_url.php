<?php
/**
 * Used to handle app urls to ensure proper path (absolute/relative)
 * 
 * @param string $dest The destination URL or path.
 * @param bool $isEcho Whether to echo the URL instead of returning it.
 * @return string|null Returns the URL if $isEcho is false, otherwise echoes it.
 */
function get_url($dest, $isEcho = false)
{
    global $BASE_PATH;
    // assumes absolute path by default
    // check if not absolute
    if (!str_starts_with($dest, "/")) {
        //handle relative path
        $dest = "$BASE_PATH/$dest";
    }
    if($isEcho){
        echo $dest;
        return;
    }
    return $dest;

}