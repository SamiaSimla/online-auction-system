<?php
require_once(__DIR__.'/../config/database.php');

function addBid($listing_id, $buyer_id, $amount){
    global $conn;
    $sql = "INSERT INTO bids(listing_id,buyer_id,amount,created_at) VALUES(?,?,?,NOW())";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, 'iid', $listing_id, $buyer_id, $amount);
    $ok = mysqli_stmt_execute($stmt);

    if($ok){
        $sql2 = "UPDATE listings SET current_bid=? WHERE id=?";
        $stmt2 = mysqli_prepare($conn, $sql2);
        mysqli_stmt_bind_param($stmt2, 'di', $amount, $listing_id);
        mysqli_stmt_execute($stmt2);
    }
    return $ok;
}

function getLastBids($listing_id, $limit=10){
    global $conn;
    $sql = "SELECT b.*, u.name AS bidder_name FROM bids b JOIN users u ON b.buyer_id=u.id WHERE b.listing_id=? ORDER BY b.created_at DESC LIMIT ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, 'ii', $listing_id, $limit);
    mysqli_stmt_execute($stmt);
    return mysqli_stmt_get_result($stmt);
}

function getListingBidCount($listing_id){
    global $conn;
    $sql = "SELECT COUNT(*) AS total FROM bids WHERE listing_id=?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, 'i', $listing_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $row = mysqli_fetch_assoc($result);
    return (int)$row['total'];
}

function getBuyerBidRows($buyer_id){
    global $conn;
    $sql = "SELECT l.id AS listing_id, l.title, l.current_bid, l.status, l.reserve_price, l.winner_bid_id,
            MAX(b.amount) AS my_highest_bid, wb.amount AS winner_amount,
            s.name AS seller_name, s.email AS seller_email
            FROM bids b
            JOIN listings l ON b.listing_id=l.id
            JOIN users s ON l.seller_id=s.id
            LEFT JOIN bids wb ON l.winner_bid_id=wb.id
            WHERE b.buyer_id=?
            GROUP BY l.id
            ORDER BY MAX(b.created_at) DESC";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, 'i', $buyer_id);
    mysqli_stmt_execute($stmt);
    return mysqli_stmt_get_result($stmt);
}
?>
