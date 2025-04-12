<?php
require_once(__DIR__ . "/../lib/functions.php");
//Note: this is to resolve cookie issues with port numbers
$domain = $_SERVER["HTTP_HOST"];
if (strpos($domain, ":")) {
    $domain = explode(":", $domain)[0];
}
$localWorks = true; //some people have issues with localhost for the cookie params
//if you're one of those people make this false

//this is an extra condition added to "resolve" the localhost issue for the session cookie
if (($localWorks && $domain == "localhost") || $domain != "localhost") {
    session_set_cookie_params([
        "lifetime" => 60 * 60,
        "path" => "$BASE_PATH",
        //"domain" => $_SERVER["HTTP_HOST"] || "localhost",
        "domain" => $domain,
        "secure" => true,
        "httponly" => true,
        "samesite" => "lax"
    ]);
}
session_start();


?>
<!-- include css and js files -->
<link rel="stylesheet" href="<?php echo get_url('styles.css'); ?>">
<script src="<?php echo get_url('helpers.js'); ?>"></script>
<nav class="navbar">
    <ul>
        <?php if (is_logged_in()) : ?>
            <li><a href="<?php echo get_url('home.php'); ?>">Home</a></li>
            <li><a href="<?php echo get_url('profile.php'); ?>">Profile</a></li>
        <?php endif; ?>
        <?php if (!is_logged_in()) : ?>
            <li><a href="<?php echo get_url('login.php'); ?>">Login</a></li>
            <li><a href="<?php echo get_url('register.php'); ?>">Register</a></li>
        <?php endif; ?>
        <?php if (has_role("Admin")) : ?>
            <div class="dropdown">
                <button class="dropbtn">Roles
                    <i class="fa fa-caret-down"></i>
                </button>
                <div class="dropdown-content">

                    <li><a href="<?php echo get_url('admin/create_role.php'); ?>">Create Role</a></li>
                    <li><a href="<?php echo get_url('admin/list_roles.php'); ?>">List Roles</a></li>
                    <li><a href="<?php echo get_url('admin/assign_roles.php'); ?>">Assign Roles</a></li>

                </div>
            </div>
        <?php endif; ?>
        <?php if (has_role("Admin")) : ?>
            <div class="dropdown">
                <button class="dropbtn">Stocks
                    <i class="fa fa-caret-down"></i>
                </button>
                <div class="dropdown-content">
                    <li><a href="<?php echo get_url('admin/create_stock.php'); ?>">Create Stock</a></li>
                    <li><a href="<?php echo get_url('admin/list_stocks.php'); ?>">List Stock</a></li>
                </div>
            </div>
        <?php endif; ?>
        <?php if (has_role("Admin")) : ?>
            <div class="dropdown">
                <button class="dropbtn">Companies
                    <i class="fa fa-caret-down"></i>
                </button>
                <div class="dropdown-content">
                    <li><a href="<?php echo get_url('admin/create_company.php'); ?>">Create Company</a></li>
                    <li><a href="<?php echo get_url('admin/list_companies.php'); ?>">List Companies</a></li>
                </div>
            </div>
        <?php endif; ?>
        <?php if (is_logged_in()) : ?>
            <li><a href="<?php echo get_url('logout.php'); ?>">Logout</a></li>
        <?php endif; ?>
    </ul>
</nav>

<style>
    /* From https://www.w3schools.com/howto/howto_css_dropdown_navbar.asp */
    /* Navbar container */
    .navbar {
        overflow: hidden;
        background-color: #333;
        font-family: Arial;
    }

    /* Links inside the navbar */
    .navbar a {
        float: left;
        font-size: 16px;
        color: white;
        text-align: center;
        padding: 14px 16px;
        text-decoration: none;
    }

    /* The dropdown container */
    .dropdown {
        float: left;
        overflow: hidden;
    }

    /* Dropdown button */
    .dropdown .dropbtn {
        font-size: 16px;
        border: none;
        outline: none;
        color: white;
        padding: 14px 16px;
        background-color: inherit;
        font-family: inherit;
        /* Important for vertical align on mobile phones */
        margin: 0;
        /* Important for vertical align on mobile phones */
    }

    /* Add a red background color to navbar links on hover */
    .navbar a:hover,
    .dropdown:hover .dropbtn {
        background-color: red;
    }

    /* Dropdown content (hidden by default) */
    .dropdown-content {
        display: none;
        position: absolute;
        background-color: #f9f9f9;
        min-width: 160px;
        box-shadow: 0px 8px 16px 0px rgba(0, 0, 0, 0.2);
        z-index: 1;
    }

    /* Links inside the dropdown */
    .dropdown-content a {
        float: none;
        color: black;
        padding: 12px 16px;
        text-decoration: none;
        display: block;
        text-align: left;
    }

    /* Add a grey background color to dropdown links on hover */
    .dropdown-content a:hover {
        background-color: #ddd;
    }

    /* Show the dropdown menu on hover */
    .dropdown:hover .dropdown-content {
        display: block;
    }
</style>