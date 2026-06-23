<?php
session_start();
if (!isset($_SESSION['customer']) || !isset($_SESSION['CID'])) {
    header('Location: customerLogin.php');
    exit;
}
$cid = $_SESSION['CID'];
$today = date('Y-m-d');
// 数据库连接
$host = 'localhost';
$dbuser = 'root';
$dbpass = '';
$dbname = 'projectdb';
$conn = mysqli_connect($host, $dbuser, $dbpass, $dbname);
mysqli_set_charset($conn, 'utf8');
if (!$conn) die("DB Connect Error: " . mysqli_connect_error());

if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['oid'])) {
    $delOid = intval($_GET['oid']);

    $checkSql = "SELECT oid, cid, odeliverydate, ostatus, ototalamount FROM orders WHERE oid = ?";
    $checkStmt = mysqli_prepare($conn, $checkSql);
    mysqli_stmt_bind_param($checkStmt, 'i', $delOid);
    mysqli_stmt_execute($checkStmt);
    $checkRes = mysqli_stmt_get_result($checkStmt);
    $orderInfo = mysqli_fetch_assoc($checkRes);

    if (!$orderInfo || $orderInfo['cid'] != $cid) {
        $_SESSION['msg'] = "Failed: No permission to delete this order";
        header('Location: order.php');
        exit;
    }

    $deliveryDate = $orderInfo['odeliverydate'];
    $limitDay = date('Y-m-d', strtotime($today . " +2 days"));
    if ($deliveryDate <= $limitDay) {
        $_SESSION['msg'] = "Cannot cancel! Only allowed to cancel 2 days before delivery date.";
        header('Location: order.php');
        exit;
    }

    $banStatus = [4,5,6];
    if (in_array($orderInfo['ostatus'], $banStatus)) {
        $_SESSION['msg'] = "Cannot cancel shipped/completed orders.";
        header('Location: order.php');
        exit;
    }

    // ========== 新增逻辑1：恢复商品库存 ==========
    // 1. 获取该订单下的所有商品及数量
    $getItemsSql = "SELECT fid, oqty FROM orderfurnitures WHERE oid = ?";
    $getItemsStmt = mysqli_prepare($conn, $getItemsSql);
    mysqli_stmt_bind_param($getItemsStmt, 'i', $delOid);
    mysqli_stmt_execute($getItemsStmt);
    $itemsRes = mysqli_stmt_get_result($getItemsStmt);
    
    // 2. 循环恢复每个商品的库存
    while ($item = mysqli_fetch_assoc($itemsRes)) {
        $fid = $item['fid'];
        $oqty = $item['oqty'];
        $CancelStock=
        $restoreStockSql = "UPDATE furnitures SET fStock = fStock + ? WHERE fid = ?";
        $restoreStockStmt = mysqli_prepare($conn, $restoreStockSql);
        mysqli_stmt_bind_param($restoreStockStmt, 'ii', $oqty, $fid);
        mysqli_stmt_execute($restoreStockStmt);
    }

    // ========== 新增逻辑2：退回订单金额到用户账户 ==========
    // 假设customers表有cbudget字段（用户余额），需根据实际字段名调整
    $orderTotal = $orderInfo['ototalamount'];
    $refundSql = "UPDATE customers SET cBudget = cBudget + ? WHERE cid = ?";
    $refundStmt = mysqli_prepare($conn, $refundSql);
    mysqli_stmt_bind_param($refundStmt, 'di', $orderTotal, $cid); // d=decimal, i=int
    mysqli_stmt_execute($refundStmt);

    // ========== 原有删除逻辑 ==========
    $delItemSql = "DELETE FROM orderfurnitures WHERE oid = ?";
    $delItemStmt = mysqli_prepare($conn, $delItemSql);
    mysqli_stmt_bind_param($delItemStmt, 'i', $delOid);
    mysqli_stmt_execute($delItemStmt);

    $delOrderSql = "DELETE FROM orders WHERE oid = ? AND cid = ?";
    $delOrderStmt = mysqli_prepare($conn, $delOrderSql);
    mysqli_stmt_bind_param($delOrderStmt, 'ii', $delOid, $cid);
    mysqli_stmt_execute($delOrderStmt);

    $_SESSION['msg'] = "Order cancelled successfully! Stock and fund have been restored.";
    header('Location: order.php');
    exit;
}

