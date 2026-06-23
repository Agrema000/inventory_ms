<?php
// Check if a session isn't already active before starting one
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Security Guard: If the user is trying to view a page but isn't logged in, kick them back to login
if (!isset($_SESSION['user_id']) && basename($_SERVER['PHP_SELF']) !== 'index.php') {
    header("Location: index.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>StockMaster IMS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
    </style>
</head>
<body class="bg-light">

    <nav class="navbar navbar-expand-lg navbar-dark bg-dark shadow-sm mb-4 py-3">
        <div class="container">
            <a class="navbar-brand fw-bold tracking-tight" href="dashboard.php">📊 StockMaster <span class="text-primary-color">IMS</span></a>
            <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto align-items-center">
                    <li class="nav-item me-3">
                        <span class="badge bg-secondary px-3 py-2 text-capitalize">👤 <?php echo $_SESSION['role'] ?? 'Staff'; ?></span>
                    </li>
                    <li class="nav-item">
                        <a class="btn btn-sm btn-outline-danger px-3" href="logout.php">Sign Out</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
    <div class="container">