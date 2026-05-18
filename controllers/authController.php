<?php
require_once(__DIR__.'/../models/userModel.php');
require_once(__DIR__.'/../config/functions.php');

$errors = [];

if(isset($_POST['register'])){
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $bio = trim($_POST['bio']);
    $password = $_POST['password'];

    if($name == '') $errors['name'] = 'Name is required';
    if($email == '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors['email'] = 'Valid email is required';
    if($phone == '') $errors['phone'] = 'Phone is required';
    if($bio == '') $errors['bio'] = 'Bio is required';
    if(strlen($password) < 8) $errors['password'] = 'Password must be at least 8 characters';
    if($email != '' && emailExists($email)) $errors['email'] = 'Email already exists';

    if(count($errors) == 0){
        if(registerUser($name, $email, $phone, $bio, $password)){
            header('Location: ../views/login.php?registered=1');
        }else{
            $errors['general'] = 'Registration failed';
        }
    }
}

if(isset($_POST['login'])){
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    if($email == '') $errors['email'] = 'Email is required';
    if($password == '') $errors['password'] = 'Password is required';

    if(count($errors) == 0){
        $user = getUserByEmail($email);
        if($user && password_verify($password, $user['password_hash'])){
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['name'] = $user['name'];
            $_SESSION['seller_verified'] = $user['seller_verified'];
            $_SESSION['role'] = $user['role'];

            if($user['role'] == 'admin'){
                header('Location: ../views/admin_dashboard.php');
            }else{
                header('Location: ../views/home.php');
            }
        }else{
            $errors['general'] = 'Invalid email or password';
        }
    }
}

if(isset($_GET['logout'])){
    session_destroy();
    header('Location: ../views/login.php');
}
?>
