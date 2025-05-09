<?php
// track_order.php
require_once '../config/admin/db_connection.php'; // Your database connection file

// Function to mask sensitive information
function maskData($data, $visibleChars, $totalVisible = null) {
    if (empty($data)) return '';
    $length = strlen($data);
    $visible = substr($data, 0, $visibleChars);
    $masked = str_repeat('•', max($length - $visibleChars, 0));
    return $visible . $masked;
}

// Fetch all orders (in a real app, you'd filter by user session)
$stmt = $pdo->query("SELECT * FROM orders ORDER BY created_at DESC");
$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<?php include '../logic/navbar.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Tracking</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .tracking-table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        .tracking-table th, .tracking-table td { 
            padding: 12px 15px; 
            text-align: left; 
            border-bottom: 1px solid #e0e0e0; 
        }
        .tracking-table th { 
            background-color: #2c3e50; 
            color: white; 
            font-weight: bold; 
        }
        .tracking-table tr:nth-child(even) { background-color: #f8f9fa; }
        .tracking-table tr:hover { background-color: #f1f1f1; }
        
        .status {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: bold;
            text-transform: uppercase;
        }
        .status-pending { background-color: #FFF3CD; color: #856404; }
        .status-processing { background-color: #CCE5FF; color: #004085; }
        .status-shipped { background-color: #E2D4F0; color: #4B0082; }
        .status-delivered { background-color: #D4EDDA; color: #155724; }
        .status-cancelled { background-color: #F8D7DA; color: #721C24; }
        
        .monospace { font-family: monospace; }
        .text-center { text-align: center; }
    </style>
</head>
<body>
    <h1>Order Tracking</h1>
    <table class="tracking-table">
        <thead>
            <tr>
                <th>Customer</th>
                <th>Phone</th>
                <th>Email</th>
                <th>Location</th>
                <th class="text-center">Status</th>
                <th>Amount</th>
                <th>Date</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($orders as $order): ?>
            <tr>
                <td class="monospace"><?= maskData($order['customer_name'], 3) ?></td>
                <td class="monospace"><?= maskData($order['customer_phone'], 3) ?></td>
                <td class="monospace"><?= maskData($order['customer_email'], 4) ?></td>
                <td class="monospace"><?= maskData($order['delivery_location'], 4) ?></td>
                <td class="text-center">
                    <span class="status status-<?= $order['status'] ?>">
                        <?= ucfirst($order['status']) ?>
                    </span>
                </td>
                <td>$<?= number_format($order['total_amount'], 2) ?></td>
                <td><?= date('M d, Y', strtotime($order['created_at'])) ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</body>
</html>