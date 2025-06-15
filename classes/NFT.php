<?php
require_once(__DIR__ . '/../config/database.php');

class NFT {
    private $conn;
    private $table_name = "nfts";

    public $id;
    public $title;
    public $price;
    public $image;
    public $category_id;
    public $description;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function create() {
    $query = "INSERT INTO " . $this->table_name . " 
              SET title=:title, price=:price, image=:image, category_id=:category_id, 
                  description=:description";

    $stmt = $this->conn->prepare($query);

    // Handle NULL dan jaga biar PHP 8.1+ gak warning
    $this->title = htmlspecialchars(strip_tags($this->title ?? ''));
    $this->price = htmlspecialchars(strip_tags($this->price ?? ''));
    $this->image = htmlspecialchars(strip_tags($this->image ?? ''));
    $this->description = htmlspecialchars(strip_tags($this->description ?? ''));

    // Pastikan category_id adalah integer valid atau NULL
    $this->category_id = isset($this->category_id) && is_numeric($this->category_id)
        ? (int) $this->category_id
        : null;

    $stmt->bindParam(":title", $this->title);
    $stmt->bindParam(":price", $this->price);
    $stmt->bindParam(":image", $this->image);
    $stmt->bindParam(":category_id", $this->category_id, PDO::PARAM_INT); // <--- penting: pastikan pakai PDO::PARAM_INT
    $stmt->bindParam(":description", $this->description);

    return $stmt->execute();
}


    public function read($limit = 10, $offset = 0) {
        $query = "SELECT 
                      n.id, n.title, n.price, n.image, n.description,
                      c.title AS category_name, c.id AS category_id
                  FROM " . $this->table_name . " n
                  LEFT JOIN categories c ON n.category_id = c.id
                  ORDER BY n.id DESC 
                  LIMIT :limit OFFSET :offset";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":limit", $limit, PDO::PARAM_INT);
        $stmt->bindParam(":offset", $offset, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt;
    }

    public function readOne() {
        $query = "SELECT 
                      n.id, n.title, n.price, n.image, n.description, n.category_id,
                      c.title as category_name
                  FROM " . $this->table_name . " n
                  LEFT JOIN categories c ON n.category_id = c.id
                  WHERE n.id = :id LIMIT 0,1";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $this->id);
        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row) {
            $this->title = $row['title'];
            $this->price = $row['price'];
            $this->image = $row['image'];
            $this->description = $row['description'];
            $this->category_id = $row['category_id'];
            return true;
        }
        return false;
    }

    public function readAll() {
    $query = "SELECT * FROM " . $this->table_name . " ORDER BY id DESC";
    $stmt = $this->conn->prepare($query);
    $stmt->execute();
    return $stmt;
}

    public function update() {
        $query = "UPDATE " . $this->table_name . " 
                  SET title=:title, price=:price, image=:image, category_id=:category_id,
                      description=:description
                  WHERE id=:id";

        $stmt = $this->conn->prepare($query);

        $this->title = htmlspecialchars(strip_tags($this->title));
        $this->price = htmlspecialchars(strip_tags($this->price));
        $this->image = htmlspecialchars(strip_tags($this->image));
        $this->category_id = htmlspecialchars(strip_tags($this->category_id));
        $this->description = htmlspecialchars(strip_tags($this->description));
        $this->id = htmlspecialchars(strip_tags($this->id));

        $stmt->bindParam(":title", $this->title);
        $stmt->bindParam(":price", $this->price);
        $stmt->bindParam(":image", $this->image);
        $stmt->bindParam(":category_id", $this->category_id);
        $stmt->bindParam(":description", $this->description);
        $stmt->bindParam(":id", $this->id);

        return $stmt->execute();
    }

    public function delete() {
        $query = "DELETE FROM " . $this->table_name . " WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $this->id = htmlspecialchars(strip_tags($this->id));
        $stmt->bindParam(":id", $this->id);

        return $stmt->execute();
    }

    public function count() {
        $query = "SELECT COUNT(*) as total FROM " . $this->table_name;
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row['total'];
    }

    public function readByCategory($category_id, $limit = 10, $offset = 0) {
        $query = "SELECT 
                      n.id, n.title, n.price, n.image, n.description,
                      c.title AS category_name, c.id AS category_id
                  FROM " . $this->table_name . " n
                  LEFT JOIN categories c ON n.category_id = c.id
                  WHERE n.category_id = :category_id
                  ORDER BY n.id DESC 
                  LIMIT :limit OFFSET :offset";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":category_id", $category_id, PDO::PARAM_INT);
        $stmt->bindParam(":limit", $limit, PDO::PARAM_INT);
        $stmt->bindParam(":offset", $offset, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt;
    }

    public function search($keyword, $limit = 10, $offset = 0) {
        $query = "SELECT 
                      n.id, n.title, n.price, n.image, n.description,
                      c.title AS category_name, c.id AS category_id
                  FROM " . $this->table_name . " n
                  LEFT JOIN categories c ON n.category_id = c.id
                  WHERE n.title LIKE :keyword OR n.description LIKE :keyword
                  ORDER BY n.id DESC 
                  LIMIT :limit OFFSET :offset";

        $stmt = $this->conn->prepare($query);
        $keyword = "%" . $keyword . "%";
        $stmt->bindParam(":keyword", $keyword);
        $stmt->bindParam(":limit", $limit, PDO::PARAM_INT);
        $stmt->bindParam(":offset", $offset, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt;
    }
}