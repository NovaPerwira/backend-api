<?php
require_once(__DIR__ . '/../config/database.php');

class Transaction {
    private $conn;
    private $table_name = "transactions";

    public $id;
    public $product_id;
    public $buyer_id;
    public $seller_id;
    public $price;
    public $created_at;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function create() {
        $query = "INSERT INTO " . $this->table_name . " 
                  SET product_id=:product_id, buyer_id=:buyer_id, 
                      seller_id=:seller_id, price=:price";

        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(":product_id", $this->product_id);
        $stmt->bindParam(":buyer_id", $this->buyer_id);
        $stmt->bindParam(":seller_id", $this->seller_id);
        $stmt->bindParam(":price", $this->price);

        return $stmt->execute();
    }

    public function getByUser() {
        $query = "SELECT t.id, t.product_id, t.buyer_id, t.seller_id, t.price, t.created_at,
                         p.title, p.slug, p.image,
                         buyer.name as buyer_name,
                         seller.name as seller_name
                  FROM " . $this->table_name . " t
                  LEFT JOIN products p ON t.product_id = p.id
                  LEFT JOIN users buyer ON t.buyer_id = buyer.id
                  LEFT JOIN users seller ON t.seller_id = seller.id
                  WHERE t.buyer_id = :user_id OR t.seller_id = :user_id
                  ORDER BY t.created_at DESC";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":user_id", $this->buyer_id);
        $stmt->execute();
        return $stmt;
    }

    public function readAll() {
        $query = "SELECT t.id, t.product_id, t.buyer_id, t.seller_id, t.price, t.created_at,
                         p.title, p.slug, p.image,
                         buyer.name as buyer_name,
                         seller.name as seller_name
                  FROM " . $this->table_name . " t
                  LEFT JOIN products p ON t.product_id = p.id
                  LEFT JOIN users buyer ON t.buyer_id = buyer.id
                  LEFT JOIN users seller ON t.seller_id = seller.id
                  ORDER BY t.created_at DESC";

        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }
}
?>