<?php
// index.php
session_start();

// If the user is already logged in, send them straight to the dashboard
if (isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit;
}

require_once 'config/database.php';
$error = '';

// Check if the form was submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    if (!empty($username) && !empty($password)) {
        // Query the database to find the user
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch();

        // Check if user exists and password is correct
        if ($user && password_verify($password, $user['password'])) {
            // Store user details in global session variables
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];

            // Redirect smoothly to the dashboard
            header("Location: dashboard.php");
            exit;
        } else {
            $error = 'Invalid username or password.';
        }
    } else {
        $error = 'Please fill in all fields.';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - StockMaster IMS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
    <style>
        /* Center the login box beautifully on the page */
        body {
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .login-card {
            background: #ffffff;
            border-radius: 16px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.3);
            max-width: 400px;
            width: 100%;
            padding: 2.5rem;
        }
    </style>
</head>
<body>

<div class="login-card">
    <div class="text-center mb-4">
        <h2 class="fw-bold text-dark mb-1">📊 StockMaster</h2>
        <p class="text-muted small">Sign in to manage your inventory</p>
    </div>

    <?php if (!empty($error)): ?>
        <div class="alert alert-danger py-2 small border-0 text-center" role="alert">
            <?php echo $error; ?>
        </div>
    <?php endif; ?>

    <form action="index.php" method="POST">
        <div class="mb-3">
            <label for="username" class="form-label small fw-semibold text-secondary">Username</label>
            <input type="text" name="username" id="username" class="form-control form-control-lg fs-6" placeholder="Enter username" required>
        </div>
        
        <div class="mb-4">
            <label for="password" class="form-label small fw-semibold text-secondary">Password</label>
            <input type="password" name="password" id="password" class="form-control form-control-lg fs-6" placeholder="••••••••" required>
        </div>

        <button type="submit" class="btn btn-primary w-100 btn-lg fs-6 py-2">Sign In</button>
    </form>
</div>

</body>
</html>