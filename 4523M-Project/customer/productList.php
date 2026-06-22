<?php
session_start();
if (!isset($_SESSION['customer'])) {
    header("Location: customerLogin.php");
    exit;
}
$userName = $_SESSION['customer'];



$host = 'localhost';
$dbuser = 'root';
$dbpass = '';
$dbname = 'projectdb';
$conn = mysqli_connect($host, $dbuser, $dbpass, $dbname);
$sortType = isset($_GET['sort']) ? $_GET['sort'] : 'fname';

$sql = "SELECT * FROM furnitures ORDER BY $sortType ASC";
$rs = mysqli_query($conn, $sql);
$productArray = [];
while ($row = mysqli_fetch_assoc($rs)) {
    $productArray[] = $row;
}
mysqli_close($conn);
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Furniture Product Browse</title>
    <link rel="stylesheet" href="../CSS/style.css">

</head>
<body>
    <div >
        welcome!  <?php echo $userName; ?> 
        <a href="..\Function\logout.php" style="margin-left:20px;">Sign Out</a>
    </div>

    <div class="nav">
        <a class="active" href="productList.php">Browse products</a>
        <a href="#">shopping cart</a>
        <a href="#">My Orders</a>
        <a href="myAccount.php">My account</a>
    </div>

    <div class="header">
        <h1>Furniture Product Browse</h1>
    </div>

    <div class="sort-controls">
        <a class="sort-btn" href="productList.php?sort=fname"><button  class="sort-btn">Sort by Name</button></a>
        <a class="sort-btn" href="productList.php?sort=fStock"><button class="sort-btn">Sort by Stock</button></a>
        <a class="sort-btn" href="productList.php?sort=fprice"><button class="sort-btn" >Sort by Price</button></a>
    </div>

    <div class="goods-box" id="goodsContainer">
        <?php foreach($productArray as $item): ?>

        <div class="card">
            <img class="card-img"      
            src="<?php echo $item['fImgPath']; ?>"      
            alt="<?php echo $item['fname']; ?>"     
            onerror="this.src='../img/default.png'">
            <div class="card-info">
                <h3 class="card-name"><?php echo $item['fname']; ?></h3>
                <p class="card-price">Price : $<?php echo $item['fprice']; ?></p>
                <p class="stock">Stock : <?php echo $item['fStock']; ?></p>

                <?php if ($item['fStock'] < 1): ?>
                    <button disabled  class="card-btn">Out of Stock</button> 
                <?php else: ?>
                    <button class="card-btn"  onclick="ProductInfo(<?php echo $item['fid']; ?>)">Product Info</button>
                <?php endif; ?> 


            </div>
            
        </div>
    <?php endforeach; ?>
    </div>




    <script>
function ProductInfo(fid) {
    window.location.href = "furnitureInfo.php?fid=" + fid;
}
    </script>
</body>
</html>

