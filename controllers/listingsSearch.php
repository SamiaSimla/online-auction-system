<?php
    require_once(__DIR__.'/../config/functions.php');
    require_once(__DIR__.'/../models/listingModel.php');
    close_expired_auctions();

    $q = isset($_GET['q']) ? trim($_GET['q']) : '';
    $result = searchListings($q);
    $listings = [];
    while($row = mysqli_fetch_assoc($result)){
        $listings[] = $row;
    }
    json_response(['ok'=>true, 'listings'=>$listings]);
?>
