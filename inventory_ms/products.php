<?php
require_once 'includes/header.php';
require_once 'config/database.php';

$message = '';
$message_class = '';

// Handle form submission to add a new product
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $sku = trim($_POST['sku']);
    $quantity = intval($_POST['quantity']);
    $reorder_level = intval($_POST['reorder_level']);
    $price = floatval($_POST['price']);

    if (!empty($name) && !empty($sku)) {
        try {
            $stmt = $pdo->prepare("INSERT INTO products (name, sku, quantity, reorder_level, price) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$name, $sku, $quantity, $reorder_level, $price]);
            
            $message = "🎉 Product added successfully!";
            $message_class = "alert-success";
        } catch (\PDOException $e) {
            // Handle unique constraint violation for duplicate SKU
            if ($e->getCode() == 23000) {
                $message = "❌ Error: An item with this SKU/Code already exists.";
                $message_class = "alert-danger";
            } else {
                $message = "❌ Error: " . $e->getMessage();
                $message_class = "alert-danger";
            }
        }
    } else {
        $message = "⚠️ Please fill in all required fields.";
        $message_class = "alert-warning";
    }
}

// Fetch all products to display in the table
$stmt = $pdo->query("SELECT * FROM products ORDER BY id DESC");
$products = $stmt->fetchAll();
?>

<?php if (!empty($message)): ?>
    <div class="alert <?php echo $message_class; ?> border-0 shadow-sm rounded-3 mb-4" role="alert">
        <?php echo $message; ?>
    </div>
<?php endif; ?>

<div class="row g-4">
    <div class="col-md-4">
        <div class="card p-4 bg-white border-0 shadow-sm">
            <h5 class="fw-bold text-dark mb-3">➕ Add New Item</h5>
            <form action="products.php" method="POST">
                <div class="mb-3">
                    <label class="form-label small fw-semibold text-secondary">Item Name *</label>
                    <input type="text" name="name" class="form-control" placeholder="e.g., Elite Premium Soap" required>
                </div>
                <div class="mb-3">
                    <label class="form-label small fw-semibold text-secondary">SKU / Item Code *</label>
                    <input type="text" name="sku" class="form-control" placeholder="e.g., EPS-001" required>
                </div>
                <div class="row g-2 mb-3">
                    <div class="col-6">
                        <label class="form-label small fw-semibold text-secondary">Initial Stock</label>
                        <input type="number" name="quantity" class="form-control" value="0" min="0">
                    </div>
                    <div class="col-6">
                        <label class="form-label small fw-semibold text-secondary">Alert Threshold</label>
                        <input type="number" name="reorder_level" class="form-control" value="5" min="0">
                    </div>
                </div>
                <div class="mb-4">
                    <label class="form-label small fw-semibold text-secondary">Price (₦) *</label>
                    <input type="number" step="0.01" name="price" class="form-control" placeholder="0.00" required>
                </div>
                <button type="submit" class="btn btn-primary w-100">Save to Inventory</button>
            </form>
        </div>
    </div>

    <div class="col-md-8">
        <div class="card p-4 bg-white border-0 shadow-sm">
            <h5 class="fw-bold text-dark mb-3">📦 Live Stock Records</h5>
            
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr class="small text-secondary text-uppercase">
                            <th>SKU</th>
                            <th>Name</th>
                            <th>Price</th>
                            <th>Qty</th>
                            <th>Status</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="small">
                        <?php if (empty($products)): ?>
                            <tr>
                                <td colspan="6" class="text-center text-muted py-4">No products found in the inventory database.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($products as $item): ?>
                                <?php 
                                    // Determine badges color based on quantity thresholds
                                    if ($item['quantity'] == 0) {
                                        $status_badge = '<span class="badge bg-danger bg-opacity-10 text-danger px-2 py-1">Out of Stock</span>';
                                        $row_class = 'table-danger bg-opacity-25';
                                    } elseif ($item['quantity'] <= $item['reorder_level']) {
                                        $status_badge = '<span class="badge bg-warning bg-opacity-10 text-warning-emphasis px-2 py-1">Low Stock</span>';
                                        $row_class = '';
                                    } else {
                                        $status_badge = '<span class="badge bg-success bg-opacity-10 text-success px-2 py-1">Healthy</span>';
                                        $row_class = '';
                                    }
                                ?>
                                <tr class="<?php echo $row_class; ?>">
                                    <td class="fw-bold text-secondary"><?php echo htmlspecialchars($item['sku']); ?></td>
                                    <td class="fw-semibold text-dark"><?php echo htmlspecialchars($item['name']); ?></td>
                                    <td>₦<?php echo number_format($item['price'], 2); ?></td>
                                    <td class="fw-bold"><?php echo $item['quantity']; ?></td>
                                    <td><?php echo $status_badge; ?></td>
                                    <td class="text-end">
                                        <a href="edit_product.php?id=<?php echo $item['id']; ?>" class="btn btn-sm btn-outline-primary py-0 px-2 small me-1">Edit</a>
                                        <a href="delete_product.php?id=<?php echo $item['id']; ?>" class="btn btn-sm btn-outline-danger py-0 px-2 small" onclick="return confirm('Are you sure you want to delete this item?');">Delete</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>