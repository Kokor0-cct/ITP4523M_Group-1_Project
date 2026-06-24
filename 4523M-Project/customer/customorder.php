<?php
session_start();
if (!isset($_SESSION['customer']) || !isset($_SESSION['CID'])) {
    header('Location: customerLogin.php');
    exit;
}
$cid = $_SESSION['CID'];
$today = date('Y-m-d');
$host = 'localhost';
$dbuser = 'root';
$dbpass = '';
$dbname = 'projectdb';
$conn = mysqli_connect($host, $dbuser, $dbpass, $dbname);
mysqli_set_charset($conn, 'utf8');

if (!$conn) die("DB Connect Error: " . mysqli_connect_error());

// 修复点1：统一接收oid参数（和前端传参一致）
if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['oid'])) {
    $delOid = intval($_GET['oid']);

    // 修复点2：字段名统一用customeorders表的字段（costatus/codeliverydate）
    $checkSql = "SELECT coid, cid, codeliveraddress, costatus, cototalamount, codeliverydate FROM customeorders WHERE coid = ?";
    $checkStmt = mysqli_prepare($conn, $checkSql);
    mysqli_stmt_bind_param($checkStmt, 'i', $delOid);
    mysqli_stmt_execute($checkStmt);
    $checkRes = mysqli_stmt_get_result($checkStmt);
    $orderInfo = mysqli_fetch_assoc($checkRes);

    if (!$orderInfo || $orderInfo['cid'] != $cid) {
        $_SESSION['msg'] = "Failed: No permission to delete this order";
        header('Location: customorder.php'); // 修复：跳转到正确的自定义订单页
        exit;
    }

    // 修复点3：字段名改为codeliverydate
    $deliveryDate = $orderInfo['codeliverydate'];
    $limitDay = date('Y-m-d', strtotime($today . " +2 days"));
    if ($deliveryDate <= $limitDay) {
        $_SESSION['msg'] = "Cannot cancel! Only allowed to cancel 2 days before delivery date.";
        header('Location: customorder.php');
        exit;
    }

    $banStatus = [4,5,6];
    // 修复点4：字段名改为costatus
    if (in_array($orderInfo['costatus'], $banStatus)) {
        $_SESSION['msg'] = "Cannot cancel shipped/completed orders.";
        header('Location: customorder.php');
        exit;
    }

    

    $orderTotal = $orderInfo['cototalamount'];
    $refundSql = "UPDATE customers SET cBudget = cBudget + ? WHERE cid = ?";
    $refundStmt = mysqli_prepare($conn, $refundSql);
    mysqli_stmt_bind_param($refundStmt, 'di', $orderTotal, $cid);
    mysqli_stmt_execute($refundStmt);

    $delItemSql = "DELETE FROM orderfurnitures WHERE oid = ?";
    $delItemStmt = mysqli_prepare($conn, $delItemSql);
    mysqli_stmt_bind_param($delItemStmt, 'i', $delOid);
    mysqli_stmt_execute($delItemStmt);

    // 修复点5：删除customeorders表的订单（而非orders表）
    $delOrderSql = "DELETE FROM customeorders WHERE coid = ? AND cid = ?";
    $delOrderStmt = mysqli_prepare($conn, $delOrderSql);
    mysqli_stmt_bind_param($delOrderStmt, 'ii', $delOid, $cid);
    mysqli_stmt_execute($delOrderStmt);

    $_SESSION['msg'] = "Order cancelled successfully! Stock and fund have been restored.";
    header('Location: customorder.php'); // 修复：跳转到自定义订单页
    exit;
}

// 修复点6：排序字段数组去重+补充codate字段
$allowedSortFields = ['coid', 'codate', 'cototalamount', 'codeliverydate', 'costatus'];

$sortBy = isset($_GET['sortBy']) && in_array($_GET['sortBy'], $allowedSortFields) ? $_GET['sortBy'] : 'coid';
$sortDir = isset($_GET['sortDir']) && $_GET['sortDir'] == 'asc' ? 'asc' : 'desc';
$toggleDir = $sortDir == 'asc' ? 'desc' : 'asc';

