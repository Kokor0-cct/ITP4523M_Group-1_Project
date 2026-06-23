<?php
session_start();
if (!isset($_SESSION['staff_logged_in'])) {
    header('Location: login.php');
    exit;
}
require_once 'db_config.php';

$cfid = $_GET['cfid'] ?? 0;
$cfrID = $_GET['cfrID'] ?? 0;
if (!$cfid || !$cfrID) die("Missing parameters.");

$cf = $pdo->prepare("SELECT * FROM customefurnitures WHERE cfid = ?");
$cf->execute([$cfid]);
$customFurniture = $cf->fetch();
if (!$customFurniture) die("Custom furniture not found.");

$req = $pdo->prepare("SELECT cUserID FROM customfurniturerequest WHERE cfrID = ?");
$req->execute([$cfrID]);
$request = $req->fetch();
if (!$request) die("Request not found.");
$cid = $request['cUserID'];

$cust = $pdo->prepare("SELECT * FROM customers WHERE cid = ?");
$cust->execute([$cid]);
$customer = $cust->fetch();

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $qty = $_POST['qty'] ?? 1;
    $deliveryDate = $_POST['delivery_date'];
    $address = trim($_POST['address']);
    if (empty($address) || empty($deliveryDate) || $qty <= 0) {
        $error = 'Please fill in all fields.';
    } else {
        $total = $customFurniture['cfprice'] * $qty;
        $stmt = $pdo->prepare("INSERT INTO customeorders (codate, cototalamount, cid, codeliverydate, codeliveraddress, costatus) VALUES (NOW(), ?, ?, ?, ?, 1)");
        $stmt->execute([$total, $cid, $deliveryDate, $address]);
        $coid = $pdo->lastInsertId();

        $stmt2 = $pdo->prepare("INSERT INTO ordercustomefurnitures (coid, cfid, coqty) VALUES (?, ?, ?)");
        $stmt2->execute([$coid, $cfid, $qty]);

        $upd = $pdo->prepare("UPDATE customfurniturerequest SET isComplete = '1' WHERE cfrID = ?");
        $upd->execute([$cfrID]);

        header('Location: staff_custom_orders.php');
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Create Custom Order</title>
    <link rel="stylesheet" href="../CSS/staffStyle.css">
    <style>
        .form-container { max-width: 600px; margin: 0 auto; background: white; padding: 30px; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.1); }
        .form-container label { font-weight: 500; display: block; margin-top: 12px; }
        .form-container input, .form-container textarea { width: 100%; padding: 8px; margin-top: 5px; border-radius: 6px; border: 1px solid #ddd; }
        .btn-submit { margin-top: 20px; background: #007bff; color: white; border: none; padding: 12px; border-radius: 6px; width: 100%; cursor: pointer; }
        .info-box { background: #e9ecef; padding: 15px; border-radius: 6px; margin-bottom: 20px; }
    </style>
</head>
<body class="body-staff">
    <div class="nav">
        <a href="dashboard.php">Dashboard</a>
        <a href="products.php">Products</a>
        <a href="materials.php">Materials</a>
        <a href="orders.php">Orders</a>
        <a href="staff_custom_requests.php">Custom Requests</a>
        <a href="staff_custom_orders.php">Custom Orders</a>
        <a href="logout.php">Logout</a>
    </div>
    <div class="staff-container">
        <h2>Create Custom Order</h2>
        <div class="form-container">
            <?php if ($error): ?><div style="color:red;"><?php echo $error; ?></div><?php endif; ?>
            <div class="info-box">
                <strong>Customer:</strong> <?php echo htmlspecialchars($customer['cname']); ?><br>
                <strong>Furniture:</strong> <?php echo htmlspecialchars($customFurniture['cfname']); ?> (Price: HK$ <?php echo number_format($customFurniture['cfprice'], 2); ?>)<br>
                <strong>Size:</strong> <?php echo htmlspecialchars($customFurniture['cfSize']); ?>
            </div>
            <form method="post">
                <label>Quantity *</label>
                <input type="number" name="qty" min="1" value="1" required>
                <label>Delivery Address *</label>
                <textarea name="address" rows="3" required><?php echo htmlspecialchars($customer['caddr']); ?></textarea>
                <label>Expected Delivery Date *</label>
                <input type="datetime-local" name="delivery_date" required>
                <button type="submit" class="btn-submit">Create Order</button>
            </form>
        </div>
    </div>
</body>
</html>