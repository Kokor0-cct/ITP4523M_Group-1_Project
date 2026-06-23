<?php
session_start();
if (!isset($_SESSION['staff_logged_in'])) {
    header('Location: login.php');
    exit;
}
require_once 'db_config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add'])) {
        $stmt = $pdo->prepare("INSERT INTO Materials (mname, mqty, munit) VALUES (?,?,?)");
        $stmt->execute([$_POST['name'], $_POST['qty'], $_POST['unit']]);
    } elseif (isset($_POST['edit'])) {
        $stmt = $pdo->prepare("UPDATE Materials SET mname=?, mqty=?, munit=? WHERE mid=?");
        $stmt->execute([$_POST['name'], $_POST['qty'], $_POST['unit'], $_POST['id']]);
    } elseif (isset($_POST['delete'])) {
        $stmt = $pdo->prepare("DELETE FROM Materials WHERE mid=?");
        $stmt->execute([$_POST['id']]);
    }
    header('Location: materials.php');
    exit;
}
$materials = $pdo->query("SELECT * FROM Materials ORDER BY mid DESC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Materials · Premium Living</title>
    <link rel="stylesheet" href="../CSS/staffStyle.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .btn-add { background: #28a745; color: white; padding: 8px 18px; border-radius: 30px; border: none; cursor: pointer; }
        .warning-level { background-color: #fff3cd; color: #856404; border-radius: 20px; padding: 4px 10px; font-size: 0.8rem; }
    </style>
</head>
<body class="body-staff">
    <div class="nav">
        <a href="dashboard.php">Dashboard</a>
        <a href="products.php">Products</a>
        <a href="materials.php" class="active">Materials</a>
        <a href="orders.php">Orders</a>
        <a href="staff_custom_requests.php">Custom Requests</a>
        <a href="staff_custom_orders.php">Custom Orders</a>
        <a href="logout.php">Logout</a>
    </div>
    <div class="staff-container">
        <div class="table-box">
            <div class="table-tools">
                <h2><i class="fas fa-wood"></i> Raw Materials Inventory</h2>
                <form method="post" style="display:inline;">
                    <input type="hidden" name="add" value="1">
                    <input type="text" name="name" placeholder="Material name" required size="12">
                    <input type="number" name="qty" placeholder="Stock" required size="6">
                    <input type="text" name="unit" placeholder="Unit" size="6">
                    <button type="submit" class="btn-add">+ Add</button>
                </form>
            </div>
            <div style="overflow-x: auto;">
                <table>
                    <thead><tr><th>ID</th><th>Name</th><th>Stock</th><th>Unit</th><th>Alert</th><th>Actions</th></tr></thead>
                    <tbody>
                    <?php foreach ($materials as $m): ?>
                    <tr>
                        <form method="post">
                            <td><?php echo $m['mid']; ?><input type="hidden" name="id" value="<?php echo $m['mid']; ?>"></td>
                            <td><input type="text" name="name" value="<?php echo htmlspecialchars($m['mname']); ?>" required></td>
                            <td><input type="number" name="qty" value="<?php echo $m['mqty']; ?>" required></td>
                            <td><input type="text" name="unit" value="<?php echo htmlspecialchars($m['munit']); ?>"></td>
                            <td><?php echo ($m['mqty'] < 100) ? '<span class="warning-level"><i class="fas fa-exclamation-triangle"></i> Low stock</span>' : '<span style="color:green;">Sufficient</span>'; ?></td>
                            <td>
                                <button type="submit" name="edit">Save</button>
                                <button type="submit" name="delete" onclick="return confirm('Delete this material?')">Delete</button>
                             </td>
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