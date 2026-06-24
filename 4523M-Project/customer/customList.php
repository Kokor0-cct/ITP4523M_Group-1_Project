<?php
session_start();
// 登录校验
if (!isset($_SESSION['customer']) || !isset($_SESSION['CID'])) {
    header('Location: customerLogin.php');
    exit;
}
$cid = $_SESSION['CID'];
$today =date('Y-m-d H:i:s');

$host = 'localhost';
$dbuser = 'root';
$dbpass = '';
$dbname = 'projectdb';
$conn = mysqli_connect($host, $dbuser, $dbpass, $dbname);
mysqli_set_charset($conn, 'utf8');
if (!$conn) die("DB Connect Error: " . mysqli_connect_error());

$showDetail = false;
$detailData = [];
if (isset($_GET['cr_id']) && !empty($_GET['cr_id'])) {
    $targetCrId = intval($_GET['cr_id']);

    $sqlDetail = "SELECT * FROM customefurnitures  WHERE cfrID = ? AND cUserID = ?";
    $stmtDetail = mysqli_prepare($conn, $sqlDetail);
    mysqli_stmt_bind_param($stmtDetail, 'ii', $targetCrId, $cid);
    mysqli_stmt_execute($stmtDetail);
    $resDetail = mysqli_stmt_get_result($stmtDetail);
    $detailData = mysqli_fetch_assoc($resDetail);
    if (!empty($detailData)) {
        $showDetail = true;
    }
}


$sqlList = "SELECT * FROM customfurniturerequest WHERE cUserID = ?";
$stmtList = mysqli_prepare($conn, $sqlList);
mysqli_stmt_bind_param($stmtList, 'i',$cid);
mysqli_stmt_execute($stmtList);
$resList = mysqli_stmt_get_result($stmtList);
$customList = [];
while ($row = mysqli_fetch_assoc($resList)) {
    $customList[] = $row;
}


