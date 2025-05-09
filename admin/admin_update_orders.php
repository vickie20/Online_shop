<?php
// admin_orders.php
require_once '../config/admin/db_connection.php'; // Your database connection file

// Check admin authentication here (add your own auth logic)

// Handle status update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $order_id = $_POST['order_id'];
    $new_status = $_POST['status'];
    
    $stmt = $pdo->prepare("UPDATE orders SET status = ? WHERE id = ?");
    $stmt->execute([$new_status, $order_id]);
}

// Fetch all orders
$stmt = $pdo->query("SELECT * FROM orders ORDER BY created_at DESC");
$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<?php include '../logic/side_bar.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Orders Panel</title>
    <style>
     .main-content {
    margin-left: 250px; /* Same as sidebar width */
    padding: 20px;
    transition: margin-left var(--transition-speed);
}

body.sidebar-collapsed .main-content {
    margin-left: 80px; /* Same as collapsed sidebar width */
}
        body { font-family: Arial, sans-serif; margin: 20px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
        select, button { padding: 5px; }
        .status-pending { color: #FFA500; }
        .status-processing { color: #3498db; }
        .status-shipped { color: #9b59b6; }
        .status-delivered { color: #2ecc71; }
        .status-cancelled { color: #e74c3c; }
    </style>
</head>
<body>
<div class="interstellarContent" id="cosmicMainContent">
    <h1>Orders Management</h1>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Customer</th>
                <th>Phone</th>
                <th>Email</th>
                <th>Location</th>
                <th>Amount</th>
                <th>Status</th>
                <th>Date</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($orders as $order): ?>
            <tr>
                <td><?= htmlspecialchars($order['id']) ?></td>
                <td><?= htmlspecialchars($order['customer_name']) ?></td>
                <td><?= htmlspecialchars($order['customer_phone']) ?></td>
                <td><?= htmlspecialchars($order['customer_email']) ?></td>
                <td><?= htmlspecialchars(substr($order['delivery_location'], 0, 50)) ?>...</td>
                <td>$<?= number_format($order['total_amount'], 2) ?></td>
                <td class="status-<?= $order['status'] ?>"><?= ucfirst($order['status']) ?></td>
                <td><?= date('M d, Y H:i', strtotime($order['created_at'])) ?></td>
                <td>
                    <form method="POST" style="display: inline;">
                        <input type="hidden" name="order_id" value="<?= $order['id'] ?>">
                        <select name="status">
                            <option value="pending" <?= $order['status'] === 'pending' ? 'selected' : '' ?>>Pending</option>
                            <option value="processing" <?= $order['status'] === 'processing' ? 'selected' : '' ?>>Processing</option>
                            <option value="shipped" <?= $order['status'] === 'shipped' ? 'selected' : '' ?>>Shipped</option>
                            <option value="delivered" <?= $order['status'] === 'delivered' ? 'selected' : '' ?>>Delivered</option>
                            <option value="cancelled" <?= $order['status'] === 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
                        </select>
                        <button type="submit" name="update_status">Update</button>
                    </form>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    </div>
</body>
</html>