$sqlList = "SELECT * FROM orders WHERE cid = ?";
$stmtList = mysqli_prepare($conn, $sqlList);
mysqli_stmt_bind_param($stmtList, 'i', $cid);
mysqli_stmt_execute($stmtList);
$rs = mysqli_stmt_get_result($stmtList);
$orderList = [];
while ($row = mysqli_fetch_assoc($rs)) $orderList[] = $row;

$showModal = false;
$goodsList = [];
if (isset($_GET['oid']) && !empty($_GET['oid'])) {
    $targetOid = intval($_GET['oid']);
    $sqlOrder = "SELECT * FROM orders WHERE oid = ? ";
    $stmtOrder = mysqli_prepare($conn, $sqlOrder);
    mysqli_stmt_bind_param($stmtOrder, 'i', $targetOid);
    mysqli_stmt_execute($stmtOrder);
    $resOrder = mysqli_stmt_get_result($stmtOrder);
    $detailOrder = mysqli_fetch_assoc($resOrder);
    if ($detailOrder) {
        $showModal = true;
        $sqlJoin = "SELECT oi.oqty, oi.fid, f.fdesc, f.fprice FROM orderfurnitures oi LEFT JOIN furnitures f ON oi.fid = f.fid WHERE oi.oid = ?";
        $stmtItem = mysqli_prepare($conn, $sqlJoin);
        mysqli_stmt_bind_param($stmtItem, 'i', $targetOid);
        mysqli_stmt_execute($stmtItem);
        $rsItem = mysqli_stmt_get_result($stmtItem);
        $goodsList = [];
        while ($row = mysqli_fetch_assoc($rsItem)) {
            $goodsList[] = $row;
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
    <title>My Orders</title>
    <link rel="stylesheet" href="../CSS/style.css">
    <style>

        /* 弹窗遮罩 */
        .mask {
            <?php if($showModal): ?>display:flex;<?php else: ?>display:none;<?php endif; ?>
            position: fixed;
            top:0;left:0;width:100%;height:100%;
            background:rgba(0,0,0,0.5);
            justify-content:center;align-items:center;
            z-index:999;
        }
        .modal-box{
            background:#fff;
            width:1200px;
            padding:25px;
            border-radius:8px;
        }
        .modal-head{
            display:flex;
            justify-content:space-between;
            align-items:center;
            border-bottom:1px #eee solid;
            padding-bottom:10px;
            margin-bottom:15px;
        }
        .close-btn{
            text-decoration:none;
            font-size:24px;
            color:#777;
        }
        .info-line{margin:8px 0;}
        .info-label{font-weight:bold;width:200px;display:inline-block;}
       
        .btn-info {
            background:#28a745;color:white;text-decoration:none;padding:4px 9px;border-radius:3px;display:inline-block;margin-right:6px;
        }
        .btn-del {
            background:#dc3545;color:white;border:none;padding:4px 9px;border-radius:3px;cursor:pointer;
        }
        .msg-success{color:green;margin:10px 0;}
        .msg-error{color:red;margin:10px 0;}
    </style>
</head>
<body>
    <div class="nav">
        <a href="productList.php">Browse products</a>
        <a href="ShoppingCart.php">shopping cart</a>
        <a class="active" href="order.php">My Orders</a>
        <a href="myAccount.php">My account</a>
    </div>
    <div class="table-card">
        <div class="header">
            <h1>Order List</h1>
            <?php if(isset($_SESSION['msg'])): ?>
                <div class="<?= str_contains($_SESSION['msg'],'success') ? 'msg-success' : 'msg-error' ?>">
                    <?= $_SESSION['msg'] ?>
                    <?php unset($_SESSION['msg']); ?>
                </div>
            <?php endif; ?>
        </div>
        <table border="1" width="100%">
            <thead>
                <tr>
                    <th>Order ID</th>
                    <th>Order Date</th>
                    <th>Total Amount</th>
                    <th>Delivery Date</th>
                    <th>Delivery Address</th>
                    <th>Order Status</th>
                    <th>Operation</th>
                </tr>
            </thead>
            <tbody>
                <?php if(empty($orderList)): ?>
                    <tr><td colspan="10" class="empty-row">You have no orders yet</td></tr>
                <?php else: ?>
                    <?php foreach ($orderList as $item): ?>
                    <?php
                        $limitDay = date('Y-m-d', strtotime($today . " +2 days"));
                        $canCancel = $item['odeliverydate'] > $limitDay && !in_array($item['ostatus'], [4,5,6]);
                    ?>
                    <tr>
                        <td><?= $item['oid'] ?></td>
                        <td><?= $item['odate'] ?></td>
                        <td>$<?= number_format($item['ototalamount'],2) ?></td>
                        <td><?= $item['odeliverydate'] ?></td>
                        <td><?= $item['odeliveraddress'] ?></td>
                        <td>
                            <?php
                            $status = $item['ostatus'];
                            $tagClass = $tagVal = '';
                            if($status == 1){$tagClass='tag-pending';$tagVal='pending';}
                            elseif($status == 2){$tagClass='tag-processing';$tagVal='processing';}
                            elseif($status == 3){$tagClass='tag-waitshipping';$tagVal='waitshipping';}
                            elseif($status == 4){$tagClass='tag-shipped';$tagVal='shipped';}
                            elseif($status ==5 || $status ==6){$tagClass='tag-arrive';$tagVal='arrive';}
                            ?>
                            <span class="<?= $tagClass ?>"><?= $tagVal ?></span>
                        </td>
                        <td>
                            <a href="order.php?oid=<?= $item['oid'] ?>" class="btn-info">Order Info</a>
                            <?php if($canCancel): ?>
                                <a class="btn-del" onclick="if(confirm('Are you sure to cancel this order?')) window.location.href='order.php?action=delete&oid=<?= $item['oid'] ?>'">Cancel Order</a>
                            <?php else: ?>
                                <a class="btn-del" disabled style="background:#aaa;cursor:not-allowed;">Cannot Cancel</a>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
        <p>Today is: <?= $today ?></p>
    </div>
    <?php if($showModal && !empty($detailOrder)): ?>
    <div class="mask">
        <div class="modal-box">
            <div class="modal-head">
                <h3>Complete Order Information</h3>
                <a href="order.php" class="close-btn">×</a>
            </div>
            <div class="info-line"><span class="info-label">Order ID:</span><?= $detailOrder['oid'] ?></div>
            <div class="info-line"><span class="info-label">Order Date:</span><?= $detailOrder['odate'] ?></div>
            <div class="info-line"><span class="info-label">Delivery Date:</span><?= $detailOrder['odeliverydate'] ?></div>
            <div class="info-line"><span class="info-label">Delivery Address:</span><?= $detailOrder['odeliveraddress'] ?></div>
            <div class="info-line"><span class="info-label">Order Status Code:</span><?= $detailOrder['ostatus'] ?></div>
            <h4 style="margin:15px 0 8px;">Products In This Order</h4>
            <table>
                <thead>
                    <tr>
                        <th>Product Name</th>
                        <th>Unit Price</th>
                        <th>Quantity</th>
                        <th>Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(empty($goodsList)): ?>
                        <tr><td colspan="4">No product data</td></tr>
                    <?php else: ?>
                        <?php foreach ($goodsList as $goods): ?>
                        <tr>
                            <td><?= htmlspecialchars($goods['fdesc']) ?></td>
                            <td>$<?= number_format($goods['fprice'],2) ?></td>
                            <td><?= $goods['oqty'] ?></td>
                            <td>$<?= number_format($goods['fprice'] * $goods['oqty'],2) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
            <div style="font-weight:bold;font-size:16px;text-align:right;">
                Total Order Amount: $<?= number_format($detailOrder['ototalamount'],2) ?>
            </div>
        </div>
    </div>
    <?php endif; ?>
</body>
</html>