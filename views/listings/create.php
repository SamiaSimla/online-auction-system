<?php
require_once (__DIR__."/../../config/db.php");
session_start();

$_SESSION['user_id'] = 1;
$_SESSION['seller_verified'] = 1;



$db = (new Database())->connect();

$stmt = $db->prepare("SELECT * FROM categories");

$stmt->execute();
?>

<!DOCTYPE html>
<html>

<head>

    <title>Create Listing</title>

    <link rel="stylesheet" href="../../public/css/style.css">

</head>

<body>

<div class="navbar">

    <a href="dashboard.php">Dashboard</a>

    <a href="create.php">Create Listing</a>

    <a href="../categories/index.php">Categories</a>

</div>

<div class="container">

    <h2>Create Auction Listing</h2>

    <form
        method="POST"
        action="../../controllers/ListingController.php"
        enctype="multipart/form-data"
    >

        <input type="hidden" name="create_listing">

        <input
            type="text"
            name="title"
            placeholder="Title"
            required
        >

        <textarea
            name="description"
            placeholder="Description"
            required
        ></textarea>

        <select name="category_id" required>

            <option value="">Select Category</option>

            <?php while($row = $stmt->fetch(PDO::FETCH_ASSOC)) { ?>

                <option value="<?= $row['id']; ?>">
                    <?= $row['name']; ?>
                </option>

            <?php } ?>

        </select>

        <input
            type="number"
            step="0.01"
            name="starting_price"
            placeholder="Starting Price"
            required
        >

        <input
            type="number"
            step="0.01"
            name="reserve_price"
            placeholder="Reserve Price"
            required
        >

        <input
            type="datetime-local"
            name="end_datetime"
            required
            onkeydown="return false"
        >

        <label for="imageUpload" class="upload-btn">
            Choose Image
        </label>

        <input
            type="file"
            id="imageUpload"
            name="image"
            accept=".jpg,.jpeg,.png"
            hidden
            required
        >

        <button type="submit">
            Create Listing
        </button>

    </form>

</div>

</body>
</html>