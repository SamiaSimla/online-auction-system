<?php
require_once "../config/db.php";
class Category {

    private $conn;
    private $table = "categories";

    public function __construct($db){
        $this->conn = $db;
    }

    public function getAll(){

        $query = "SELECT * FROM ".$this->table." ORDER BY id DESC";

        $stmt = $this->conn->prepare($query);
        $stmt->execute();

        return $stmt;
    }

    public function create($name){

        $query = "INSERT INTO ".$this->table."(name) VALUES(:name)";

        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(":name", $name);

        return $stmt->execute();
    }

    public function update($id, $name){

        $query = "UPDATE ".$this->table." SET name=:name WHERE id=:id";

        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(":name", $name);
        $stmt->bindParam(":id", $id);

        return $stmt->execute();
    }

    public function hasListings($id){

        $query = "SELECT COUNT(*) as total FROM listings WHERE category_id=:id";

        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(":id", $id);

        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row['total'];
    }

    public function delete($id){

        if($this->hasListings($id) > 0){
            return false;
        }

        $query = "DELETE FROM ".$this->table." WHERE id=:id";

        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(":id", $id);

        return $stmt->execute();
    }
}
?>