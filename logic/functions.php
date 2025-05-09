<?php
// logic/fetch_products.php
require_once '../config/admin/db_connection.php'; // Your database connection file

function getFeaturedProducts($limit = 4) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("SELECT * FROM products WHERE status = 'on sale' ORDER BY created_at DESC LIMIT :limit");
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Database error: " . $e->getMessage());
        return [];
    }
}
?>