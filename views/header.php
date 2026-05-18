<?php
require_once (__DIR__.'/../config/functions.php');

?>
<!DOCTYPE html>
<html>
<head>
    <title>Online Auction System</title>
    <link rel="stylesheet" href="../public/css/style.css">
</head>
<body>
<div class="topbar">
    <a href="../views/home.php">Home</a>
    <?php if(isset($_SESSION['user_id'])){ ?>
        <a href="../views/my_bids.php">My Bids</a>
        <a href="../views/profile.php">Profile</a>
        <?php if(isset($_SESSION['seller_verified']) && $_SESSION['seller_verified'] == 1){ ?>
            <a href="../views/listings/create.php">Create Listing</a>
            <a href="../views/listings/dashboard.php">Seller Dashboard</a>
        <?php }else{ ?>
            <a href="../views/become_seller.php">Become Seller</a>
        <?php } ?>
        <?php if(isset($_SESSION['role']) && $_SESSION['role'] == 'admin'){ ?>
            <a href="../views/admin_dashboard.php">Admin</a>
            <a href="../views/categories/index.php">Categories</a>
        <?php } ?>
        <span class="right">Welcome, <?php echo isset($_SESSION['name']) ?  htmlspecialchars($_SESSION['name']) : 'User' ; ?> | <a href="../controllers/authController.php?logout=1">Logout</a></span>
    <?php }else{ ?>
        <a href="../views/login.php">Login</a>
        <a href="../views/register.php">Register</a>
    <?php } ?>
</div>
<div class="container">