$sqlList = "SELECT * FROM customeorders WHERE cid = ? ORDER BY $sortBy $sortDir";
$stmtList = mysqli_prepare($conn, $sqlList);
mysqli_stmt_bind_param($stmtList, 'i', $cid);
mysqli_stmt_execute($stmtList);
$rs = mysqli_stmt_get_result($stmtList);
$orderList = [];
while ($row = mysqli_fetch_assoc($rs)) $orderList[] = $row;

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
        
        .sortable-th {
            cursor: pointer;
            user-select: none;
            padding: 5px;
        }
        .sortable-th:hover {
            background-color: #f5f5f5;
        }
        .sort-arrow {
            margin-left: 5px;
            font-size: 12px;
            color: #666;
        }
    </style>
</head>
<body>
    <div class="nav">
        <a href="productList.php">💎Browse products</a>
        <a href="customProduct.php">🔧Custom Product</a>
        <a href="ShoppingCart.php">🛒Shopping cart</a>
        <a class="active" href="order.php">🧾My Orders</a>
        <a href="myAccount.php">👤My account</a>
    </div>
    <div>
        <a  class="sort-btn" href="order.php">🧾My Orders</a>
        <a  class="sort-btn" href="customorder.php">🔧Custom Orders</a>
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
                    <th class="sortable-th" onclick="window.location.href='customorder.php?sortBy=oid&sortDir=<?= $sortBy == 'coid' ? $toggleDir : 'asc' ?>'">
                        Order ID
                        <?php if($sortBy == 'coid'): ?>
                            <span class="sort-arrow"><?= $sortDir == 'asc' ? '↑' : '↓' ?></span>
                        <?php endif; ?>
                    </th>
                    <th class="sortable-th" onclick="window.location.href='customorder.php?sortBy=codate&sortDir=<?= $sortBy == 'codate' ? $toggleDir : 'asc' ?>'">
                        Order Date
                        <?php if($sortBy == 'codate'): ?>
                            <span class="sort-arrow"><?= $sortDir == 'asc' ? '↑' : '↓' ?></span>
                        <?php endif; ?>
                    </th>
                    <th class="sortable-th" onclick="window.location.href='customorder.php?sortBy=cototalamount&sortDir=<?= $sortBy == 'cototalamount' ? $toggleDir : 'asc' ?>'">
                        Total Amount
                        <?php if($sortBy == 'cototalamount'): ?>
                            <span class="sort-arrow"><?= $sortDir == 'asc' ? '↑' : '↓' ?></span>
                        <?php endif; ?>
                    </th>
                    <th class="sortable-th" onclick="window.location.href='customorder.php?sortBy=codeliverydate&sortDir=<?= $sortBy == 'codeliverydate' ? $toggleDir : 'asc' ?>'">
                        Delivery Date
                        <?php if($sortBy == 'codeliverydate'): ?>
                            <span class="sort-arrow"><?= $sortDir == 'asc' ? '↑' : '↓' ?></span>
                        <?php endif; ?>
                    </th>
                    <th>Delivery Address</th>
                    <th class="sortable-th" onclick="window.location.href='customorder.php?sortBy=costatus&sortDir=<?= $sortBy == 'costatus' ? $toggleDir : 'asc' ?>'">
                        Order Status
                        <?php if($sortBy == 'costatus'): ?>
                            <span class="sort-arrow"><?= $sortDir == 'asc' ? '↑' : '↓' ?></span>
                        <?php endif; ?>
                    </th>
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
                        $canCancel = $item['codeliverydate'] > $limitDay && !in_array($item['costatus'], [4,5,6]);
                    ?>
                    <tr>
                        <td><?= $item['coid'] ?></td>
                        <td><?= $item['codate'] ?></td>
                        <td>$<?= number_format($item['cototalamount'],2) ?></td>
                        <td><?= $item['codeliverydate'] ?></td>
                        <td><?= $item['codeliveraddress'] ?></td>
                        <td>
                            <?php
                            $status = $item['costatus'];
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
                            <?php if($canCancel): ?>
                                <a class="btn-del" onclick="if(confirm('Are you sure to cancel this order?')) window.location.href='customorder.php?action=delete&oid=<?= $item['coid'] ?>&sortBy=<?= $sortBy ?>&sortDir=<?= $sortDir ?>'">Cancel Order</a>
                            <?php else: ?>
                                <a class="btn-del" disabled style="background:#aaa;cursor:not-allowed;">Cannot Cancel</a>
                                
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table><br><br>
        <p>Today is: <?= $today ?></p>
    </div>
    <?php if($showModal && !empty($detailOrder)): ?>
    
    <?php endif; ?>
</body>
</html>