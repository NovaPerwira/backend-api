<?php
require_once(__DIR__ . '/../config/database.php');

class Product {
    private $conn;
    private $table_name = "products";

    public $id;
    public $title;
    public $slug;
    public $category_id;
    public $price;
    public $description;
    public $image;
    public $is_nft;
    public $created_at;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function create() {
        $query = "INSERT INTO " . $this->table_name . " 
                  SET title=:title, slug=:slug, category_id=:category_id, 
                      price=:price, description=:description, image=:image, is_nft=:is_nft";

        $stmt = $this->conn->prepare($query);

        $this->title = htmlspecialchars(strip_tags($this->title));
        $this->slug = htmlspecialchars(strip_tags($this->slug));
        $this->description = htmlspecialchars(strip_tags($this->description));
        $this->image = htmlspecialchars(strip_tags($this->image));

        $stmt->bindParam(":title", $this->title);
        $stmt->bindParam(":slug", $this->slug);
        $stmt->bindParam(":category_id", $this->category_id, PDO::PARAM_INT);
        $stmt->bindParam(":price", $this->price);
        $stmt->bindParam(":description", $this->description);
        $stmt->bindParam(":image", $this->image);
        $stmt->bindParam(":is_nft", $this->is_nft, PDO::PARAM_BOOL);

        return $stmt->execute();
    }

    public function readAll($limit = 0, $offset = 0) {
        $query = "SELECT p.id, p.title, p.slug, p.category_id, p.price, 
                         p.description, p.image, p.is_nft, p.created_at,
                         c.name as category_name
                  FROM " . $this->table_name . " p
                  LEFT JOIN categories c ON p.category_id = c.id
                  ORDER BY p.created_at DESC";

        if ($limit > 0) {
            $query .= " LIMIT :offset, :limit";
        }

        $stmt = $this->conn->prepare($query);

        if ($limit > 0) {
            $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
            $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        }

        $stmt->execute();
        return $stmt;
    }

    public function readOneBySlug() {
        $query = "SELECT p.id, p.title, p.slug, p.category_id, p.price, 
                         p.description, p.image, p.is_nft, p.created_at,
                         c.name as category_name
                  FROM " . $this->table_name . " p
                  LEFT JOIN categories c ON p.category_id = c.id
                  WHERE p.slug = :slug LIMIT 0,1";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":slug", $this->slug);
        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row) {
            $this->id = $row['id'];
            $this->title = $row['title'];
            $this->category_id = $row['category_id'];
            $this->price = $row['price'];
            $this->description = $row['description'];
            $this->image = $row['image'];
            $this->is_nft = $row['is_nft'];
            $this->created_at = $row['created_at'];
            return $row;
        }
        return false;
    }

    public function readOneById() {
        $query = "SELECT p.id, p.title, p.slug, p.category_id, p.price, 
                         p.description, p.image, p.is_nft, p.created_at,
                         c.name as category_name
                  FROM " . $this->table_name . " p
                  LEFT JOIN categories c ON p.category_id = c.id
                  WHERE p.id = :id LIMIT 0,1";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $this->id);
        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row) {
            $this->title = $row['title'];
            $this->slug = $row['slug'];
            $this->category_id = $row['category_id'];
            $this->price = $row['price'];
            $this->description = $row['description'];
            $this->image = $row['image'];
            $this->is_nft = $row['is_nft'];
            $this->created_at = $row['created_at'];
            return $row;
        }
        return false;
    }

    public function update() {
        $query = "UPDATE " . $this->table_name . " 
                  SET title=:title, slug=:slug, category_id=:category_id, 
                      price=:price, description=:description, image=:image, is_nft=:is_nft
                  WHERE id=:id";

        $stmt = $this->conn->prepare($query);

        $this->title = htmlspecialchars(strip_tags($this->title));
        $this->slug = htmlspecialchars(strip_tags($this->slug));
        $this->description = htmlspecialchars(strip_tags($this->description));
        $this->image = htmlspecialchars(strip_tags($this->image));
        $this->id = htmlspecialchars(strip_tags($this->id));

        $stmt->bindParam(":title", $this->title);
        $stmt->bindParam(":slug", $this->slug);
        $stmt->bindParam(":category_id", $this->category_id, PDO::PARAM_INT);
        $stmt->bindParam(":price", $this->price);
        $stmt->bindParam(":description", $this->description);
        $stmt->bindParam(":image", $this->image);
        $stmt->bindParam(":is_nft", $this->is_nft, PDO::PARAM_BOOL);
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

    public function slugExists() {
        $query = "SELECT COUNT(*) FROM " . $this->table_name . " WHERE slug = :slug";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':slug', $this->slug);
        $stmt->execute();
        return $stmt->fetchColumn() > 0;
    }
}
?>