<?php
    require_once(__DIR__.'/../config/functions.php');
    require_once(__DIR__.'/../models/listingModel.php');
    close_expired_auctions();

    $category_id = isset($_GET['category_id']) ? (int)$_GET['category_id'] : 0;
    $result = getActiveListings($category_id);
    $listings = [];
    while($row = mysqli_fetch_assoc($result)){
        $listings[] = $row;
    }
    json_response(['ok'=>true, 'listings'=>$listings]);
?>
