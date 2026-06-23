<?php
session_start();
if (!isset($_SESSION['staff_logged_in'])) {
    header('Location: login.php');
    exit;
}
require_once 'db_config.php';

$cfrID = $_GET['cfrID'] ?? 0;
if (!$cfrID) die("Invalid request.");

$req = $pdo->prepare("SELECT cfr.*, c.cname, c.ctel, c.caddr FROM customfurniturerequest cfr JOIN customers c ON cfr.cUserID = c.cid WHERE cfr.cfrID = ?");
$req->execute([$cfrID]);
$request = $req->fetch();
if (!$request) die("Request not found.");

$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $staffNote = trim($_POST['staffNote']);
    $imagePath = '';
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = 'uploads/requests/';
        if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);
        $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
        $filename = uniqid() . '.' . $ext;
        $destination = $upload_dir . $filename;
        if (move_uploaded_file($_FILES['image']['tmp_name'], $destination)) {
            $imagePath = $destination;
        }
    }
    if (!empty($staffNote) || !empty($imagePath)) {
        $sql = "UPDATE customfurniturerequest SET ";
        $params = [];
        if (!empty($staffNote)) {
            $sql .= "cfrStaffNote = ?";
            $params[] = $staffNote;
        }
        if (!empty($imagePath)) {
            if (!empty($staffNote)) $sql .= ", ";
            $sql .= "cfrImage = ?";
            $params[] = $imagePath;
        }
        $sql .= " WHERE cfrID = ?";
        $params[] = $cfrID;
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $message = "Updated successfully.";
        $req->execute([$cfrID]);
        $request = $req->fetch();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Custom Request Detail</title>
    <link rel="stylesheet" href="../CSS/staffStyle.css">
    <style>
        .container { max-width: 800px; margin: 0 auto; background: white; padding: 30px; border-radius: 12px; }
        .info { background: #f9f9f9; padding: 15px; border-radius: 6px; }
        .info label { font-weight: bold; }
        .img-preview { max-width: 200px; max-height: 200px; }
        .btn-primary { background: #007bff; color: white; padding: 6px 16px; border-radius: 4px; text-decoration: none; }
        .btn-primary:hover { background: #0056b3; color: white; }
        .msg { padding: 10px; margin-bottom: 10px; border-radius: 4px; background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
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
        <div class="container">
            <h2>Custom Request #<?php echo $cfrID; ?></h2>
            <?php if ($message): ?>
                <div class="msg"><?php echo $message; ?></div>
            <?php endif; ?>
            <div class="info">
                <p><label>Customer:</label> <?php echo htmlspecialchars($request['cname']); ?></p>
                <p><label>Phone:</label> <?php echo htmlspecialchars($request['ctel']); ?></p>
                <p><label>Address:</label> <?php echo htmlspecialchars($request['caddr']); ?></p>
                <p><label>Budget:</label> HK$ <?php echo number_format($request['cfrBudget'], 2); ?></p>
                <p><label>Description:</label> <?php echo htmlspecialchars($request['cfrDESC']); ?></p>
                <p><label>Created:</label> <?php echo $request['cfrCreateDate']; ?></p>
                <p><label>Status:</label> <?php echo $request['isComplete'] == '1' ? 'Completed' : 'Pending'; ?></p>
                <?php if ($request['cfrImage']): ?>
                    <p><label>Design Image:</label><br><img src="<?php echo htmlspecialchars($request['cfrImage']); ?>" class="img-preview"></p>
                <?php endif; ?>
                <?php if ($request['cfrStaffNote']): ?>
                    <p><label>Staff Note:</label> <?php echo nl2br(htmlspecialchars($request['cfrStaffNote'])); ?></p>
                <?php endif; ?>
            </div>
            <hr>
            <h3>Add Design Image or Staff Note</h3>
            <form method="post" enctype="multipart/form-data">
                <div class="item">
                    <label>Upload Image (optional)</label>
                    <input type="file" name="image" accept="image/*">
                </div>
                <div class="item">
                    <label>Staff Note</label>
                    <textarea name="staffNote" rows="3" style="width:100%;"><?php echo htmlspecialchars($request['cfrStaffNote']); ?></textarea>
                </div>
                <button type="submit">Update</button>
            </form>
            <?php if ($request['isComplete'] == '0'): ?>
                <p><a href="staff_create_custom_furniture.php?cfrID=<?php echo $cfrID; ?>" class="btn-primary">Process Request (Create Furniture)</a></p>
            <?php endif; ?>
            <p><a href="staff_custom_requests.php">← Back to Requests</a></p>
        </div>
    </div>
</body>
</html>