<?php
require_once(__DIR__.'/../config/db.php');

function registerUser($name, $email, $phone, $bio, $password){
    global $conn;
    $hash = password_hash($password, PASSWORD_DEFAULT);
    $role = 'buyer';
    $seller_verified = 0;
    $sql = "INSERT INTO users(name,email,password_hash,role,seller_verified,bio,phone,created_at) VALUES(?,?,?,?,?,?,?,NOW())";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, 'ssssiss', $name, $email, $hash, $role, $seller_verified, $bio, $phone);
    return mysqli_stmt_execute($stmt);
}

function getUserByEmail($email){
    global $conn;
    $sql = "SELECT * FROM users WHERE email=? LIMIT 1";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, 's', $email);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    return mysqli_fetch_assoc($result);
}

function getUserById($id){
    global $conn;
    $sql = "SELECT * FROM users WHERE id=? LIMIT 1";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, 'i', $id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    return mysqli_fetch_assoc($result);
}

function updateProfile($id, $name, $phone, $bio){
    global $conn;
    $sql = "UPDATE users SET name=?, phone=?, bio=? WHERE id=?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, 'sssi', $name, $phone, $bio, $id);
    return mysqli_stmt_execute($stmt);
}

function emailExists($email){
    if(getUserByEmail($email)) {
        return true;
    }
    return false;
}

function addSellerRequest($user_id, $motivation){
    global $conn;
    $check_sql = "SELECT id FROM seller_requests WHERE user_id=? AND status='pending' LIMIT 1";
    $check_stmt = mysqli_prepare($conn, $check_sql);
    mysqli_stmt_bind_param($check_stmt, 'i', $user_id);
    mysqli_stmt_execute($check_stmt);
    $check_result = mysqli_stmt_get_result($check_stmt);

    if(mysqli_fetch_assoc($check_result)){
        $sql = "UPDATE seller_requests SET motivation=?, created_at=NOW() WHERE user_id=? AND status='pending'";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, 'si', $motivation, $user_id);
        return mysqli_stmt_execute($stmt);
    }

    $status = 'pending';
    $sql = "INSERT INTO seller_requests(user_id,motivation,status,created_at) VALUES(?,?,?,NOW())";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, 'iss', $user_id, $motivation, $status);
    return mysqli_stmt_execute($stmt);
}

function getPendingSellerRequests(){
    global $conn;
    $sql = "SELECT sr.id, sr.user_id, sr.motivation, sr.created_at, u.name, u.email, u.phone, u.bio
            FROM seller_requests sr
            JOIN users u ON sr.user_id=u.id
            WHERE sr.status='pending' AND u.seller_verified=0
            ORDER BY sr.created_at DESC";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_execute($stmt);
    return mysqli_stmt_get_result($stmt);
}

function approveSeller($user_id){
    global $conn;
    $sql = "UPDATE users SET seller_verified=1 WHERE id=?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, 'i', $user_id);
    $ok1 = mysqli_stmt_execute($stmt);

    $status = 'approved';
    $sql2 = "UPDATE seller_requests SET status=? WHERE user_id=? AND status='pending'";
    $stmt2 = mysqli_prepare($conn, $sql2);
    mysqli_stmt_bind_param($stmt2, 'si', $status, $user_id);
    $ok2 = mysqli_stmt_execute($stmt2);

    return $ok1 && $ok2;
}

function rejectSeller($user_id){
    global $conn;
    $status = 'rejected';
    $sql = "UPDATE seller_requests SET status=? WHERE user_id=? AND status='pending'";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, 'si', $status, $user_id);
    return mysqli_stmt_execute($stmt);
}
?>
