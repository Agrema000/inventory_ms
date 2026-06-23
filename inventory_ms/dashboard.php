<?php
require_once 'includes/header.php';
require_once 'config/database.php';

// Quick greeting based on time of day
$hour = date('H');
$greeting = ($hour < 12) ? 'Good morning' : (($hour < 18) ? 'Good afternoon' : 'Good evening');

try {
    // 1. Fetch Total Number of Unique Products
    $stmtTotal = $pdo->query("SELECT COUNT(*) AS total FROM products");
    $totalItems = $stmtTotal->fetch()['total'];

    // 2. Fetch Count of Out of Stock Items (Quantity is exactly 0)
    $stmtOut = $pdo->query("SELECT COUNT(*) AS total_out FROM products WHERE quantity = 0");
    $outOfStockCount = $stmtOut->fetch()['total_out'];

    // 3. Fetch Count of Low Stock Items (Quantity is above 0 but less than or equal to reorder_level)
    $stmtLow = $pdo->query("SELECT COUNT(*) AS total_low FROM products WHERE quantity > 0 AND quantity <= reorder_level");
    $lowStockCount = $stmtLow->fetch()['total_low'];

    // 4. Fetch the specific items that are running low or out of stock to loop into alerts
    $stmtAlerts = $pdo->query("SELECT name, quantity, reorder_level FROM products WHERE quantity <= reorder_level ORDER BY quantity ASC");
    $alertProducts = $stmtAlerts->fetchAll();

} catch (\PDOException $e) {
    die("Error pulling metrics: " . $e->getMessage());
}
?>

<div class="row mb-4">
    <div class="col-12">
        <div class="p-4 bg-white rounded-4 shadow-sm border-0 d-flex justify-content-between align-items-center">
            <div>
                <h4 class="fw-bold mb-1 text-dark"><?php echo $greeting; ?>, <?php echo htmlspecialchars($_SESSION['username']); ?>! 👋</h4>
                <p class="text-muted small mb-0">Here is what is happening with your stock levels today.</p>
            </div>
            <span class="text-muted small fw-medium"><?php echo date('F d, Y'); ?></span>
        </div>
    </div>
</div>

<?php if (!empty($alertProducts)): ?>
    <?php foreach ($alertProducts as $alertItem): ?>
        <?php if ($alertItem['quantity'] == 0): ?>
            <div class="alert alert-danger border-0 shadow-sm rounded-4 p-3 mb-3 d-flex align-items-center" role="alert">
                <div class="fs-3 me-3">❌</div>
                <div>
                    <h6 class="alert-heading fw-bold mb-1 text-danger-emphasis">Item Completely Empty!</h6>
                    <p class="small mb-0 text-secondary">The product <strong>"<?php echo htmlspecialchars($alertItem['name']); ?>"</strong> is totally out of stock (0 units remaining). Restock immediately.</p>
                </div>
            </div>
        <?php else: ?>
            <div class="alert alert-warning border-0 shadow-sm rounded-4 p-3 mb-3 d-flex align-items-center" role="alert">
                <div class="fs-3 me-3">⚠️</div>
                <div>
                    <h6 class="alert-heading fw-bold mb-1 text-warning-emphasis">Critical Stock Attention Needed!</h6>
                    <p class="small mb-0 text-secondary">The item <strong>"<?php echo htmlspecialchars($alertItem['name']); ?>"</strong> has fallen below its reorder level. Only <strong><?php echo $alertItem['quantity']; ?> units left</strong>.</p>
                </div>
            </div>
        <?php endif; ?>
    <?php endforeach; ?>
<?php endif; ?>

<div class="row g-4 mb-4">
    <div class="col-md-4">
        <div class="card p-4 bg-white h-100 border-0">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <p class="text-secondary small fw-semibold text-uppercase tracking-wider mb-1">Total Tracked Items</p>
                    <h2 class="fw-bold text-dark mb-0"><?php echo $totalItems; ?></h2>
                </div>
                <div class="bg-primary bg-opacity-10 text-primary p-3 rounded-3 fs-4">📦</div>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card p-4 bg-white h-100 border-0">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <p class="text-secondary small fw-semibold text-uppercase tracking-wider mb-1">Low Stock Alerts</p>
                    <h2 class="fw-bold text-warning mb-0"><?php echo $lowStockCount; ?></h2>
                </div>
                <div class="bg-warning bg-opacity-10 text-warning p-3 rounded-3 fs-4">⚠️</div>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card p-4 bg-white h-100 border-0">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <p class="text-secondary small fw-semibold text-uppercase tracking-wider mb-1">Out of Stock</p>
                    <h2 class="fw-bold text-danger mb-0"><?php echo $outOfStockCount; ?></h2>
                </div>
                <div class="bg-danger bg-opacity-10 text-danger p-3 rounded-3 fs-4">❌</div>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    <div class="col-12">
        <h5 class="fw-bold text-dark mb-3">System Navigation</h5>
        <div class="d-flex flex-wrap gap-3">
            <a href="products.php" class="btn btn-primary px-4 py-2">📦 Manage Inventory (CRUD)</a>
            <a href="stock_logs.php" class="btn btn-outline-dark bg-white px-4 py-2">🔄 Log Stock In/Out</a>
            <a href="reports.php" class="btn btn-outline-dark bg-white px-4 py-2">📊 View Reports</a>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>