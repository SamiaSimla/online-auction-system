<?php
$host = 'localhost';
$dbuser = 'root';
$dbpass = '';
$dbname = 'online_auction_system';
 
$conn = mysqli_connect($host, $dbuser, $dbpass, $dbname);
 
if(!$conn){
    die('Database connection failed: '.mysqli_connect_error());
}
?>