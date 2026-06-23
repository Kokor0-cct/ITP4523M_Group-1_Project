<?php
require_once '../Function/Cart.php';

$host = 'localhost';
$dbuser = 'root';
$dbpass = '';
$dbname = 'projectdb';
$conn = mysqli_connect($host, $dbuser, $dbpass, $dbname);


$sql = "SELECT MAX(oid) AS max_oid FROM orders";
$rs = mysqli_query($conn, $sql);
$MaxOid = mysqli_fetch_assoc($rs);
$Newoid = $MaxOid['max_oid'] +1;
$ocdate=date('Y-m-d H:i:s');
$cid = $_SESSION['CID'];
$cartTotal = getCartTotal();




$sql = "SELECT *  FROM customers WHERE cid='$cid'";
$rs = mysqli_query($conn, $sql);
$Stock = mysqli_fetch_assoc($rs);
$cBudget = $Stock['cBudget'];
$twoDayLater = date('Y-m-d H:i:s',strtotime('+2 days'));


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action']) && $_POST['action'] === 'delete' && isset($_POST['fid'])) {
        delCartItem($_POST['fid']);
        header('Location: ShoppingCart.php');
        exit;
    }
    if (isset($_POST['action']) && $_POST['action'] === 'checkout') {
        $cartList = getCartList();
        if (empty($cartList)) {
            $error = 'Shopping cart is empty, cannot place order!';
        }elseif ($cBudget < $cartTotal){    
            $error = 'Insufficient balance！ ';
        }elseif($twoDayLater > $_POST['date']){
             $error = 'The shipping date is at least two days from today. ';
        } else {
            $address = mysqli_real_escape_string($conn, $_POST['address']);
            $date = mysqli_real_escape_string($conn, $_POST['date']);
            $cartTotal = getCartTotal();

            $orderSql = "INSERT INTO `orders`(`oid`, `odate`, `ototalamount`, `cid`, `odeliverydate`, `odeliveraddress`, `ostatus`) 
                         VALUES ( '$Newoid','$ocdate','$cartTotal','$cid', '$date' ,'$address',1 )";
            if (mysqli_query($conn, $orderSql)) {

                foreach ($cartList as $item) {
                    $fid = $item['fid'];
                    $qty = $item['qty'];

                    $itemSql = "INSERT INTO `orderfurnitures`(`oid`, `fid`, `oqty`) 
                                VALUES ($Newoid, $fid,$qty)";
                    mysqli_query($conn, $itemSql);

                    $sql = "SELECT *  FROM furnitures WHERE fid='$fid'";
                    $rs = mysqli_query($conn, $sql);
                    $Stock = mysqli_fetch_assoc($rs);
                    $oldStock = $Stock['fStock'];
                    $NewStock = $oldStock - $qty;

                    $update_stmt = $conn->prepare("UPDATE furnitures SET fStock = ? WHERE fid = ?");
                    $update_stmt->bind_param("ii", $NewStock, $fid);
                    $update_stmt->execute();


                    $NewBudget=$cBudget-$cartTotal;

                    $update_stmt = $conn->prepare("UPDATE customers SET cBudget = ? WHERE cid = ?");
                    $update_stmt->bind_param("ii", $NewBudget, $cid);
                    $update_stmt->execute();

                    clearCart(); 
                    $success = 'Order submitted successfully! (Demo only)';
                }   
            }
        }
    }
}
$cartList = getCartList();
$cartTotal = getCartTotal();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Shopping Cart</title>
    <link rel="stylesheet" href="../CSS/style.css">

</head>
<body>
    <div class="nav">
        <a  href="productList.php">Browse products</a>
        <a class="active" href="ShoppingCart.php">shopping cart</a>
        <a href="#">My Orders</a>
        <a href="myAccount.php">My account</a>
    </div>
    <div class="cart-container">
        <div class="cart-header">My Shopping Cart</div>
        
        <?php if (isset($success)): ?>
            <div class="msg success"><?= $success ?></div>
        <?php endif; ?>
        <?php if (isset($error)): ?>
            <div class="msg error"><?= $error ?></div>
        <?php endif; ?>

        <table class="cart-table">
            <thead>
                <tr>
                    <th>Product Name</th>
                    <th>Unit Price </th>
                    <th>Quantity</th>
                    <th>Subtotal </th>
                    <th>Operation</th>
                </tr>
            </thead>
            <tbody id="cartBody">
                <?php if (empty($cartList)): ?>
                    <tr>
                        <td colspan="6" class="empty-tip">Shopping cart is empty</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($cartList as $item): ?>
                        <?php
                        $subtotal = $item['fprice'] * $item['qty'];
                        ?>
                        <tr>
                            <td><?= htmlspecialchars($item['fname']) ?></td>
                            <td><?= number_format($item['fprice'], 2) ?></td>
                            <td><?= $item['qty'] ?></td>
                            <td><?= number_format($subtotal, 2) ?></td>
                            <td>
                                <form method="POST" style="display: inline;" onsubmit="return confirm('Are you sure to cancel this product?');">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="fid" value="<?= $item['fid'] ?>">
                                    <button type="submit" class="cancel-btn">Cancel</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>

        <?php if (!empty($cartList)): ?>
            <div class="cart-total">Total: $ <?= number_format($cartTotal, 2) ?> </div>

            <form method="POST" onsubmit="return confirm('Are you sure to place the order?');">
                <input type="hidden" name="action" value="checkout">
                

                <div class="form-group">
                    <label>Delivery Date *</label>
                    <input type="datetime-local" name="date" required >
                </div>
                <div class="form-group">
                    <label>Delivery Address *</label>
                    <input type="text" name="address" required value="<?php echo $_SESSION['CAddress']?>">
                </div>

                <button type="submit" class="submit-btn"  onsubmit="return confirm('Are you sure to place the order?');">Submit Order</button>
            </form>
        <?php endif; ?>
    </div>
</body>
</html>