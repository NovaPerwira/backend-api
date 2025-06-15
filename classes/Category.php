<?php
require_once(__DIR__ . '/../config/database.php');

class Category {
    private $conn;
    private $table_name = "categories";

    public $id;
    public $slug;
    public $title;
    public $description;
    public $thumbnail;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function create() {
        $query = "INSERT INTO " . $this->table_name . " 
                  SET slug=:slug, title=:title, description=:description, thumbnail=:thumbnail";

        $stmt = $this->conn->prepare($query);

        $this->slug = htmlspecialchars(strip_tags($this->slug));
        $this->title = htmlspecialchars(strip_tags($this->title));
        $this->description = htmlspecialchars(strip_tags($this->description));
        $this->thumbnail = htmlspecialchars(strip_tags($this->thumbnail));

        $stmt->bindParam(":slug", $this->slug);
        $stmt->bindParam(":title", $this->title);
        $stmt->bindParam(":description", $this->description);
        $stmt->bindParam(":thumbnail", $this->thumbnail);

        return $stmt->execute();
    }

    public function readOneBySlug() {
        $query = "SELECT id, slug, title, description, thumbnail 
                  FROM " . $this->table_name . " 
                  WHERE slug = :slug LIMIT 0,1";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":slug", $this->slug);
        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row) {
            $this->id = $row['id'];
            $this->title = $row['title'];
            $this->description = $row['description'];
            $this->thumbnail = $row['thumbnail'];
            return true;
        }
        return false;
    }

    public function updateBySlug() {
        $query = "UPDATE " . $this->table_name . " 
                  SET title=:title, description=:description, thumbnail=:thumbnail 
                  WHERE slug=:slug";

        $stmt = $this->conn->prepare($query);

        $this->title = htmlspecialchars(strip_tags($this->title));
        $this->description = htmlspecialchars(strip_tags($this->description));
        $this->thumbnail = htmlspecialchars(strip_tags($this->thumbnail));
        $this->slug = htmlspecialchars(strip_tags($this->slug));

        $stmt->bindParam(":title", $this->title);
        $stmt->bindParam(":description", $this->description);
        $stmt->bindParam(":thumbnail", $this->thumbnail);
        $stmt->bindParam(":slug", $this->slug);

        return $stmt->execute();
    }

    public function deleteBySlug() {
        $query = "DELETE FROM " . $this->table_name . " WHERE slug = :slug";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":slug", $this->slug);
        return $stmt->execute();
    }

    public function count() {
        $query = "SELECT COUNT(*) as total FROM " . $this->table_name;
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row['total'];
    }

    public function readAll($limit = 0, $offset = 0) {
        $query = "SELECT id, slug, title, description, thumbnail 
                  FROM " . $this->table_name . " 
                  ORDER BY title ASC";

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

    public function existsBySlug() {
    $query = "SELECT COUNT(*) FROM categories WHERE slug = :slug";
    $stmt = $this->conn->prepare($query);
    $stmt->bindParam(':slug', $this->slug);
    $stmt->execute();
    return $stmt->fetchColumn() > 0;
}

}
