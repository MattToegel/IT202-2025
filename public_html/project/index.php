
<style>
    /* TODO can replace the CSS as you wish or replace with bootstrap in Milestone2+ topics */
.jumbotron {
    padding: 2rem 1rem;
    margin-bottom: 2rem;
    background-color: #e9ecef;
    border-radius: 0.3rem;
    text-align: center;
}
.display-4 {
    font-size: 2.5rem;
    font-weight: 300;
}
.lead {
    font-size: 1.25rem;
    font-weight: 300;
}
.btn {
    padding: 0.5rem 1.5rem;
    font-size: 1rem;
    border-radius: 0.25rem;
    text-decoration: none;
    margin: 0 0.5rem;
    transition: background 0.2s, color 0.2s;
    display: inline-block;
}
.btn-primary {
    background-color: #007bff;
    color: #fff;
    border: none;
}
.btn-primary:hover, .btn-primary:focus {
    background-color: #0056b3;
    color: #fff;
}
.btn-secondary {
    background-color: #6c757d;
    color: #fff;
    border: none;
}
.btn-secondary:hover, .btn-secondary:focus {
    background-color: #495057;
    color: #fff;
}
</style>
<div class="jumbotron">
    <!-- TODO replace your name -->
    <h1 class="display-4">Welcome to Matt's project</h1>
    <!-- TODO change Date/Semester accordingly -->
    <p class="lead">This is for the Summer semester of IT202 2025</p>
    <p>
        <a class="btn btn-primary" href="<?php echo $BASE_PATH;?>/register.php" class="btn btn-primary">Register</a>
        <a class="btn btn-secondary" href="<?php echo $BASE_PATH;?>/login.php" class="btn btn-secondary">Login</a>
    </p>
</div>
