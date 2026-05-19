<?php
require_once (__DIR__."/../config/db.php");
require_once (__DIR__."/../models/Listing.php");
session_start();
 
$_SESSION['user_id'] = 1;
$_SESSION['seller_verified'] = 1;
 
 
 
$db = (new Database())->connect();
 
$listingModel = new Listing($db);
 
if(!isset($_GET['id'])){
 
    die("Invalid Listing ID");
}
 
$data = $listingModel->getById($_GET['id']);
 
$bidCount = $listingModel->bidCount($_GET['id']);
include_once("header.php");
?>
 
 
<div class="container">
 
    <h2>Edit Listing</h2>
 
    <?php if($bidCount > 0){ ?>
 
        <p style="color:red;">
            Cannot edit because bids already exist.
        </p>
 
    <?php } ?>
 
    <form
        method="POST"
        action="../controllers/ListingController.php"
        enctype="multipart/form-data"
    >
 
        <input type="hidden" name="update_listing">
 
        <input type="hidden" name="listing_id" value="<?= $data['id']; ?>">
 
        <input
            type="text"
            name="title"
            value="<?= $data['title']; ?>"
            <?= ($bidCount > 0) ? 'readonly' : ''; ?>
        >
 
        <textarea
            name="description"
            <?= ($bidCount > 0) ? 'readonly' : ''; ?>
        ><?= $data['description']; ?></textarea>
 
        <?php if($bidCount == 0){ ?>
 
            <input type="file" name="image">
 
        <?php } ?>
 
        <button type="submit">
            Update
        </button>
 
    </form>
 
</div>
 
<?php Include_once("footer.php"); ?>