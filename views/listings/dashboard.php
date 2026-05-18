<?php
require_once (__DIR__."/../../config/db.php");
require_once (__DIR__."/../../models/Listing.php");
session_start();

$_SESSION['user_id'] = 1;
$_SESSION['seller_verified'] = 1;


$db = (new Database())->connect();

$listing = new Listing($db);

$listings = $listing->getSellerListings($_SESSION['user_id']);
?>

<!DOCTYPE html>
<html>

<head>

    <title>Seller Dashboard</title>

    <link rel="stylesheet" href="../../public/css/style.css">

</head>

<body>

<div class="navbar">

    <a href="dashboard.php">Dashboard</a>

    <a href="create.php">Create Listing</a>

    <a href="../categories/index.php">Manage Categories</a>

</div>

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

<script src="../../public/js/countdown.js"></script>

<script src="../../public/js/listing.js"></script>

</body>
</html>