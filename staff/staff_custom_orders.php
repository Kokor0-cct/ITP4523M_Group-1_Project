
<?php
session_start();
if (!isset($_SESSION['staff_logged_in'])) {
    header('Location: login.php');
    exit;
}
require_once 'db_config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $coid = $_POST['coid'];
    $status = $_POST['costatus'];
    $stmt = $pdo->prepare("UPDATE customeorders SET costatus = ? WHERE coid = ?");
    $stmt->execute([$status, $coid]);
    header('Location: staff_custom_orders.php');
    exit;
}

$orders = $pdo->query("
    SELECT co.*, c.cname, c.ctel,
           GROUP_CONCAT(CONCAT(cf.cfname, ' x', ocf.coqty) SEPARATOR ', ') AS items
    FROM customeorders co
    JOIN customers c ON co.cid = c.cid
    LEFT JOIN ordercustomefurnitures ocf ON co.coid = ocf.coid
    LEFT JOIN customefurnitures cf ON ocf.cfid = cf.cfid
    GROUP BY co.coid
    ORDER BY co.codate DESC
")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Custom Orders · Staff</title>
    <link rel="stylesheet" href="../CSS/staffStyle.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .status-badge { display: inline-block; padding: 4px 12px; border-radius: 20px; font-size: 0.8rem; }
        .status-1 { background: #ffc107; color: #333; }
        .status-2 { background: #17a2b8; color: white; }
        .status-3 { background: #6f42c1; color: white; }
        .status-4 { background: #28a745; color: white; }
        .status-5 { background: #dc3545; color: white; }
        .update-form { display: inline-block; }
    </style>
</head>
<body class="body-staff">
    <div class="nav">
        <a href="dashboard.php">Dashboard</a>
        <a href="products.php">Products</a>
        <a href="materials.php">Materials</a>
        <a href="orders.php">Orders</a>
        <a href="staff_custom_requests.php">Custom Requests</a>
        <a href="staff_custom_orders.php" class="active">Custom Orders</a>
        <a href="logout.php">Logout</a>
    </div>
    <div class="staff-container">
        <div class="table-box">
            <h2><i class="fas fa-truck"></i> Custom Orders</h2>
            <div style="overflow-x: auto;">
                <table>
                    <thead>
                        <tr>
                            <th>Order ID</th>
                            <th>Customer</th>
                            <th>Items</th>
                            <th>Total</th>
                            <th>Order Date</th>
                            <th>Delivery Date</th>
                            <th>Address</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($orders as $o): ?>
                    <tr>
                        <td>#<?php echo $o['coid']; ?></td>
                        <td><?php echo htmlspecialchars($o['cname']); ?><br><small><?php echo htmlspecialchars($o['ctel']); ?></small></td>
                        <td><?php echo htmlspecialchars($o['items']); ?></td>
                        <td>HK$ <?php echo number_format($o['cototalamount'], 2); ?></td>
                        <td><?php echo $o['codate']; ?></td>
                        <td><?php echo $o['codeliverydate']; ?></td>
                        <td><small><?php echo htmlspecialchars(substr($o['codeliveraddress'], 0, 30)); ?>...</small></td>
                        <td><span class="status-badge status-<?php echo $o['costatus']; ?>"><?php echo ['Pending','Processing','Shipped','Delivered','Cancelled'][$o['costatus']-1]; ?></span></td>
                        <td>
                            <form method="post" class="update-form">
                                <input type="hidden" name="coid" value="<?php echo $o['coid']; ?>">
                                <select name="costatus">
                                    <option value="1" <?php if ($o['costatus']==1) echo 'selected'; ?>>Pending</option>
                                    <option value="2" <?php if ($o['costatus']==2) echo 'selected'; ?>>Processing</option>
                                    <option value="3" <?php if ($o['costatus']==3) echo 'selected'; ?>>Shipped</option>
                                    <option value="4" <?php if ($o['costatus']==4) echo 'selected'; ?>>Delivered</option>
                                    <option value="5" <?php if ($o['costatus']==5) echo 'selected'; ?>>Cancelled</option>
                                </select>
                                <button type="submit" name="update_status">Update</button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>