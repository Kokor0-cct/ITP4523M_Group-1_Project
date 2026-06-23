<?php
$host = 'localhost';
$dbuser = 'root';
$dbpass = '';
$dbname = 'projectdb'; 

$conn = mysqli_connect($host, $dbuser, $dbpass, $dbname);

if (!$conn) {
    die("Database connection failed:" . mysqli_connect_error());
}

$msg = "";
if (isset($_POST['login'])) {
    $cname = trim($_POST['cname']);
    $pwd = trim($_POST['cpassword']);

    $sql = "SELECT * FROM customers WHERE cname = '$cname'";
    $rs = mysqli_query($conn, $sql);

    if (mysqli_num_rows($rs) > 0) {
        $row = mysqli_fetch_assoc($rs);
        if ($pwd == $row['cpassword']) {
            session_start();
            $_SESSION['customer'] = $row['cname'];
            $_SESSION['CAddress'] = $row['caddr'];
            $_SESSION['CPhone'] = $row['ctel'];
            $_SESSION['CID'] = $row['cid'];
            $_SESSION['cBudget'] = $row['cBudget'];

            header("Location: productList.php");
            exit;
        } else {
            $msg = "The password is wrong, please re-enter!";
        }
    } else {
        $msg = "This username is not registered!";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Customer Login</title>
    <link rel="stylesheet" href="../CSS/style.css">
    <style>
        .background_img{
            background-image: url(../img/background.png);
            background-size: cover;    
            background-position: center; 
            background-repeat: no-repeat; 
            background-attachment: fixed; 
        }
    </style>
</head>
<body class="background_img">
    <div class="login">
        <h2 style="text-align:center">Customer Login Portal</h2>
        <!-- 表单提交到自身php文件 -->
        <form id="loginForm" method="POST" action="">
            <div class="item">
                <label>Account Name</label>
                <input type="text" name="cname" required>
            </div>
            <div class="item">
                <label>Password</label>
                <input type="password" name="cpassword" required>
            </div>
            <p style="color:red;text-align:center;"><?php echo $msg; ?></p>
            <button class="login-button" type="submit" name="login">Login</button>
        </form>
        <button class="back-home-btn" onclick="window.location.href='../index.html'">Return to homepage</button>
    </div>
</body>
</html>

<?php
mysqli_close($conn);
?>







