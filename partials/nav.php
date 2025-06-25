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
<link rel="stylesheet" href="<?php get_url('styles.css', true);?>">
<script src="<?php get_url('helpers.js', true);?>"></script>
<nav>
    <ul>
        <?php if (is_logged_in()) : ?>
            <li><a href="<?php get_url('landing.php', true);?>">Landing</a></li>
            <li><a href="<?php get_url('profile.php', true);?>">Profile</a></li>
        <?php endif; ?>
        <?php if (!is_logged_in()) : ?>
            <li><a href="<?php get_url('login.php', true);?>">Login</a></li>
            <li><a href="<?php get_url('register.php', true);?>">Register</a></li>
        <?php endif; ?>
        <?php if (has_role("Admin")) : ?>
            <li><a href="<?php get_url('admin/create_role.php', true); ?>">Create Role</a></li>
            <li><a href="<?php get_url('admin/list_roles.php', true); ?>">List Roles</a></li>
            <li><a href="<?php get_url('admin/assign_roles.php', true); ?>">Assign Roles</a></li>
        <?php endif; ?>
        <?php if (is_logged_in()) : ?>
            <li><a href="<?php get_url('logout.php', true);?>">Logout</a></li>
        <?php endif; ?>
    </ul>
</nav>
