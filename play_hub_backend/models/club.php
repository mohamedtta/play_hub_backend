<?php
class Club {
    private $conn;
    private $table_name = "clubs";

    public $id;
    public $category_id;
    public $name;
    public $facility_name;
    public $area;
    public $courts_count;
    public $image_url;
    public $price_per_hour;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function getByCategory($category_id) {
        $query = "SELECT c.*, cat.name as category_name 
                  FROM " . $this->table_name . " c
                  JOIN categories cat ON c.category_id = cat.id
                  WHERE c.category_id = ? AND c.is_active = 1
                  ORDER BY c.name";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$category_id]);
        return $stmt;
    }

    // New method to get clubs by category AND area
    public function getByCategoryAndArea($category_id, $area) {
        $query = "SELECT c.*, cat.name as category_name 
                  FROM " . $this->table_name . " c
                  JOIN categories cat ON c.category_id = cat.id
                  WHERE c.category_id = ? AND c.area = ? AND c.is_active = 1
                  ORDER BY c.name";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$category_id, $area]);
        return $stmt;
    }

    public function getAll() {
        $query = "SELECT c.*, cat.name as category_name 
                  FROM " . $this->table_name . " c
                  JOIN categories cat ON c.category_id = cat.id
                  WHERE c.is_active = 1
                  ORDER BY c.category_id";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    public function getById($id) {
        $query = "SELECT c.*, cat.name as category_name 
                  FROM " . $this->table_name . " c
                  JOIN categories cat ON c.category_id = cat.id
                  WHERE c.id = ? AND c.is_active = 1";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function searchByArea($area) {
        $query = "SELECT c.*, cat.name as category_name 
                  FROM " . $this->table_name . " c
                  JOIN categories cat ON c.category_id = cat.id
                  WHERE c.area LIKE ? AND c.is_active = 1
                  ";
        $stmt = $this->conn->prepare($query);
        $searchTerm = "%{$area}%";
        $stmt->execute([$searchTerm]);
        return $stmt;
    }
}
?>