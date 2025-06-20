<?php
//include functions here so we can have it on every page that uses the nav bar
//that way we don't need to include so many other files on each page
//nav will pull in functions and functions will pull in db

// checking to see if domain has a port number attached (localhost)
$domain = $_SERVER["HTTP_HOST"];
if (strpos($domain, ":")) {
    // strip the port number if present
    $domain = explode(":", $domain)[0];
}
// used for public hosting like heroku
if ($domain != "localhost") {
    session_set_cookie_params([
        "lifetime" => 60 * 60, // this is cookie lifetime, not session lifetime
        "path" => "/project", // path to restrict cookie to; match your project folder (case sensitive)
        "domain" => $domain, // domain to restrict cookie to
        "secure" => true, // https only
        "httponly" => true, // javascript can't access
        "samesite" => "lax" // helps prevent CSRF, but allows normal navigation
    ]);
}
session_start();
require(__DIR__."/../lib/functions.php");
?>
<link rel="stylesheet" href="<?php echo $BASE_PATH; ?>/styles.css">
<script src="<?php echo $BASE_PATH; ?>/helpers.js"></script>
<nav>
    <ul>
        <?php if (is_logged_in()) : ?>
            <li><a href="landing.php">Landing</a></li>
            <li><a href="profile.php">Profile</a></li>
        <?php endif; ?>
        <?php if (!is_logged_in()) : ?>
            <li><a href="login.php">Login</a></li>
            <li><a href="register.php">Register</a></li>
        <?php endif; ?>
        <?php if (is_logged_in()) : ?>
            <li><a href="logout.php">Logout</a></li>
        <?php endif; ?>
    </ul>
</nav>