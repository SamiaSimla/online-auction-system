<?php
if(session_status() === PHP_SESSION_NONE){
    session_start();
}

function isLoggedIn(){
    return isset($_SESSION['user_id']);
}
function requireLogin(){
    if(!isLoggedIn()){
        header('Location: ../views/login.php');
    }
}

function requireAdmin(){
    requireLogin();
    if(!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin'){
        header('Location: ../views/home.php');
    }
}

function json_response($arr){
    header('Content-Type: application/json');
    echo json_encode($arr);
    exit();
}

?>