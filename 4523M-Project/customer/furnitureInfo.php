<?php
require_once '../Function/Cart.php';
if (!isset($_SESSION['customer'])) {
    header("Location: customerLogin.php");
    exit;
}

if (!isset($_GET['fid']) || empty($_GET['fid'])) {
    header("Location: productList.php");
    exit;
}
$fid = $_GET['fid'];

$host = 'localhost';
$dbuser = 'root';
$dbpass = '';
$dbname = 'projectdb';


$conn = mysqli_connect($host, $dbuser, $dbpass, $dbname);
$sql = "SELECT * FROM furnitures WHERE fid = $fid";
$rs = mysqli_query($conn,$sql );

$productInfo = mysqli_fetch_assoc($rs);
if (!$productInfo) {
    header("Location: productList.php");
    exit;
}
mysqli_close($conn);

$fname = $productInfo['fname'];
$fprice = $productInfo['fprice'];
$fdesc = $productInfo['fdesc'];
$fStock = $productInfo['fStock'];
$fImgPath = $productInfo['fImgPath'];
$fSize = $productInfo['fSize'];

$msg="";
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $addQty = $_POST['quantity'];
    addToCart( $fid, $fname,$fprice,$addQty);
    $msg = "Add Suggessful!";

}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $fname;?></title>
    <link rel="stylesheet" href="../CSS/style.css">

</head>
<body>

    <button class="back-btn" onclick="goBack()">← Back to Product List</button>

    <!-- Top Navigation Bar -->
    <div class="nav">
        <a href="productList.php">Browse products</a>
        <a href="ShoppingCart.php"">shopping cart</a>
        <a href="#">My Orders</a>
        <a href="myAccount.php"">My account</a>
    </div>

    <div class="product-detail-container">

        <div>
            <img class="product-img-wrapper" src="<?php echo $fImgPath;?>" alt="<?php echo $fname; ?>">
        </div>

        <div class="product-info">
            <div class="product-name"><?php echo $fname; ?></div>
            
            <div class="product-desc"><?php echo $fdesc; ?></div>

            <div class="product-price"><?php echo $fprice; ?></div>
 
            <div class="product-stock" id="stockInfo">Stock Quantity: <?php echo $fStock; ?> pieces</div>

            <div class="quantity-control">
                <label for="size">Size Specification</label>
                    <input type="text" readonly value="<?php echo $fSize; ?>" selected>

            </div>

            <form  method="POST" action="">
                <div class="quantity-control">
                    <label for="quantity">Purchase Quantity</label>
                    <input type="number" name="quantity" class="quantity-input" min="1" max="999" value="1">
                </div>
                <button type="submit" class="add-cart-btn" >Add to Cart</button>
            </form>
            <div name="message" style="color: green;"> <?php echo $msg ?></div> 

            
        </div>
    </div>

    <script>

        function goBack() {
            window.location.href = 'productList.php';
        }

    </script>
</body>
</html>