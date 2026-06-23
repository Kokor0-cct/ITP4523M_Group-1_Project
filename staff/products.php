<?php
session_start();
if (!isset($_SESSION['staff_logged_in'])) {
    header('Location: login.php');
    exit;
}
require_once 'db_config.php';

// 处理增加库存（消耗物料）
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_stock'])) {
    $fid = $_POST['fid'];
    $addQty = (int)$_POST['add_qty'];
    if ($addQty <= 0) {
        $error = "Quantity must be positive.";
    } else {
        $pdo->beginTransaction();
        try {
            $matStmt = $pdo->prepare("SELECT mid, pmqty FROM furniturematerials WHERE fid = ?");
            $matStmt->execute([$fid]);
            $materials = $matStmt->fetchAll();
            $enough = true;
            foreach ($materials as $mat) {
                $needed = $mat['pmqty'] * $addQty;
                $check = $pdo->prepare("SELECT mqty FROM materials WHERE mid = ?");
                $check->execute([$mat['mid']]);
                $current = $check->fetchColumn();
                if ($current < $needed) {
                    $enough = false;
                    $error = "Insufficient material: " . $mat['mid'] . " (need $needed, have $current)";
                    break;
                }
            }
            if (!$enough) {
                $pdo->rollBack();
            } else {
                foreach ($materials as $mat) {
                    $needed = $mat['pmqty'] * $addQty;
                    $update = $pdo->prepare("UPDATE materials SET mqty = mqty - ? WHERE mid = ?");
                    $update->execute([$needed, $mat['mid']]);
                }
                $updateStock = $pdo->prepare("UPDATE furnitures SET fStock = fStock + ? WHERE fid = ?");
                $updateStock->execute([$addQty, $fid]);
                $pdo->commit();
                $message = "Stock added successfully. Materials consumed.";
            }
        } catch (Exception $e) {
            $pdo->rollBack();
            $error = "Error: " . $e->getMessage();
        }
    }
    // 重定向以避免表单重复提交
    header('Location: products.php');
    exit;
}

// 处理添加/编辑/删除（原有）
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add'])) {
        $stmt = $pdo->prepare("INSERT INTO Furnitures (fname, fdesc, fSize, fprice, fStock, fImgPath) VALUES (?, ?, ?, ?, 0, '')");
        $stmt->execute([$_POST['name'], $_POST['desc'], $_POST['size'], $_POST['price']]);
    } elseif (isset($_POST['edit'])) {
        $stmt = $pdo->prepare("UPDATE Furnitures SET fname=?, fdesc=?, fSize=?, fprice=? WHERE fid=?");
        $stmt->execute([$_POST['name'], $_POST['desc'], $_POST['size'], $_POST['price'], $_POST['id']]);
    } elseif (isset($_POST['delete'])) {
        $stmt = $pdo->prepare("DELETE FROM Furnitures WHERE fid=?");
        $stmt->execute([$_POST['id']]);
    }
    header('Location: products.php');
    exit;
}

// 获取所有家具，并附带物料信息（用于展示）
$furnitures = $pdo->query("
    SELECT f.*, 
           GROUP_CONCAT(CONCAT(m.mname, '(', fm.pmqty, ' ', m.munit, ')') SEPARATOR ', ') AS materials_used
    FROM furnitures f
    LEFT JOIN furniturematerials fm ON f.fid = fm.fid
    LEFT JOIN materials m ON fm.mid = m.mid
    GROUP BY f.fid
    ORDER BY f.fid DESC
")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Products · Premium Living</title>
    <link rel="stylesheet" href="../CSS/staffStyle.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .table-tools { display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; margin-bottom: 20px; }
        .btn-add { background: #28a745; color: white; padding: 8px 18px; border-radius: 30px; border: none; cursor: pointer; }
        .stock-form { display: inline-block; margin-left: 5px; }
        .stock-form input { width: 60px; padding: 4px; }
        .stock-form button { padding: 4px 10px; background: #007bff; color: white; border: none; border-radius: 4px; cursor: pointer; }
        table input, table button { margin: 2px; }
        .msg { padding: 10px; margin-bottom: 10px; border-radius: 4px; }
        .msg-success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .msg-error { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
    </style>
</head>
<body class="body-staff">
    <div class="nav">
        <a href="dashboard.php">Dashboard</a>
        <a href="products.php" class="active">Products</a>
        <a href="materials.php">Materials</a>
        <a href="orders.php">Orders</a>
        <a href="staff_custom_requests.php">Custom Requests</a>
        <a href="staff_custom_orders.php">Custom Orders</a>
        <a href="logout.php">Logout</a>
    </div>
    <div class="staff-container">
        <div class="table-box">
            <div class="table-tools">
                <h2><i class="fas fa-boxes"></i> Furniture Products</h2>
                <form method="post" style="display:inline;">
                    <input type="hidden" name="add" value="1">
                    <input type="text" name="name" placeholder="Product name" required size="10">
                    <input type="text" name="desc" placeholder="Description" size="12">
                    <input type="text" name="size" placeholder="Size" size="10">
                    <input type="number" step="0.01" name="price" placeholder="Price" required size="6">
                    <button type="submit" class="btn-add">+ Add</button>
                </form>
            </div>
            <?php if (isset($message)): ?>
                <div class="msg msg-success"><?php echo $message; ?></div>
            <?php endif; ?>
            <?php if (isset($error)): ?>
                <div class="msg msg-error"><?php echo $error; ?></div>
            <?php endif; ?>
            <div style="overflow-x: auto;">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Description</th>
                            <th>Size</th>
                            <th>Price (HKD)</th>
                            <th>Stock</th>
                            <th>Materials Used</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($furnitures as $f): ?>
                    <tr>
                        <form method="post" style="display:inline-block;">
                            <td><?php echo $f['fid']; ?><input type="hidden" name="id" value="<?php echo $f['fid']; ?>"></td>
                            <td><input type="text" name="name" value="<?php echo htmlspecialchars($f['fname']); ?>" required></td>
                            <td><input type="text" name="desc" value="<?php echo htmlspecialchars($f['fdesc']); ?>"></td>
                            <td><input type="text" name="size" value="<?php echo htmlspecialchars($f['fSize']); ?>"></td>
                            <td><input type="number" step="0.01" name="price" value="<?php echo $f['fprice']; ?>" required></td>
                            <td><?php echo $f['fStock']; ?></td>
                            <td><?php echo htmlspecialchars($f['materials_used']); ?></td>
                            <td>
                                <button type="submit" name="edit">Save</button>
                                <button type="submit" name="delete" onclick="return confirm('Delete this product?')">Delete</button>
                            </td>
                        </form>
                        <td>
                            <form method="post" class="stock-form">
                                <input type="hidden" name="fid" value="<?php echo $f['fid']; ?>">
                                <input type="number" name="add_qty" min="1" value="1" required>
                                <button type="submit" name="add_stock">+</button>
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
