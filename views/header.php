<?php
require_once(__DIR__.'/../config/functions.php');
?>
<!DOCTYPE html>
<html>
<head>
    <title>Online Auction System</title>
    <link rel="stylesheet" href="../public/css/style.css">
</head>
<body>
 
<?php if(isset($_SESSION['user_id'])){ ?>
 
    <div class="topbar">
 
        <?php if(isset($_SESSION['role']) && $_SESSION['role'] == 'admin'){ ?>
            <a href="../views/admin_dashboard.php">Admin</a>
        <?php } ?>
 
        <a href="../views/profile.php">Profile</a>
 
        <?php if(isset($_SESSION['role']) && $_SESSION['role'] == 'buyer'){ ?>
            <a href="../views/become_seller.php">Seller</a>
        <?php } ?>
 
        <span>Welcome, <?php echo htmlspecialchars($_SESSION['name']); ?></span>
        <a href="../controllers/authController.php?logout=1">Logout</a>
 
    </div>
 
<?php } ?>
 
<div class="container"></div>