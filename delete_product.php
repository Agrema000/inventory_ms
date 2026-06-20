<?php
// delete_product.php
session_start();
require_once 'config/database.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

// Ensure an ID was passed in the URL string
if (isset($_GET['id'])) {
    $id = intval($_GET['id']);

    try {
        $stmt = $pdo->prepare("DELETE FROM products WHERE id = ?");
        $stmt->execute([$id]);
    } catch (\PDOException $e) {
        // Fail silently or handle error messaging logs
    }
}

// Bounce back to the main management screen automatically
header("Location: products.php");
exit;