<?php
// 引入你的数据库连接文件
$host = 'localhost';
$dbuser = 'root';
$dbpass = '';
$dbname = 'projectdb';
$conn = mysqli_connect($host, $dbuser, $dbpass, $dbname);

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$new_status = isset($_GET['new_status']) ? intval($_GET['new_status']) : 0;
$qty = isset($_GET['qty']) ? intval($_GET['qty']) : 0;

if ($id > 0 && $new_status > 0) {
    $sql = "UPDATE customfurniturerequest SET fcrState = ? WHERE cfrID = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "ii", $new_status, $id);
    mysqli_stmt_execute($stmt);
}

header("Location: ../customer/customList.php");
exit;
?>