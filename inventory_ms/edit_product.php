<?php
require_once 'includes/header.php';
require_once 'config/database.php';

$message = '';
$message_class = '';
$product = null;

// 1. Fetch the product details based on the ID passed in the URL
if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
    $stmt->execute([$id]);
    $product = $stmt->fetch();

    // If no product matches that ID, send them back to the main products page
    if (!$product) {
        header("Location: products.php");
        exit;
    }
} else {
    header("Location: products.php");
    exit;
}

// 2. Handle Form Submission to Update the Item
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $sku = trim($_POST['sku']);
    $quantity = intval($_POST['quantity']);
    $reorder_level = intval($_POST['reorder_level']);
    $price = floatval($_POST['price']);

    if (!empty($name) && !empty($sku)) {
        try {
            $stmt = $pdo->prepare("UPDATE products SET name = ?, sku = ?, quantity = ?, reorder_level = ?, price = ? WHERE id = ?");
            $stmt->execute([$name, $sku, $quantity, $reorder_level, $price, $id]);
            
            // Refresh local product array variables to display updated values in the form fields
            $product['name'] = $name;
            $product['sku'] = $sku;
            $product['quantity'] = $quantity;
            $product['reorder_level'] = $reorder_level;
            $product['price'] = $price;

            $message = "✨ Product updated successfully! <a href='products.php' class='fw-bold text-success text-decoration-none ms-2'>← Back to Inventory</a>";
            $message_class = "alert-success";
        } catch (\PDOException $e) {
            if ($e->getCode() == 23000) {
                $message = "❌ Error: This SKU/Code is already taken by another item.";
                $message_class = "alert-danger";
            } else {
                $message = "❌ Error: " . $e->getMessage();
                $message_class = "alert-danger";
            }
        }
    } else {
        $message = "⚠️ Required fields cannot be empty.";
        $message_class = "alert-warning";
    }
}
?>

<div class="row justify-content-center">
    <div class="col-md-6">
        
        <?php if (!empty($message)): ?>
            <div class="alert <?php echo $message_class; ?> border-0 shadow-sm rounded-3 mb-4" role="alert">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>

        <div class="card p-4 bg-white border-0 shadow-sm">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h5 class="fw-bold text-dark mb-0">✏️ Edit Product Details</h5>
                <a href="products.php" class="btn btn-sm btn-light border">Cancel</a>
            </div>

            <form action="edit_product.php?id=<?php echo $id; ?>" method="POST">
                <div class="mb-3">
                    <label class="form-label small fw-semibold text-secondary">Item Name *</label>
                    <input type="text" name="name" class="form-control" value="<?php echo htmlspecialchars($product['name']); ?>" required>
                </div>
                
                <div class="mb-3">
                    <label class="form-label small fw-semibold text-secondary">SKU / Item Code *</label>
                    <input type="text" name="sku" class="form-control" value="<?php echo htmlspecialchars($product['sku']); ?>" required>
                </div>
                
                <div class="row g-2 mb-3">
                    <div class="col-6">
                        <label class="form-label small fw-semibold text-secondary">Current Stock Balance</label>
                        <input type="number" name="quantity" class="form-control" value="<?php echo $product['quantity']; ?>" min="0">
                    </div>
                    <div class="col-6">
                        <label class="form-label small fw-semibold text-secondary">Alert Threshold</label>
                        <input type="number" name="reorder_level" class="form-control" value="<?php echo $product['reorder_level']; ?>" min="0">
                    </div>
                </div>
                
                <div class="mb-4">
                    <label class="form-label small fw-semibold text-secondary">Price (₦) *</label>
                    <input type="number" step="0.01" name="price" class="form-control" value="<?php echo $product['price']; ?>" required>
                </div>
                
                <button type="submit" class="btn btn-primary w-100">Update Records</button>
            </form>
        </div>

    </div>
</div>

<?php require_once 'includes/footer.php'; ?>