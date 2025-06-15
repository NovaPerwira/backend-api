<?php
require_once 'config/database.php';

class NFT {
    private $conn;
    private $table_name = "nfts";

    public $id;
    public $title;
    public $price;
    public $image;
    public $category_id;
    public $creator;
    public $description;
    public $tags;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function create() {
        $query = "INSERT INTO " . $this->table_name . " 
                  SET title=:title, price=:price, image=:image, category_id=:category_id, 
                      creator=:creator, description=:description, tags=:tags";

        $stmt = $this->conn->prepare($query);

        $this->title = htmlspecialchars(strip_tags($this->title));
        $this->price = htmlspecialchars(strip_tags($this->price));
        $this->image = htmlspecialchars(strip_tags($this->image));
        $this->category_id = htmlspecialchars(strip_tags($this->category_id));
        $this->creator = htmlspecialchars(strip_tags($this->creator));
        $this->description = htmlspecialchars(strip_tags($this->description));
        $this->tags = htmlspecialchars(strip_tags($this->tags));

        $stmt->bindParam(":title", $this->title);
        $stmt->bindParam(":price", $this->price);
        $stmt->bindParam(":image", $this->image);
        $stmt->bindParam(":category_id", $this->category_id);
        $stmt->bindParam(":creator", $this->creator);
        $stmt->bindParam(":description", $this->description);
        $stmt->bindParam(":tags", $this->tags);

        return $stmt->execute();
    }

    public function read($limit = 10, $offset = 0) {
        $query = "SELECT 
                      n.id, n.title, n.price, n.image, n.creator, n.description, n.tags,
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
                      n.id, n.title, n.price, n.image, n.creator, n.description, n.tags, n.category_id,
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
            $this->creator = $row['creator'];
            $this->description = $row['description'];
            $this->tags = $row['tags'];
            $this->category_id = $row['category_id'];
            return true;
        }
        return false;
    }

    public function update() {
        $query = "UPDATE " . $this->table_name . " 
                  SET title=:title, price=:price, image=:image, category_id=:category_id,
                      creator=:creator, description=:description, tags=:tags 
                  WHERE id=:id";

        $stmt = $this->conn->prepare($query);

        $this->title = htmlspecialchars(strip_tags($this->title));
        $this->price = htmlspecialchars(strip_tags($this->price));
        $this->image = htmlspecialchars(strip_tags($this->image));
        $this->category_id = htmlspecialchars(strip_tags($this->category_id));
        $this->creator = htmlspecialchars(strip_tags($this->creator));
        $this->description = htmlspecialchars(strip_tags($this->description));
        $this->tags = htmlspecialchars(strip_tags($this->tags));
        $this->id = htmlspecialchars(strip_tags($this->id));

        $stmt->bindParam(":title", $this->title);
        $stmt->bindParam(":price", $this->price);
        $stmt->bindParam(":image", $this->image);
        $stmt->bindParam(":category_id", $this->category_id);
        $stmt->bindParam(":creator", $this->creator);
        $stmt->bindParam(":description", $this->description);
        $stmt->bindParam(":tags", $this->tags);
        $stmt->bindParam(":id", $this->id);

        return $stmt->execute();
    }

    public function delete() {
        $query = "DELETE FROM " . $this->table_name . " WHERE id = :id";
        $stmt = $this->conn->prepare($query);
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
}
?>
