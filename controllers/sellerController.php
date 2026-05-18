<?php
require_once(__DIR__.'/../models/userModel.php');
require_once(__DIR__.'/../config/functions.php');

$errors = [];
$message = '';

if(isset($_POST['seller_request'])){
    requireLogin();
    $motivation = trim($_POST['motivation']);
    if($motivation == ''){
        $errors['motivation'] = 'Motivation is required';
    }
    if(count($errors) == 0){
        if(addSellerRequest($_SESSION['user_id'], $motivation)){
            $message = 'Seller request submitted. Please wait for admin approval.';
        }else{
            $errors['general'] = 'Request failed';
        }
    }
}

if(isset($_POST['update_profile'])){
    requireLogin();
    $name = trim($_POST['name']);
    $phone = trim($_POST['phone']);
    $bio = trim($_POST['bio']);
    if($name == '') $errors['name'] = 'Name is required';
    if($phone == '') $errors['phone'] = 'Phone is required';
    if($bio == '') $errors['bio'] = 'Bio is required';
    if(count($errors) == 0){
        if(updateProfile($_SESSION['user_id'], $name, $phone, $bio)){
            $_SESSION['name'] = $name;
            $message = 'Profile updated';
        }else{
            $errors['general'] = 'Profile update failed';
        }
    }
}
?>
