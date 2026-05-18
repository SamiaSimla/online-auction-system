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
        exit();
    }
}

function requireAdmin(){
    requireLogin();
    if(!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin'){
        header('Location: ../views/home.php');
        exit();
    }
}

function json_response($arr){
    header('Content-Type: application/json');
    echo json_encode($arr);
    exit();
}

function close_expired_auctions(){
    global $conn;

    $sql = "SELECT id FROM listings WHERE status='active' AND end_datetime <= NOW()";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    while($row = mysqli_fetch_assoc($result)){
        $listing_id = $row['id'];

        $bid_sql = "SELECT id FROM bids WHERE listing_id=? ORDER BY amount DESC, created_at ASC LIMIT 1";
        $bid_stmt = mysqli_prepare($conn, $bid_sql);
        mysqli_stmt_bind_param($bid_stmt, 'i', $listing_id);
        mysqli_stmt_execute($bid_stmt);
        $bid_result = mysqli_stmt_get_result($bid_stmt);
        $bid = mysqli_fetch_assoc($bid_result);
        $winner_bid_id = $bid ? $bid['id'] : null;

        $update_sql = "UPDATE listings SET status='ended', winner_bid_id=? WHERE id=? AND status='active'";
        $update_stmt = mysqli_prepare($conn, $update_sql);
        mysqli_stmt_bind_param($update_stmt, 'ii', $winner_bid_id, $listing_id);
        mysqli_stmt_execute($update_stmt);
    }
}

?>