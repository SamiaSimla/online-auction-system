<?php 
    require_once(__DIR__.'/../controllers/sellerController.php'); 
    requireLogin(); 
    include('header.php'); 
?>

<h2>Become a Seller</h2>
<?php if(isset($_SESSION['seller_verified']) && $_SESSION['seller_verified'] == 1){ ?>
    <p class="success">You are already a verified seller.</p>
<?php }else{ ?>
    <?php if($message) echo '<p class="success">'.htmlspecialchars($message).'</p>'; ?>
    <?php if(isset($errors['general'])) echo '<p class="error">'.htmlspecialchars($errors['general']).'</p>'; ?>
    
    <form method="post">
        <label>Short Motivation</label>
        <textarea name="motivation"><?php echo htmlspecialchars($_POST['motivation'] ?? ''); ?></textarea>
        <span class="error"><?php echo htmlspecialchars($errors['motivation'] ?? ''); ?></span>
        <button type="submit" name="seller_request">Submit Request</button>
    </form>
<?php } ?>
<?php include('footer.php'); ?>