$sql = "SELECT *  FROM customers WHERE cid='$cid'";
$rs = mysqli_query($conn, $sql);
$bg = mysqli_fetch_assoc($rs);
$cBudget = $bg['cBudget'];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $cfid=trim($_POST['cfid'] ?? '');
    $cfprice=trim($_POST['cfprice'] ?? '');
    $qty=trim($_POST['Numofqty'] ?? '');
    $totalamount=$cfprice*$qty;
    $today=date('Y-m-d H:i:s');
    $cid=$_SESSION['CID'];
    $codeliverydate=trim($_POST['date'] ?? '');
    $codeliveraddress=trim($_POST['address'] ?? '');
    $costatus=1;
    $twoDayLater = date('Y-m-d H:i:s',strtotime('+2 days'));


    if ($cBudget < $totalamount){    
            $error = 'Insufficient balance！ ';
        }elseif($twoDayLater > $codeliverydate){
             $error = 'The shipping date is at least two days from today. ';
        }elseif($_POST['address']==""){
            $error = 'The shipping address must not be empty. ';
        }else{

        $orderSql = "INSERT INTO `customeorders`(`cfid`, `cfprice`, `qty`, `cototalamount`, `codate`, `cid`, `codeliverydate`, `codeliveraddress`, `costatus`) 
                        VALUES ('$cfid','$cfprice','$qty','$totalamount','$today','$cid','$codeliverydate','$codeliveraddress','$costatus')";
        if (mysqli_query($conn, $orderSql)) {

            $NewBudget=$cBudget-$totalamount;

            $update_stmt = $conn->prepare("UPDATE customers SET cBudget = ? WHERE cid = ?");
            $update_stmt->bind_param("ii", $NewBudget, $cid);
            $update_stmt->execute();

            $success = 'Order submitted successfully! (Demo only)';
        
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
    <title>My Custom Requests</title>
    <link rel="stylesheet" href="../CSS/style.css">
    <style>


        .page-title {
            font-size:24px;
            font-weight:bold;
            margin-bottom:20px;
        }
        .table {
            width: 100%;
        }
       
        .empty-tip {
            text-align:center;
            padding:40px;
            color:#666;
        }
        .btn-view {
            background:#28a745;
            color:white;
            text-decoration:none;
            padding:5px 12px;
            border-radius:3px;
        }
        .mask {
            <?php if($showDetail): ?>display:flex;<?php else: ?>display:none;<?php endif; ?>
            position:fixed;
            top:0;left:0;
            width:100%;height:100%;
            background:rgba(0,0,0,0.5);
            justify-content:center;
            align-items:center;
            z-index:999;
        }
        .modal-box {
            width:1100px;
            background:#fff;
            padding:30px;
            border-radius:8px;
        }
        .modal-head {
            display:flex;
            justify-content:space-between;
            align-items:center;
            border-bottom:1px #eee solid;
            padding-bottom:12px;
            margin-bottom:20px;
        }
        .close-modal {
            text-decoration:none;
            font-size:26px;
            color:#666;
        }
        .img-preview {
            max-width:300px;
            border:1px #ccc solid;
            margin-top:8px;
        }
    </style>
</head>
<body>
    <!-- 统一导航栏 -->
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
        <div class="page-title">My Submitted Custom Applications</div>
            <table style="width:100%;">
            <thead>
                <tr>
                    <th>Custom ID</th>
                    <th>Budget</th>
                    <th>Custom Description</th>
                    <th>State</th>
                    <th>Submit Time</th>
                    <th >Operation</th>
                </tr>
            </thead>
            <tbody>
                <?php if(empty($customList)): ?>
                    <tr>
                        <td colspan="4" class="empty-tip">You have not submitted any custom requests yet. <a href="CustomProduct.php">Click to submit new demand</a></td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($customList as $item): ?>
                    <tr>
                        <td><?= $item['cfrID'] ?></td>
                        <td>$<?= number_format($item['cfrBudget'],2) ?></td>
                        <td><?= $item['cfrDESC'] ?></td>
                        <td> 
                            <?php
                            $status = $item['fcrState'];
                            $tagVal = '';
                            if($status == 1)
                                $tagVal='Awaiting processing';
                            elseif($status == 2)
                                $tagVal='Awaiting confirmation';
                            elseif($status == 3)
                                $tagVal='Completed';
                            ?>
                            
                            <?= $tagVal ?></td>
                        <td><?= $item['cfrCreateDate'] ?></td>
                        <td >
                            <?php if ($status == 1): ?>
                                <button disabled  class="card-btn">Pending processing</button> 
                            <?php else: ?>
                                <Button  onclick="viewInfo(<?php echo $item['cfrID']; ?>,<?php echo $status?>);" class="btn-view" id="ViewInfo">View Details</Button>
                            <?php endif; ?> 
                            
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
        
    </div>

    <!-- 弹窗：点击View后弹出详情表格 -->
    <?php if($showDetail && !empty($detailData)): ?>
    <div class="mask">
        <div class="modal-box">
            <?php if (isset($success)): ?>
            <div class="msg success"><?= $success ?></div>
        <?php endif; ?>
        <?php if (isset($error)): ?>
            <div class="msg error"><?= $error ?></div>
        <?php endif; ?>
            <div class="modal-head">
                <h2>Custom Request Full Information</h2>
                <a href="customList.php" class="close-modal">×</a>
            </div>


            <table style="width:100%;">
                <tbody>
                    <tr>
                        <th id="aas">Custom Request ID</th>
                        <th>Custom product Name</th>
                        <th>Expected Budget</th>
                        <th>Custom Requirement Description</th>
                        <th>Reference Image</th>
                        
                    </tr>
                    <tr>
                            <td><?= $detailData['cfrID'] ?></td>
                        <td><?= $detailData['cfname'] ?></td>
                        <td>$<?= number_format($detailData['cfprice'],2) ?></td>
                        <td width="200" ><?= htmlspecialchars($detailData['cfdesc']) ?></td>
                        <td>
                            <?php if (!empty($detailData['cfImgPath']) && file_exists($detailData['cfImgPath'])): ?>
                                <img src="<?= htmlspecialchars($detailData['cfImgPath']) ?>" class="img-preview">
                            <?php else: ?>
                                No reference picture uploaded
                            <?php endif; ?>
                        </td>
                    </tr>
                   
                </tbody>
            </table>
            <br>
            <div style="display:flex; gap:10px; align-items:center;">
                <button style="width: 15%; display: none;" id="approved" onclick="changeStatus(<?php echo $detailData['cfrID'] ?>,3)">Design approved</button>
                <button style="width: 15%; background-color: red; display: none;" id="rejected" onclick="changeStatus(<?php echo $detailData['cfrID'] ?>,1)">Design rejected</button>
                    <form  method="POST" onsubmit="return confirm('Are you sure to place the order?');" id="subform" style="display: none;">
                        <div class="form-group" >
                            <label>Number of Quantity *</label>
                            <input type="number" name="Numofqty" min="1" max="999" value="1" style="width:100%;" >
                        </div>

                        <div class="form-group" >
                            <label>Delivery Date *</label>
                            <input type="datetime-local" name="date" required >
                        </div>

                        <div class="form-group" >
                            <label>Delivery Address *</label>
                            <input type="text" name="address" required value="<?php echo $_SESSION['CAddress']?>">
                        </div>
                        <input type="text" style="display: none;" name="cfid" value="<?php echo $detailData['cfid'] ?>">
                        <input type="text" style="display: none;" name="cfprice" value="<?php echo $detailData['cfprice'] ?>">

                        <button type="submit" style="width:100%;" id="Buy">Buy</button>
                    </form>
            </div>                


        </div>
    </div>
    <?php endif; ?>
</body>
<script>


    const approved = document.getElementById('approved');
    const rejected = document.getElementById('rejected');
    const subform = document.getElementById('subform');
    const ViewInfo = document.getElementById('ViewInfo');                            

    
    function viewInfo(cfrid,st) {
        window.location.href = "customList.php?cr_id=" + cfrid+"&st="+st;
    }
    function getParam(name) {
        const params = new URLSearchParams(location.search);
        return params.get(name);
    }
    window.onload = function(){
        const params = new URLSearchParams(location.search);
        let sta = Number(getParam('st'));

        approved.style.display = 'none';    
        subform.style.display = 'none';    
        rejected.style.display = 'none';

        if(sta ===2){
            
            approved.style.display = 'block';
            rejected.style.display = 'block';
        }else{
        subform.style.display = 'block';    

        }

    }

    function changeStatus(cfrId, statusVal){
    location.href = "../Function/updateStatus.php?id=" + cfrId + "&new_status=" + statusVal;
    }   

    
</script>
</html>
