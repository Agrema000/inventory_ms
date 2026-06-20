
<?php
require_once 'includes/header.php';
require_once 'config/database.php';

$message = '';
$message_class = '';

// 1. Handle Stock Movement Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $product_id = intval($_POST['product_id']);
    $type = $_POST['type']; // 'in' or 'out'
    $quantity = intval($_POST['quantity']);
    $reason = trim($_POST['reason']);
    $logged_by = $_SESSION['username'] ?? 'System';

    if ($product_id > 0 && $quantity > 0) {
        try {
            // Start a database transaction to ensure both operations succeed together
            $pdo->beginTransaction();

            // Fetch the current stock level first
            $stmtCheck = $pdo->prepare("SELECT quantity FROM products WHERE id = ?");
            $stmtCheck->execute([$product_id]);
            $current_stock = $stmtCheck->fetchColumn();

            if ($current_stock !== false) {
                // Calculate the adjustments
                if ($type === 'in') {
                    $new_quantity = $current_stock + $quantity;
                } else {
                    $new_quantity = $current_stock - $quantity;
                }

                // Business logic check: You can't sell more than what you have!
                if ($new_quantity < 0) {
                    throw new Exception("❌ Operation Denied: Not enough stock available for this transaction.");
                }

                // Update the product's actual balance row
                $stmtUpdate = $pdo->prepare("UPDATE products SET quantity = ? WHERE id = ?");
                $stmtUpdate->execute([$new_quantity, $product_id]);

                // Record the historic movement log details
                $stmtLog = $pdo->prepare("INSERT INTO stock_logs (product_id, type, quantity, reason, logged_by) VALUES (?, ?, ?, ?, ?)");
                $stmtLog->execute([$product_id, $type, $quantity, $reason, $logged_by]);

                // Commit changes safely to the database
                $pdo->commit();

                $message = "✅ Stock updated successfully!";
                $message_class = "alert-success";
            } else {
                throw new Exception("Product not found.");
            }
        } catch (Exception $e) {
            $pdo->rollBack(); // Cancel database alterations if an error happens
            $message = $e->getMessage();
            $message_class = "alert-danger";
        }
    } else {
        $message = "⚠️ Please fill all required fields correctly.";
        $message_class = "alert-warning";
    }
}

// 2. Fetch all unique products to populate the dropdown menu options
$products = $pdo->query("SELECT id, name, quantity FROM products ORDER BY name ASC")->fetchAll();

// 3. Fetch detailed ledger history records 
$logsQuery = "
    SELECT l.*, p.name AS product_name, p.sku 
    FROM stock_logs l 
    JOIN products p ON l.product_id = p.id 
    ORDER BY l.id DESC
";
$logs = $pdo->query($logsQuery)->fetchAll();
?>

<?php if (!empty($message)): ?>
    <div class="alert <?php echo $message_class; ?> border-0 shadow-sm rounded-3 mb-4" role="alert">
        <?php echo $message; ?>
    </div>
<?php endif; ?>

<div class="row g-4">
    <div class="col-md-4">
        <div class="card p-4 bg-white border-0 shadow-sm">
            <h5 class="fw-bold text-dark mb-3">🔄 Log Stock Movement</h5>
            <form action="stock_logs.php" method="POST">
                <div class="mb-3">
                    <label class="form-label small fw-semibold text-secondary">Select Product *</label>
                    <select name="product_id" class="form-select" required>
                        <option value="">-- Choose Item --</option>
                        <?php foreach ($products as $p): ?>
                            <option value="<?php echo $p['id']; ?>">
                                <?php echo htmlspecialchars($p['name']); ?> (Current: <?php echo $p['quantity']; ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label small fw-semibold text-secondary">Movement Type *</label>
                    <div class="d-flex gap-3">
                        <label class="form-check-label text-success fw-bold">
                            <input type="radio" name="type" value="in" class="form-check-input" checked> 📈 Stock-In (Restock)
                        </label>
                        <label class="form-check-label text-danger fw-bold">
                            <input type="radio" name="type" value="out" class="form-check-input"> 📉 Stock-Out (Sale/Usage)
                        </label>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label small fw-semibold text-secondary">Quantity Changes *</label>
                    <input type="number" name="quantity" class="form-control" min="1" placeholder="e.g., 10" required>
                </div>

                <div class="mb-4">
                    <label class="form-label small fw-semibold text-secondary">Note / Reason</label>
                    <input type="text" name="reason" class="form-control" placeholder="e.g., Supplier delivery / Walk-in sale">
                </div>

                <button type="submit" class="btn btn-primary w-100">Apply Adjustments</button>
            </form>
        </div>
    </div>

    <div class="col-md-8">
        <div class="card p-4 bg-white border-0 shadow-sm">
            <h5 class="fw-bold text-dark mb-3">📋 Transaction History Logs</h5>
            
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr class="small text-secondary text-uppercase">
                            <th>Timestamp</th>
                            <th>Item Details</th>
                            <th>Type</th>
                            <th>Qty</th>
                            <th>Reason</th>
                            <th>By</th>
                        </tr>
                    </thead>
                    <tbody class="small">
                        <?php if (empty($logs)): ?>
                            <tr>
                                <td colspan="6" class="text-center text-muted py-4">No adjustments recorded yet.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($logs as $log): ?>
                                <tr>
                                    <td class="text-muted text-nowrap"><?php echo date('M d, H:i', strtotime($log['created_at'])); ?></td>
                                    <td>
                                        <div class="fw-semibold text-dark"><?php echo htmlspecialchars($log['product_name']); ?></div>
                                        <div class="text-muted text-uppercase style-font-size: 11px;"><?php echo htmlspecialchars($log['sku']); ?></div>
                                    </td>
                                    <td>
                                        <?php if ($log['type'] === 'in'): ?>
                                            <span class="badge bg-success bg-opacity-10 text-success px-2 py-1">➕ Stock In</span>
                                        <?php else: ?>
                                            <span class="badge bg-danger bg-opacity-10 text-danger px-2 py-1">➖ Stock Out</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="fw-bold"><?php echo $log['quantity']; ?></td>
                                    <td class="text-secondary"><?php echo htmlspecialchars($log['reason'] ?: '---'); ?></td>
                                    <td class="text-secondary fw-medium"><?php echo htmlspecialchars($log['logged_by']); ?></td>
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