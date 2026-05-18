<?php
    require_once(__DIR__.'/../config/functions.php');
    require_once(__DIR__.'/../models/listingModel.php');
    require_once(__DIR__.'/../models/bidModel.php');
    requireLogin();
    close_expired_auctions();

    $listing_id = isset($_POST['listing_id']) ? (int)$_POST['listing_id'] : 0;
    $amount = isset($_POST['amount']) ? (float)$_POST['amount'] : 0;

    $listing = getListingById($listing_id);
    if(!$listing){
        json_response(['ok'=>false, 'error'=>'Listing not found']);
    }
    if($listing['status'] != 'active' || strtotime($listing['end_datetime']) <= time()){
        json_response(['ok'=>false, 'error'=>'Auction is not active']);
    }
    if($amount <= (float)$listing['current_bid']){
        json_response(['ok'=>false, 'error'=>'Bid must be greater than current bid']);
    }
    if((int)$listing['seller_id'] == (int)$_SESSION['user_id']){
        json_response(['ok'=>false, 'error'=>'Seller cannot bid on own auction']);
    }

    if(addBid($listing_id, $_SESSION['user_id'], $amount)){
        $count = getListingBidCount($listing_id);
        json_response(['ok'=>true, 'new_bid'=>$amount, 'bid_count'=>$count, 'bidder'=>$_SESSION['name'], 'time'=>date('Y-m-d H:i:s')]);
    }else{
        json_response(['ok'=>false, 'error'=>'Bid failed']);
    }
?>
