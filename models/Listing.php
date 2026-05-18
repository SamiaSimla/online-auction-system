<?php
require_once "../config/db.php";
class Listing {

    private $conn;
    private $table = "listings";

    public function __construct($db){

        $this->conn = $db;
    }

    public function create($data){

        $query = "INSERT INTO listings
        (
            seller_id,
            category_id,
            title,
            description,
            starting_price,
            reserve_price,
            current_bid,
            image_path,
            end_datetime,
            status,
            created_at
        )

        VALUES
        (
            :seller_id,
            :category_id,
            :title,
            :description,
            :starting_price,
            :reserve_price,
            :current_bid,
            :image_path,
            :end_datetime,
            'active',
            NOW()
        )";

        $stmt = $this->conn->prepare($query);

        return $stmt->execute($data);
    }

    public function getSellerListings($seller_id){

        $query = "SELECT
                    l.*,
                    COUNT(b.id) as bid_count
                FROM listings l
                LEFT JOIN bids b
                ON l.id = b.listing_id
                WHERE l.seller_id=:seller_id
                GROUP BY l.id
                ORDER BY l.id DESC";

        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(":seller_id", $seller_id);

        $stmt->execute();

        return $stmt;
    }

    public function getById($id){

        $query = "SELECT * FROM listings WHERE id=:id";

        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(":id", $id);

        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function bidCount($id){

        $query = "SELECT COUNT(*) as total FROM bids WHERE listing_id=:id";

        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(":id", $id);

        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row['total'];
    }

    public function update($id, $title, $description, $image){

        $query = "UPDATE listings
                SET
                    title=:title,
                    description=:description,
                    image_path=:image
                WHERE id=:id";

        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(":title", $title);
        $stmt->bindParam(":description", $description);
        $stmt->bindParam(":image", $image);
        $stmt->bindParam(":id", $id);

        return $stmt->execute();
    }

    public function cancel($id){

        $query = "UPDATE listings
                SET status='cancelled'
                WHERE id=:id";

        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(":id", $id);

        return $stmt->execute();
    }
}
?>