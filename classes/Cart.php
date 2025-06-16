<?php
require_once(__DIR__ . '/../config/database.php');

class Cart {
    private $conn;
    private $table_name = "carts";

    public $id;
    public $user_id;
    public $product_id;
    public $quantity;
    public $created_at;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function add() {
        // Check if item already exists in cart
        $check_query = "SELECT id, quantity FROM " . $this->table_name . " 
                        WHERE user_id = :user_id AND product_id = :product_id";
        $check_stmt = $this->conn->prepare($check_query);
        $check_stmt->bindParam(":user_id", $this->user_id);
        $check_stmt->bindParam(":product_id", $this->product_id);
        $check_stmt->execute();

        if ($check_stmt->rowCount() > 0) {
            // Update existing item
            $row = $check_stmt->fetch(PDO::FETCH_ASSOC);
            $new_quantity = $row['quantity'] + $this->quantity;
            
            $update_query = "UPDATE " . $this->table_name . " 
                            SET quantity = :quantity 
                            WHERE id = :id";
            $update_stmt = $this->conn->prepare($update_query);
            $update_stmt->bindParam(":quantity", $new_quantity);
            $update_stmt->bindParam(":id", $row['id']);
            return $update_stmt->execute();
        } else {
            // Add new item
            $query = "INSERT INTO " . $this->table_name . " 
                      SET user_id=:user_id, product_id=:product_id, quantity=:quantity";

            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":user_id", $this->user_id);
            $stmt->bindParam(":product_id", $this->product_id);
            $stmt->bindParam(":quantity", $this->quantity);

            return $stmt->execute();
        }
    }

    public function getByUser() {
        $query = "SELECT c.id, c.user_id, c.product_id, c.quantity, c.created_at,
                         p.title, p.price, p.image, p.slug
                  FROM " . $this->table_name . " c
                  LEFT JOIN products p ON c.product_id = p.id
                  WHERE c.user_id = :user_id
                  ORDER BY c.created_at DESC";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":user_id", $this->user_id);
        $stmt->execute();
        return $stmt;
    }

    public function update() {
        $query = "UPDATE " . $this->table_name . " 
                  SET quantity = :quantity 
                  WHERE user_id = :user_id AND product_id = :product_id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":quantity", $this->quantity);
        $stmt->bindParam(":user_id", $this->user_id);
        $stmt->bindParam(":product_id", $this->product_id);

        return $stmt->execute();
    }

    public function remove() {
        $query = "DELETE FROM " . $this->table_name . " 
                  WHERE user_id = :user_id AND product_id = :product_id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":user_id", $this->user_id);
        $stmt->bindParam(":product_id", $this->product_id);

        return $stmt->execute();
    }

    public function clear() {
        $query = "DELETE FROM " . $this->table_name . " WHERE user_id = :user_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":user_id", $this->user_id);
        return $stmt->execute();
    }
}
?>