<?php
class Product {
    private $conn;
    private $table_name = "products";

    public $id;
    public $name;
    public $price;
    public $image;
    public $category_slug;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function create() {
        $query = "INSERT INTO $this->table_name (name, price, image, category_slug)
                  VALUES (?, ?, ?, ?)";
        $stmt = $this->conn->prepare($query);
        return $stmt->execute([$this->name, $this->price, $this->image, $this->category_slug]);
    }

    public function updateById() {
        $query = "UPDATE $this->table_name SET name=?, price=?, image=?, category_slug=? WHERE id=?";
        $stmt = $this->conn->prepare($query);
        return $stmt->execute([$this->name, $this->price, $this->image, $this->category_slug, $this->id]);
    }

    public function deleteById() {
        $query = "DELETE FROM $this->table_name WHERE id=?";
        $stmt = $this->conn->prepare($query);
        return $stmt->execute([$this->id]);
    }

    public function getAll() {
        $query = "SELECT * FROM $this->table_name ORDER BY id DESC";
        $stmt = $this->conn->query($query);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getByCategorySlug($slug) {
        $query = "SELECT * FROM $this->table_name WHERE category_slug=?";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$slug]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>
