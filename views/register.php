<?php 
    require_once(__DIR__.'/../controllers/authController.php'); 
    include('header.php'); 
?>

<h2>Register</h2>
<?php if(isset($errors['general'])) echo '<p class="error">'.htmlspecialchars($errors['general']).'</p>'; ?>
<form method="post">
    <label>Name</label>
    <input type="text" name="name" value="<?php echo htmlspecialchars($_POST['name'] ?? ''); ?>">
    <span class="error"><?php echo htmlspecialchars($errors['name'] ?? ''); ?></span>

    <label>Email</label>
    <input type="text" name="email" value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
    <span class="error"><?php echo htmlspecialchars($errors['email'] ?? ''); ?></span>

    <label>Phone</label>
    <input type="text" name="phone" value="<?php echo htmlspecialchars($_POST['phone'] ?? ''); ?>">
    <span class="error"><?php echo htmlspecialchars($errors['phone'] ?? ''); ?></span>

    <label>Bio</label>
    <textarea name="bio"><?php echo htmlspecialchars($_POST['bio'] ?? ''); ?></textarea>
    <span class="error"><?php echo htmlspecialchars($errors['bio'] ?? ''); ?></span>

    <label>Password</label>
    <input type="password" name="password">
    <span class="error"><?php echo htmlspecialchars($errors['password'] ?? ''); ?></span>

   <button type="submit" name="register" value="buyer">Register as Buyer</button>
   Have and account?<a href="login.php">Login</a>
</form>
<?php include('footer.php'); ?>
