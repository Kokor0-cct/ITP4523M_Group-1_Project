<?php
session_start();
if (!isset($_SESSION['customer']) || !isset($_SESSION['CID'])) {
    header('Location: customerLogin.php');
    exit;
}
$cid = $_SESSION['CID'];
$msg = '';
$msgType = '';

$host = 'localhost';
$dbuser = 'root';
$dbpass = '';
$dbname = 'projectdb';
$conn = mysqli_connect($host, $dbuser, $dbpass, $dbname);
mysqli_set_charset($conn, 'utf8');

if (!$conn) 
    die("DB Connect Error: " . mysqli_connect_error());

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $budget = trim($_POST['budget']);
    $desc = trim($_POST['description']);
    $imgPath = null;

    if (empty($budget) || empty($desc)) {
        $msg = "Please fill budget and custom description!";
        $msgType = 'error';
    } elseif (!is_numeric($budget) || $budget <= 0) {
        $msg = "Budget must be positive number!";
        $msgType = 'error';
    } else {
        if (!empty($_FILES['ref_img']['name'])) {
            $allowType = ['image/jpg','image/jpeg','image/png','image/gif'];
            $fileType = $_FILES['ref_img']['type'];
            $fileSize = $_FILES['ref_img']['size'];
            $maxSize = 2 * 1024 * 1024; // 2MB

            if (!in_array($fileType, $allowType)) {
                $msg = "Only jpg/png/gif images allowed!";
                $msgType = 'error';
            } elseif ($fileSize > $maxSize) {
                $msg = "Image cannot exceed 2MB!";
                $msgType = 'error';
            } else {
                $fileName = time() . '_' . $_FILES['ref_img']['name'];
                $savePath = '../img/' . $fileName;
                if (move_uploaded_file($_FILES['ref_img']['tmp_name'], $savePath)) {
                    $imgPath = $savePath;
                } else {
                    $msg = "Image upload failed, try again!";
                    $msgType = 'error';
                }
            }
        }

        if ($msg === '') {
            $date=date('Y-m-d H:i:s');
            $sql = "INSERT INTO customfurniturerequest (cUserID, cfrBudget, cfrDESC, cfrImage,cfrCreateDate,fcrState) VALUES (?,?,?,?,?,0)";
            $stmt = mysqli_prepare($conn, $sql);
            mysqli_stmt_bind_param($stmt, "idsss", $cid, $budget, $desc, $imgPath,$date);
            if (mysqli_stmt_execute($stmt)) {
                $msg = "Custom request submitted successfully!";
                $msgType = 'success';
                header("Refresh:1; url=CustomProduct.php");
            } else {
                $msg = "Submit failed: " . mysqli_error($conn);
                $msgType = 'error';
            }
        }
    }
}
mysqli_close($conn);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Custom Product Request</title>
    <link rel="stylesheet" href="../CSS/style.css">
    <style>
        .nav a.active {
            color: #007bff;
            font-weight: bold;
        }

        .form-title {
            font-size: 22px;
            font-weight: bold;
            margin-bottom: 25px;
            text-align: center;
        }
        .form-item {
            margin-bottom: 20px;
        }
        .form-item label {
            display: block;
            font-weight: bold;
            margin-bottom: 8px;
        }
        .form-item input, textarea {
            width: 100%;
            box-sizing: border-box;
            padding: 10px;
            border: 1px #ccc solid;
            border-radius: 4px;
        }
        textarea {
            min-height: 150px;
            resize: vertical;
        }
        .submit-btn {
            background: #28a745;
            color: white;
            border: none;
            padding: 12px 30px;
            font-size: 16px;
            border-radius: 4px;
            cursor: pointer;
        }
        .msg-success {
            background: #d4edda;
            color: 15572;
            padding: 10px;
            margin-bottom:20px;
            border-radius:4px;
        }
        .msg-error {
            background: #f8d7da;
            color: #721c24;
            padding: 10px;
            margin-bottom:20px;
            border-radius:4px;
        }
    </style>
</head>
<body>
    <div class="nav">
        <a href="productList.php">💎Browse products</a>
        <a class="active" href="CustomProduct.php">🔧Custom Product</a>
        <a href="ShoppingCart.php">🛒Shopping cart</a>
        <a href="order.php">🧾My Orders</a>
        <a href="myAccount.php">👤My account</a>
    </div>
    <div>
        <a  class="sort-btn" href="CustomProduct.php">🔧Custom Product</a>
        <a  class="sort-btn" href="customList.php">custom List</a>
    </div>
   
    <div class="form-wraps">
        <div class="form-title">Submit Custom Furniture Demand</div>

        <?php if (!empty($msg)): ?>
            <div class="<?= $msgType === 'success' ? 'msg-success' : 'msg-error' ?>">
                <?= $msg ?>
            </div>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data">
            <div class="form-item">
                <label>Expected Budget (USD) <span style="color:red;">*</span></label>
                <input type="number" step="0.01" min="1" name="budget" required placeholder="Input your budget">
            </div>

            <div class="form-item">
                <label>Custom Requirement Description <span style="color:red;">*</span></label>
                <textarea name="description" required placeholder="Describe your ideal furniture: material, size, style, color, structure, usage scene etc."></textarea>
            </div>

            

           

            <div class="form-item">
                <label>Reference Picture (Optional, max 2MB)</label>
                <input type="file" name="ref_img" accept=".jpg,.jpeg,.png,.gif">
            </div>

            <div class="form-item">
                <button type="submit" class="submit-btn">Submit Custom Request</button>
            </div>
        </form>
    </div>
</body>
</html>