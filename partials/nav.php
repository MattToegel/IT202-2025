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
<!-- Include Bootstrap CSS and JS before custom content so it can be reused or overriden -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-SgOJa3DmI69IUzQ2PVdRZhwQ+dy64/BUtbMJw1MZ8t5HZApcHrRKUc4W0kG879m7" crossorigin="anonymous">
<link rel="stylesheet" href="<?php echo get_url('styles.css'); ?>">
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/js/bootstrap.bundle.min.js" integrity="sha384-k6d4wzSIapyDyv1kpU366/PK5hCdSbCRGRCMv+eplOQJWyd1fbcAu9OCUj5zNLiq" crossorigin="anonymous"></script>
<script src="<?php echo get_url('helpers.js'); ?>"></script>
<nav class="navbar navbar-expand-lg bg-body-tertiary">
    <div class="container-fluid">
        <!-- Replace with your ucid -->
        <a class="navbar-brand text-uppercase" href="#">mt85</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarSupportedContent">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <?php if (is_logged_in()) : ?>
                    <li class="nav-item">
                        <a class="nav-link" aria-current="page" href="<?php echo get_url('home.php'); ?>">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" aria-current="page" href="<?php echo get_url('profile.php'); ?>">Profile</a>
                    </li>
                <?php endif; ?>
                <?php if (!is_logged_in()) : ?>
                    <li class="nav-item">
                        <a class="nav-link" aria-current="page" href="<?php echo get_url('login.php'); ?>">Login</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" aria-current="page" href="<?php echo get_url('register.php'); ?>">Register</a>
                    </li>
                <?php endif; ?>
                <?php if (has_role("Admin")) : ?>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            Roles
                        </a>
                        <ul class="dropdown-menu">

                            <li><a class="dropdown-item" aria-current="page" href="<?php echo get_url('admin/create_role.php'); ?>">Create Role</a>
                            </li>
                            <li><a class="dropdown-item" aria-current="page" href="<?php echo get_url('admin/list_roles.php'); ?>">List Roles</a>
                            </li>
                            <li><a class="dropdown-item" aria-current="page" href="<?php echo get_url('admin/assign_roles.php'); ?>">Assign Roles</a>
                            </li>

                        </ul>
                    </li>
                <?php endif; ?>
                <?php if (has_role("Admin")) : ?>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            Stocks
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" aria-current="page" href="<?php echo get_url('admin/create_stock.php'); ?>">Create Stock</a>
                            </li>
                            <li><a class="dropdown-item" aria-current="page" href="<?php echo get_url('admin/list_stocks.php'); ?>">List Stock</a>
                            </li>
                        </ul>
                    </li>
                <?php endif; ?>
                <?php if (has_role("Admin")) : ?>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            Companies
                        </a>
                        <ul class="dropdown-menu">
                            <li class="nav-item">
                                <a class="nav-link active" aria-current="page" href="<?php echo get_url('admin/create_company.php'); ?>">Create Company</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link active" aria-current="page" href="<?php echo get_url('admin/list_companies.php'); ?>">List Companies</a>
                            </li>
                        </ul>
                    </li>
                <?php endif; ?>
                <?php if (is_logged_in()) : ?>
                    <li class="nav-item">
                        <a class="nav-link" aria-current="page" href="<?php echo get_url('logout.php'); ?>">Logout</a>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>