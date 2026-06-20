<?php
require_once 'includes/header.php';
require_once 'config/database.php';

try {
    // 1. Calculate Total Valuation of current inventory (Sum of Qty * Price)
    $stmtValuation = $pdo->query("SELECT SUM(quantity * price) AS total_valuation FROM products");
    $totalValuation = $stmtValuation->fetch()['total_valuation'] ?? 0;

    // 2. Identify the highest valued product asset in stock
    $stmtHighestAsset = $pdo->query("SELECT name, (quantity * price) AS asset_value FROM products WHERE quantity > 0 ORDER BY asset_value DESC LIMIT 1");
    $highestAsset = $stmtHighestAsset->fetch();

    // 3. Count total historical actions processed through logs
    $stmtTotalLogs = $pdo->query("SELECT COUNT(*) AS total_actions FROM stock_logs");
    $totalActions = $stmtTotalLogs->fetch()['total_actions'] ?? 0;

    // 4. Breakdown stock movements to analyze distributions
    $stmtInCount = $pdo->query("SELECT COUNT(*) AS total_in FROM stock_logs WHERE type = 'in'");
    $restockCount = $stmtInCount->fetch()['total_in'] ?? 0;

    $stmtOutCount = $pdo->query("SELECT COUNT(*) AS total_out FROM stock_logs WHERE type = 'out'");
    $salesCount = $stmtOutCount->fetch()['total_out'] ?? 0;

    // 5. Fetch a quick inventory summary layout breakdown for audit
    $stmtSummary = $pdo->query("SELECT name, sku, quantity, price, (quantity * price) AS item_total FROM products ORDER BY item_total DESC");
    $summaryList = $stmtSummary->fetchAll();

} catch (\PDOException $e) {
    die("Analytics Error: " . $e->getMessage());
}
?>

<div class="row mb-4">
    <div class="col-12">
        <div class="p-4 bg-white rounded-4 shadow-sm border-0 d-flex justify-content-between align-items-center">
            <div>
                <h4 class="fw-bold mb-1 text-dark">📊 Reports & Inventory Analytics</h4>
                <p class="text-muted small mb-0">Financial value assessments and total warehouse metrics breakdown.</p>
            </div>
            <a href="dashboard.php" class="btn btn-sm btn-outline-secondary px-3">← Back Home</a>
        </div>
    </div>
</div>

<div class="row g-4 mb-4">
    <div class="col-md-4">
        <div class="card p-4 bg-white h-100 border-0 shadow-sm">
            <p class="text-secondary small fw-semibold text-uppercase tracking-wider mb-1">Total Stock Valuation</p>
            <h2 class="fw-bold text-success mb-2">₦<?php echo number_format($totalValuation, 2); ?></h2>
            <span class="text-muted small">Total asset wealth on warehouse shelves.</span>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card p-4 bg-white h-100 border-0 shadow-sm">
            <p class="text-secondary small fw-semibold text-uppercase tracking-wider mb-1">Top Financial Asset</p>
            <h4 class="fw-bold text-dark mb-1 text-truncate mt-1">
                <?php echo $highestAsset ? htmlspecialchars($highestAsset['name']) : 'None Yet'; ?>
            </h4>
            <span class="text-muted small d-block mb-0">
                Asset Holding Value: <strong class="text-dark">₦<?php echo number_format($highestAsset['asset_value'] ?? 0, 2); ?></strong>
            </span>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card p-4 bg-white h-100 border-0 shadow-sm">
            <p class="text-secondary small fw-semibold text-uppercase tracking-wider mb-1">Log Activity Load</p>
            <h2 class="fw-bold text-dark mb-2"><?php echo $totalActions; ?> <span class="fs-6 text-muted fw-normal">Total Updates</span></h2>
            <div class="d-flex gap-3 small mt-1">
                <span class="text-success">📈 <?php echo $restockCount; ?> Restocks</span>
                <span class="text-danger">📉 <?php echo $salesCount; ?> Dispatches</span>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card p-4 bg-white border-0 shadow-sm">
            <h5 class="fw-bold text-dark mb-3">💰 Stock Value Assessment Breakdown</h5>
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr class="small text-secondary text-uppercase">
                            <th>SKU Code</th>
                            <th>Product Name</th>
                            <th>Unit Cost</th>
                            <th>Units on Hand</th>
                            <th class="text-end">Total Asset Value</th>
                        </tr>
                    </thead>
                    <tbody class="small">
                        <?php if (empty($summaryList)): ?>
                            <tr>
                                <td colspan="5" class="text-center text-muted py-4">No inventory items available to assess valuation.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($summaryList as $summaryItem): ?>
                                <tr>
                                    <td class="fw-bold text-secondary text-uppercase"><?php echo htmlspecialchars($summaryItem['sku']); ?></td>
                                    <td class="fw-semibold text-dark"><?php echo htmlspecialchars($summaryItem['name']); ?></td>
                                    <td>₦<?php echo number_format($summaryItem['price'], 2); ?></td>
                                    <td class="fw-bold text-secondary"><?php echo $summaryItem['quantity']; ?></td>
                                    <td class="text-end fw-bold text-dark">₦<?php echo number_format($summaryItem['item_total'], 2); ?></td>
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