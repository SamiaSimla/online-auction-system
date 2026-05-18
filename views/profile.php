<?php 
    require_once(__DIR__.'/../controllers/sellerController.php'); 
    requireLogin(); 

    $user=getUserById($_SESSION['user_id']); 

    include('header.php'); 
?>
<h2>Profile</h2>
<?php if($message) echo '<p class="success">'.htmlspecialchars($message).'</p>'; ?>
<?php if(isset($errors['general'])) echo '<p class="error">'.htmlspecialchars($errors['general']).'</p>'; ?>

<form method="post">
    <label>Name</label>
    <input type="text" name="name" value="<?php echo htmlspecialchars($user['name']); ?>">
    <span class="error"><?php echo htmlspecialchars($errors['name'] ?? ''); ?></span>

    <label>Email</label>
    <input type="text" value="<?php echo htmlspecialchars($user['email']); ?>" readonly>

    <label>Phone</label>
    <input type="text" name="phone" value="<?php echo htmlspecialchars($user['phone']); ?>">
    <span class="error"><?php echo htmlspecialchars($errors['phone'] ?? ''); ?></span>

    <label>Bio</label>
    <textarea name="bio"><?php echo htmlspecialchars($user['bio']); ?></textarea>
    <span class="error"><?php echo htmlspecialchars($errors['bio'] ?? ''); ?></span>

    <button type="submit" name="update_profile">Update Profile</button>
</form>
<?php include('footer.php'); ?>
