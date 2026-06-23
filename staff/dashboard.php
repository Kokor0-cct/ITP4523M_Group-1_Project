<?php
session_start();
if (!isset($_SESSION['staff_logged_in'])) {
    header('Location: login.php');
    exit;
}
require_once 'db_config.php';

$productCount = $pdo->query("SELECT COUNT(*) FROM Furnitures")->fetchColumn();
$materialCount = $pdo->query("SELECT COUNT(*) FROM Materials")->fetchColumn();
$orderCount = $pdo->query("SELECT COUNT(*) FROM Orders")->fetchColumn();
$pendingCount = $pdo->query("SELECT COUNT(*) FROM Orders WHERE ostatus IN (1,2)")->fetchColumn();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Staff Dashboard · Premium Living</title>
    <link rel="stylesheet" href="../CSS/staffStyle.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .stats-row { display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 20px; margin-bottom: 40px; }
        .stat-card { background: white; border-radius: 16px; padding: 1.5rem; text-align: center; box-shadow: 0 4px 12px rgba(0,0,0,0.05); }
        .stat-card i { font-size: 2rem; color: #007bff; margin-bottom: 0.5rem; }
        .stat-card .number { font-size: 2rem; font-weight: 700; color: #2c3e2f; }
    </style>
</head>
<body class="body-staff">
    <div class="nav">
        <a href="dashboard.php" class="active"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
        <a href="products.php"><i class="fas fa-chair"></i> Products</a>
        <a href="materials.php"><i class="fas fa-cubes"></i> Materials</a>
        <a href="orders.php"><i class="fas fa-truck"></i> Orders</a>
         <a href="staff_custom_requests.php">Custom Requests</a>
        <a href="staff_custom_orders.php">Custom Orders</a>
        <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
       
    </div>
    <div class="staff-container">
        <div class="welcome-box">
            <h1><i class="fas fa-user-check"></i> Welcome, <?php echo htmlspecialchars($_SESSION['staff_name']); ?></h1>
            <p>Premium Living Furniture Integrated Hub | Real‑time management</p>
        </div>
        <div class="stats-row">
            <div class="stat-card"><i class="fas fa-couch"></i><div class="number"><?php echo $productCount; ?></div><div>Furniture Products</div></div>
            <div class="stat-card"><i class="fas fa-boxes"></i><div class="number"><?php echo $materialCount; ?></div><div>Raw Materials</div></div>
            <div class="stat-card"><i class="fas fa-clipboard-list"></i><div class="number"><?php echo $orderCount; ?></div><div>Total Orders</div></div>
            <div class="stat-card"><i class="fas fa-clock"></i><div class="number"><?php echo $pendingCount; ?></div><div>Pending Orders</div></div>
        </div>
        <div class="menu-card-wrap">
            <div class="menu-card"><i class="fas fa-chair" style="font-size: 2.5rem; color:#007bff;"></i><h3>Furniture Products</h3><p>Add/Edit/Delete products</p><a href="products.php">Manage →</a></div>
            <div class="menu-card"><i class="fas fa-cubes" style="font-size: 2.5rem; color:#28a745;"></i><h3>Raw Materials</h3><p>Monitor stock, reorder alerts</p><a href="materials.php">Manage →</a></div>
            <div class="menu-card"><i class="fas fa-truck-fast" style="font-size: 2.5rem; color:#ff8800;"></i><h3>Order Fulfillment</h3><p>Update status, tracking, delivery</p><a href="orders.php">Update →</a></div>
        </div>
    </div>
</body>
</html>