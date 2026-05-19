<?php
 
require_once (__DIR__ . "/../config/db.php");
require_once (__DIR__ . "/../models/Category.php");
 
$db = (new Database())->connect();
 
$category = new Category($db);
 
$categories = $category->getAll();
include_once("header.php");
?>
 
 
<div class="container">
 
    <h2>Category Management</h2>
 
    <form method="POST" action="../controllers/CategoryController.php">
 
        <input type="hidden" name="action" value="create">
 
        <input type="text" name="name" placeholder="Category Name" required>
 
        <button type="submit">Add</button>
 
    </form>
 
    <table>
 
        <tr>
            <th>ID</th>
            <th>Name</th>
            <th>Action</th>
        </tr>
 
        <?php while($row = $categories->fetch(PDO::FETCH_ASSOC)) { ?>
 
        <tr>
 
            <td><?= $row['id']; ?></td>
 
            <td><?= $row['name']; ?></td>
 
            <td>
 
                <form method="POST" action="../controllers/CategoryController.php">
 
                    <input type="hidden" name="id" value="<?= $row['id']; ?>">
 
                    <input type="hidden" name="action" value="delete">
 
                    <button type="submit">Delete</button>
 
                </form>
 
            </td>
 
        </tr>
 
        <?php } ?>