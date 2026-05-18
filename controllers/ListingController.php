<?php
require_once (__DIR__."/../config/db.php");
require_once (__DIR__."/../models/Listing.php");
session_start();

$_SESSION['user_id'] = 1;
$_SESSION['seller_verified'] = 1;


$db = (new Database())->connect();

$listing = new Listing($db);

if(isset($_POST['create_listing'])){

    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $category_id = $_POST['category_id'];
    $starting_price = $_POST['starting_price'];
    $reserve_price = $_POST['reserve_price'];
    $end_datetime = $_POST['end_datetime'];

    if($reserve_price < $starting_price){

        die("Reserve Price must be greater than Starting Price");
    }

    if(strtotime($end_datetime) < strtotime("+1 hour")){

        die("Auction must end after 1 hour");
    }

    $image = $_FILES['image'];

    $allowed = ['image/jpeg', 'image/png'];

    if(!in_array($image['type'], $allowed)){

        die("Invalid Image");
    }

    if($image['size'] > 3000000){

        die("Image too large");
    }

    $check = getimagesize($image['tmp_name']);

    if($check == false){

        die("Invalid Image File");
    }

    $file_name = time().'_'.$image['name'];

    move_uploaded_file(
        $image['tmp_name'],
        "../public/uploads/listings/".$file_name
    );

    $data = [

        'seller_id' => $_SESSION['user_id'],
        'category_id' => $category_id,
        'title' => $title,
        'description' => $description,
        'starting_price' => $starting_price,
        'reserve_price' => $reserve_price,
        'current_bid' => $starting_price,
        'image_path' => 'public/uploads/listings/'.$file_name,
        'end_datetime' => $end_datetime
    ];

    $listing->create($data);

    header("Location: ../views/listings/dashboard.php");
}
?>