<?php
require_once(__DIR__.'/../config/database.php');

function getAllCategories(){
    global $conn;
    $sql = "SELECT * FROM categories ORDER BY name ASC";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_execute($stmt);
    return mysqli_stmt_get_result($stmt);
}
?>