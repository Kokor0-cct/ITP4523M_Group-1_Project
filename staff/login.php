<?php
session_start();
require_once 'db_config.php';

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];
    // 注意：Staffs 表中没有 email 字段，我们使用 sname 作为用户名
    $stmt = $pdo->prepare("SELECT * FROM Staffs WHERE sname = ? AND spassword = ?");
    $stmt->execute([$email, $password]);
    $staff = $stmt->fetch();
    if ($staff) {
        $_SESSION['staff_logged_in'] = true;
        $_SESSION['staff_name'] = $staff['sname'];
        $_SESSION['staff_role'] = $staff['srole'];
        header('Location: dashboard.php');
        exit;
    } else {
        $error = 'Invalid email or password';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Staff Login · Premium Living Management System</title>
    <link rel="stylesheet" href="../CSS/staffStyle.css">
    <style>
        .demo-hint {
            background: #e9ecef;
            padding: 10px;
            border-radius: 8px;
            margin-top: 15px;
            font-size: 13px;
            text-align: center;
            color: #495057;
        }
        .background_img{
            background-image: url(../img/background.png);
            background-size: cover;    
            background-position: center; 
            background-repeat: no-repeat; 
            background-attachment: fixed; 
        }
        .back-home-btn {
            margin-top: 15px;
            background: #6c757d;
            width: auto;
            padding: 8px 16px;
        }
    </style>
</head>
<body class="background_img">
    <div class="login">
        <h2> Staff Login Portal</h2>
        <?php if ($error): ?>
            <div id="tip" style="color:red; margin-top: 12px;"><?php echo $error; ?></div>
        <?php endif; ?>
        <form method="post">
            <div class="item">
                <label>Username </label>
                <input type="text" name="email" required>
            </div>
            <div class="item">
                <label>Password</label>
                <input type="password" name="password" placeholder="Any password (demo)" required>
            </div>
            <button type="submit">Login</button>
        </form>
        <div class="demo-hint">
            Demo credentials: <strong>admin / admin</strong><br>
            After login you will enter the staff dashboard (Products, Materials, Orders)
        </div>
        <button class="back-home-btn" onclick="window.location.href='../index.html'">Return to homepage</button>  
    </div>
</body>
</html>