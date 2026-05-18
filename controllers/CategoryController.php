<?php

require_once (__DIR__."/../config/db.php");
require_once (__DIR__."/../models/Category.php");

$db = (new Database())->connect();

$category = new Category($db);

if(isset($_POST['action'])){

    if($_POST['action'] == "create"){

        $name = trim($_POST['name']);

        $category->create($name);

        header("Location: ../views/categories/index.php");
    }

    if($_POST['action'] == "update"){

        $category->update($_POST['id'], $_POST['name']);

        header("Location: ../views/categories/index.php");
    }

    if($_POST['action'] == "delete"){

        $category->delete($_POST['id']);

        header("Location: ../views/categories/index.php");
    }
}
?>