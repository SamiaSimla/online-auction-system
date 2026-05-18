<?php
require_once(__DIR__.'/../config/database.php');

function getActiveListings($category_id=0){
    global $conn;
    if($category_id > 0){
        $sql = "SELECT l.*, c.name AS category_name, COUNT(b.id) AS bid_count
                FROM listings l
                JOIN categories c ON l.category_id=c.id
                LEFT JOIN bids b ON l.id=b.listing_id
                WHERE l.status='active' AND l.end_datetime > NOW() AND l.category_id=?
                GROUP BY l.id
                ORDER BY l.end_datetime ASC";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, 'i', $category_id);
    }else{
        $sql = "SELECT l.*, c.name AS category_name, COUNT(b.id) AS bid_count
                FROM listings l
                JOIN categories c ON l.category_id=c.id
                LEFT JOIN bids b ON l.id=b.listing_id
                WHERE l.status='active' AND l.end_datetime > NOW()
                GROUP BY l.id
                ORDER BY l.end_datetime ASC";
        $stmt = mysqli_prepare($conn, $sql);
    }
    mysqli_stmt_execute($stmt);
    return mysqli_stmt_get_result($stmt);
}

function searchListings($q){
    global $conn;
    $like = '%'.$q.'%';
    $sql = "SELECT l.*, c.name AS category_name, COUNT(b.id) AS bid_count
            FROM listings l
            JOIN categories c ON l.category_id=c.id
            LEFT JOIN bids b ON l.id=b.listing_id
            WHERE l.status='active' AND l.end_datetime > NOW() AND l.title LIKE ?
            GROUP BY l.id
            ORDER BY l.end_datetime ASC";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, 's', $like);
    mysqli_stmt_execute($stmt);
    return mysqli_stmt_get_result($stmt);
}

function getListingById($id){
    global $conn;
    $sql = "SELECT l.*, c.name AS category_name, u.name AS seller_name, u.email AS seller_email, u.phone AS seller_phone, u.bio AS seller_bio,
            COUNT(b.id) AS bid_count
            FROM listings l
            JOIN categories c ON l.category_id=c.id
            JOIN users u ON l.seller_id=u.id
            LEFT JOIN bids b ON l.id=b.listing_id
            WHERE l.id=?
            GROUP BY l.id
            LIMIT 1";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, 'i', $id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    return mysqli_fetch_assoc($result);
}

function getWinningBid($listing_id){
    global $conn;
    $sql = "SELECT b.*, u.name, u.email FROM bids b JOIN users u ON b.buyer_id=u.id WHERE b.listing_id=? ORDER BY b.amount DESC, b.created_at ASC LIMIT 1";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, 'i', $listing_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    return mysqli_fetch_assoc($result);
}
?>
