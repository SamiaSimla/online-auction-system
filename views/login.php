<?php
require_once(__DIR__.'/../config/functions.php');
include('header.php');
?>
 
<h2>Login</h2>
 <?php

if(isset($_SESSION['login_error'])){

    echo '<p class="error">'.htmlspecialchars($_SESSION['login_error']).'</p>';

    unset($_SESSION['login_error']);

}

?>
 
<form method="post" action="../controllers/authController.php">
 
    <label>Email</label>
    <input type="email" name="email" required>
 
    <label>Password</label>
    <input type="password" name="password" required>
 
    <button type="submit" name="login">Login</button>
 
</form>
 
<p class="signup-text">
    Don't have an account?
    <a href="../views/register.php">Create new account</a>
</p>
 
<?php include('footer.php'); ?>
 