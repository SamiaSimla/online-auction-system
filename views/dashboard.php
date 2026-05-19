<?php
require_once (__DIR__."/../config/db.php");
require_once (__DIR__."/../models/Listing.php");
session_start();
 
$_SESSION['user_id'] = 1;
$_SESSION['seller_verified'] = 1;
 
 
$db = (new Database())->connect();
 
$listing = new Listing($db);
 
$listings = $listing->getSellerListings($_SESSION['user_id']);
include_once("header.php");
?>
 
 
<div class="container">
 
    <h2>Seller Dashboard</h2>
 
    <table>
 
        <tr>
 
            <th>Title</th>
            <th>Starting Price</th>
            <th>Current Bid</th>
            <th>Bid Count</th>
            <th>Status</th>
            <th>Time Remaining</th>
            <th>Action</th>
 
        </tr>
 
        <?php while($row = $listings->fetch(PDO::FETCH_ASSOC)) { ?>
 
        <tr id="row<?= $row['id']; ?>">
 
            <td><?= $row['title']; ?></td>
 
            <td><?= $row['starting_price']; ?></td>
 
            <td><?= $row['current_bid']; ?></td>
 
            <td><?= $row['bid_count']; ?></td>
 
            <td id="status<?= $row['id']; ?>">
 
                <?= $row['status']; ?>
 
            </td>
 
            <td
                class="countdown"
                data-end="<?= $row['end_datetime']; ?>"
            ></td>
 
            <td>
 
                <a href="edit.php?id=<?= $row['id']; ?>">
                    Edit
                </a>
 
                |
 
                <button onclick="cancelListing(<?= $row['id']; ?>)">
                    Cancel
                </button>
 
            </td>
 
        </tr>
 
        <?php } ?>
 
    </table>
 
</div>
 
<script src="../public/js/countdown.js"></script>
 
<script src="../public/js/listing.js"></script>
 
<?php Include_once("footer.php"); ?>
 