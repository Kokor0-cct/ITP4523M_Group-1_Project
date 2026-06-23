<?php
session_start();
if (!isset($_SESSION['staff_logged_in'])) {
    header('Location: login.php');
    exit;
}
require_once 'db_config.php';

$requests = $pdo->query("
    SELECT cfr.*, c.cname, c.ctel 
    FROM customfurniturerequest cfr 
    JOIN customers c ON cfr.cUserID = c.cid 
    ORDER BY cfr.cfrCreateDate DESC
")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Custom Requests · Staff</title>
    <link rel="stylesheet" href="../CSS/staffStyle.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .badge { display: inline-block; padding: 4px 12px; border-radius: 20px; font-size: 0.8rem; }
        .badge-pending { background: #ffc107; color: #333; }
        .badge-complete { background: #28a745; color: white; }
        .action-btn { background: #007bff; color: white; padding: 4px 12px; border-radius: 4px; text-decoration: none; }
        .action-btn:hover { background: #0056b3; color: white; }
    </style>
</head>
<body class="body-staff">
    <div class="nav">
        <a href="dashboard.php">Dashboard</a>
        <a href="products.php">Products</a>
        <a href="materials.php">Materials</a>
        <a href="orders.php">Orders</a>
        <a href="staff_custom_requests.php" class="active">Custom Requests</a>
        <a href="staff_custom_orders.php">Custom Orders</a>
        <a href="logout.php">Logout</a>
    </div>
    <div class="staff-container">
        <div class="table-box">
            <h2><i class="fas fa-pencil-ruler"></i> Custom Furniture Requests</h2>
            <div style="overflow-x: auto;">
                <table>
                    <thead>
                        <tr>
                            <th>Request ID</th>
                            <th>Customer</th>
                            <th>Budget</th>
                            <th>Description</th>
                            <th>Created</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($requests as $r): ?>
                    <tr>
                        <td>#<?php echo $r['cfrID']; ?></td>
                        <td><?php echo htmlspecialchars($r['cname']); ?><br><small><?php echo htmlspecialchars($r['ctel']); ?></small></td>
                        <td>HK$ <?php echo number_format($r['cfrBudget'], 2); ?></td>
                        <td><?php echo htmlspecialchars(substr($r['cfrDESC'], 0, 60)); ?>...</td>
                        <td><?php echo $r['cfrCreateDate']; ?></td>
                        <td><span class="badge <?php echo $r['isComplete'] == '1' ? 'badge-complete' : 'badge-pending'; ?>"><?php echo $r['isComplete'] == '1' ? 'Completed' : 'Pending'; ?></span></td>
                        <td>
                            <?php if ($r['isComplete'] == '0'): ?>
                                <a href="staff_custom_request_detail.php?cfrID=<?php echo $r['cfrID']; ?>" class="action-btn">View Details</a>
                            <?php else: ?>
                                <span class="text-muted">Done</span>
                            <?php endif; ?>
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