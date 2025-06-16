<?php
require_once(__DIR__ . '/../config/database.php');

class OrderItem {
    private $conn;
    private $table_name = "order_items";

    public $id;
    public $order_id;
    public $product_id;
    public $quantity;
    public $price_at_purchase;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function create() {
        $query = "INSERT INTO " . $this->table_name . " 
                  SET order_id=:order_id, product_id=:product_id, 
                      quantity=:quantity, price_at_purchase=:price_at_purchase";

        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(":order_id", $this->order_id);
        $stmt->bindParam(":product_id", $this->product_id);
        $stmt->bindParam(":quantity", $this->quantity);
        $stmt->bindParam(":price_at_purchase", $this->price_at_purchase);

        return $stmt->execute();
    }

    public function getByOrder() {
        $query = "SELECT oi.id, oi.order_id, oi.product_id, oi.quantity, oi.price_at_purchase,
                         p.title, p.slug, p.image
                  FROM " . $this->table_name . " oi
                  LEFT JOIN products p ON oi.product_id = p.id
                  WHERE oi.order_id = :order_id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":order_id", $this->order_id);
        $stmt->execute();
        return $stmt;
    }
}
?>