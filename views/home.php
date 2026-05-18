<?php
require_once(__DIR__.'/../config/functions.php');
requireLogin();
include('header.php');
?>
 
<h2>Home</h2>
 
<p>Welcome to Online Auction System.</p>
 
<?php if(isset($_SESSION['seller_verified']) && $_SESSION['seller_verified'] == 1){ ?>
    <p class="success">You are a verified seller.</p>
<?php }else{ ?>
    <p>You are registered as a buyer.</p>
<?php } ?>
 
<?php include('footer.php'); ?>