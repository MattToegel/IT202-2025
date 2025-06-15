<?php
/**
 * Safe Echo Function
 * - If given an array/object and a key, returns the value at that key (or $default if not set).
 * - If given a scalar, returns it directly (or $default if not set).
 * - By default, passes the result through htmlspecialchars() for XSS safety.
 * - If $isEcho is true, echoes the value; otherwise, returns it.
 * - If $raw is true, skips htmlspecialchars() (WARNING: this can expose your app to XSS if used with untrusted data).
 *
 * @param mixed $v Value, array, or object
 * @param mixed $k Key (optional)
 * @param mixed $default Default value if key not found
 * @param bool $isEcho Whether to echo (true) or return (false)
 * @param bool $raw If true, do NOT escape output (default: false)
 * @return mixed|null
 */
function se($v, $k = null, $default = "", $isEcho = true, $raw = false) {
    if (is_array($v) && !is_null($k) && array_key_exists($k, $v)) {
        $returnValue = $v[$k];
    } else if (is_object($v) && !is_null($k) && isset($v->$k)) {
        $returnValue = $v->$k;
    } else {
        $returnValue = $v;
        if (is_array($returnValue) || is_object($returnValue)) {
            $returnValue = $default;
        }
    }
    if (!isset($returnValue)) {
        $returnValue = $default;
    }
    $safeValue = $raw ? $returnValue : htmlspecialchars($returnValue, ENT_QUOTES);
    if ($isEcho) {
        echo $safeValue;
    } else {
        return $safeValue;
    }
}

function safer_echo($v, $k = null, $default = "", $isEcho = true, $raw = false){
    return se($v, $k, $default, $isEcho, $raw);
}