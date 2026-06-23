<?php
session_start();
if (!isset($_SESSION['staff_logged_in'])) {
    header('Location: login.php');
    exit;
}
require_once 'db_config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_order'])) {
    $stmt = $pdo->prepare("UPDATE Orders SET ostatus=?, odeliverydate=? WHERE oid=?");
    $stmt->execute([$_POST['status'], $_POST['delivery_date'], $_POST['order_id']]);
    header('Location: orders.php');
    exit;
}
$orders = $pdo->query("SELECT o.*, c.cname FROM Orders o JOIN Customers c ON o.cid = c.cid ORDER BY o.odate DESC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Orders · Premium Living</title>
    <link rel="stylesheet" href="../CSS/staffStyle.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .order-table input, .order-table select { width: 100%; padding: 6px; border-radius: 8px; border: 1px solid #ccc; }
        .update-btn { background: #007bff; color: white; border: none; padding: 6px 12px; border-radius: 30px; cursor: pointer; }
    </style>
</head>
<body class="body-staff">
    <div class="nav">
        <a href="dashboard.php">Dashboard</a>
        <a href="products.php">Products</a>
        <a href="materials.php">Materials</a>
        <a href="orders.php" class="active">Orders</a>
        <a href="staff_custom_requests.php">Custom Requests</a>
        <a href="staff_custom_orders.php">Custom Orders</a>
        <a href="logout.php">Logout</a>
    </div>
    <div class="staff-container">
        <div class="table-box">
            <h2><i class="fas fa-pen-ruler"></i> Customer Orders & Updates</h2>
            <div style="overflow-x: auto;">
                <table class="order-table">
                    <thead>
                        <tr>
                            <th>Order ID</th>
                            <th>Customer</th>
                            <th>Total</th>
                            <th>Order Date</th>
                            <th>Status</th>
                            <th>Delivery Date</th>
                            <th>Address</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($orders as $o): ?>
                    <tr>
                        <form method="post">
                            <td>#<?php echo $o['oid']; ?><input type="hidden" name="order_id" value="<?php echo $o['oid']; ?>"></td>
                            <td><?php echo htmlspecialchars($o['cname']); ?></td>
                            <td>HK$ <?php echo number_format($o['ototalamount'], 2); ?></td>
                            <td><?php echo $o['odate']; ?></td>
                            <td>
                                <select name="status">
                                    <option value="1" <?php if ($o['ostatus']==1) echo 'selected'; ?>>Pending</option>
                                    <option value="2" <?php if ($o['ostatus']==2) echo 'selected'; ?>>Processing</option>
                                    <option value="3" <?php if ($o['ostatus']==3) echo 'selected'; ?>>Shipped</option>
                                    <option value="4" <?php if ($o['ostatus']==4) echo 'selected'; ?>>Delivered</option>
                                    <option value="5" <?php if ($o['ostatus']==5) echo 'selected'; ?>>Cancelled</option>
                                </select>
                            </td>
                            <td>
                                <input type="datetime-local" name="delivery_date" value="<?php echo date('Y-m-d\TH:i', strtotime($o['odeliverydate'])); ?>">
                            </td>
                            <td><small><?php echo htmlspecialchars(substr($o['odeliveraddress'], 0, 30)); ?></small></td>
                            <td><button type="submit" name="update_order" class="update-btn">Update</button></td>
                        </form>
                    </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>

