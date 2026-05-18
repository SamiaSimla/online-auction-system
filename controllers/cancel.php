<?php

require_once (__DIR__."/../config/db.php");
require_once (__DIR__."/../models/Listing.php");
session_start();

header("Content-Type: application/json");


$db = (new database())->connect();

$listing = new Listing($db);

$id = $_POST['listing_id'];

if($listing->bidCount($id) > 0){

    echo json_encode([
        "success" => false,
        "message" => "Cannot cancel. Bids already exist."
    ]);

    exit;
}

$listing->cancel($id);

echo json_encode([
    "success" => true
]);
?>