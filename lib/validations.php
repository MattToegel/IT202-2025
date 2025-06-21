<?php

function sanitize_email($email = "") {
    return filter_var(trim($email), FILTER_SANITIZE_EMAIL);
}
function is_valid_email($email = "") {
    return filter_var(trim($email), FILTER_VALIDATE_EMAIL);
}
function is_valid_username($username)
{
    return preg_match('/^[a-z0-9_-]{3,30}$/', $username);
}
function is_valid_password($password)
{
    return strlen($password) >= 8;
}
function is_valid_confirm($original, $confirm)
{
    // checking not empty to avoid empty equals empty being true
    return !empty($original) && $original === $confirm;
}
