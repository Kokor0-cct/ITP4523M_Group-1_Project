<?php
session_start();
if (!isset($_SESSION['staff_logged_in'])) {
    header('Location: login.php');
    exit;
}
require_once 'db_config.php';

$cfrID = $_GET['cfrID'] ?? 0;
if (!$cfrID) die("Invalid request.");

$req = $pdo->prepare("SELECT * FROM customfurniturerequest WHERE cfrID = ?");
$req->execute([$cfrID]);
$request = $req->fetch();
if (!$request) die("Request not found.");

$materials = $pdo->query("SELECT mid, mname, munit FROM materials ORDER BY mname")->fetchAll();

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fname = trim($_POST['fname']);
    $fdesc = trim($_POST['fdesc']);
    $fSize = trim($_POST['fSize']);
    $fprice = $_POST['fprice'];
    if (empty($fname) || empty($fSize) || $fprice <= 0) {
        $error = 'Please fill in name, size and price.';
    } else {
        $imgPath = '';
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $upload_dir = 'uploads/custom/';
            if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);
            $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
            $filename = uniqid() . '.' . $ext;
            $destination = $upload_dir . $filename;
            if (move_uploaded_file($_FILES['image']['tmp_name'], $destination)) {
                $imgPath = $destination;
            } else {
                $error = 'Image upload failed.';
            }
        }
        if (empty($error)) {
            $stmt = $pdo->prepare("INSERT INTO customefurnitures (cfname, cfdesc, cfSize, cfprice, cfStock, cfImgPath) VALUES (?, ?, ?, ?, 0, ?)");
            $stmt->execute([$fname, $fdesc, $fSize, $fprice, $imgPath]);
            $cfid = $pdo->lastInsertId();

            if (isset($_POST['material'])) {
                foreach ($_POST['material'] as $mid => $qty) {
                    if ($qty > 0) {
                        $stmt2 = $pdo->prepare("INSERT INTO customefurnituresmaterials (cfid, mid, pmqty) VALUES (?, ?, ?)");
                        $stmt2->execute([$cfid, $mid, $qty]);
                    }
                }
            }

            $upd = $pdo->prepare("UPDATE customfurniturerequest SET isComplete = '1' WHERE cfrID = ?");
            $upd->execute([$cfrID]);

            header("Location: staff_create_custom_order.php?cfid=$cfid&cfrID=$cfrID");
            exit;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Create Custom Furniture</title>
    <link rel="stylesheet" href="../CSS/staffStyle.css">
    <style>
        .form-container { max-width: 700px; margin: 0 auto; background: white; padding: 30px; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.1); }
        .form-container label { font-weight: 500; display: block; margin-top: 12px; }
        .form-container input, .form-container textarea { width: 100%; padding: 8px; margin-top: 5px; border-radius: 6px; border: 1px solid #ddd; }
        .material-row { display: flex; gap: 10px; align-items: center; margin-bottom: 8px; }
        .material-row label { width: 150px; margin: 0; }
        .material-row input { width: 80px; }
        .btn-submit { margin-top: 20px; background: #28a745; color: white; border: none; padding: 12px; border-radius: 6px; width: 100%; cursor: pointer; }
        .error { color: red; }
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
        <h2>Create Custom Furniture from Request #<?php echo $cfrID; ?></h2>
        <div class="form-container">
            <?php if ($error): ?><div class="error"><?php echo $error; ?></div><?php endif; ?>
            <form method="post" enctype="multipart/form-data">
                <label>Furniture Name *</label>
                <input type="text" name="fname" required>
                <label>Description</label>
                <textarea name="fdesc" rows="3"></textarea>
                <label>Size (e.g., 85cm*85cm*90cm) *</label>
                <input type="text" name="fSize" required>
                <label>Price (HKD) *</label>
                <input type="number" step="0.01" name="fprice" required>
                <label>Upload Image (optional)</label>
                <input type="file" name="image" accept="image/*">
                <label>Materials Required</label>
                <?php foreach ($materials as $m): ?>
                <div class="material-row">
                    <label><?php echo htmlspecialchars($m['mname']); ?> (<?php echo htmlspecialchars($m['munit']); ?>)</label>
                    <input type="number" name="material[<?php echo $m['mid']; ?>]" min="0" step="1" value="0">
                </div>
                <?php endforeach; ?>
                <button type="submit" class="btn-submit">Create Furniture & Proceed to Order</button>
            </form>
        </div>
    </div>
</body>
</html>