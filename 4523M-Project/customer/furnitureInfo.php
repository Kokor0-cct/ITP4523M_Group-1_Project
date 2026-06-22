<?php
session_start();
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
        <a href="#">shopping cart</a>
        <a href="#">My Orders</a>
        <a href="#">My account</a>
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


            <div class="custom-type">
                <label>
                    <input type="radio" name="customMode" value="normal" checked onchange="toggleCustomMode()"> Normal
                </label>
                <label>
                    <input type="radio" name="customMode" value="custom" onchange="toggleCustomMode()"> Customized
                </label>
            </div>


            <div class="custom-option">
                <label for="material">Material Selection</label>
                <select id="material" class="custom-select" disabled>
                    <option value="oak" selected>Oak (Default)</option>
                    <option value="walnut">Birch</option>
                    <option value="teak">Pine</option>
                </select>
            </div>

            <div class="custom-option">
                <label for="fabric">Fabric Material</label>
                <select id="fabric" class="custom-select" disabled>
                    <option value="linen" selected>Linen Fabric (Default)</option>
                    <option value="cotton">Pure Cotton Fabric</option>
                    <option value="leather">Technology Fabric</option>
                </select>
            </div>

            <div class="custom-option">
                <label for="color">Material Selection2</label>
                <select id="color" class="custom-select" disabled>
                    <option value="natural" selected>None</option>
                    <option value="coffee">Metal</option>
                    <option value="white">Plastic</option>
                </select>
            </div>

            <div class="custom-option">
                <label for="size">Size Specification</label>
                <select id="size" class="custom-select" disabled>
                    <option value="three" selected>Single Seat (85*85*90cm) (Default)</option>
                    <option value="two">Two Seats (150*85*90cm)</option>
                    <option value="single">Three Seats (210*85*90cm)</option>
                </select>
            </div>


            <div class="quantity-control">
                <label for="quantity">Purchase Quantity</label>
                <input type="number" id="quantity" class="quantity-input" min="1" max="8" value="1">
            </div>


            <button class="add-cart-btn" id="addToCartBtn" onclick="return addToCart()">Add to Cart</button>
        </div>
    </div>

    <script>

        function goBack() {
            window.location.href = 'productList.php';
        }


        function toggleCustomMode() {

            const selectedMode = document.querySelector('input[name="customMode"]:checked').value;
   
            const customSelects = document.querySelectorAll('.custom-select');
            
            const isDisabled = selectedMode === 'normal';
            customSelects.forEach(select => {
                select.disabled = isDisabled;
                select.selectedIndex = 0;
            });
        }

    
        function addToCart() {
   
            const stockInfoText = document.getElementById('stockInfo').textContent;
            const stock = parseInt(stockInfoText.replace(/[^0-9]/g, '')); 

            const quantityInput = document.getElementById('quantity');
            const buyQuantity = parseInt(quantityInput.value);

            if (isNaN(buyQuantity)) {
                alert('Please enter a valid purchase quantity!');
                quantityInput.value = 1;
                return false;
            }

            if (buyQuantity > stock) {
                alert(`Insufficient stock! Current stock is only ${stock} pieces, unable to purchase ${buyQuantity} pieces.`);
                quantityInput.value = stock;
                return false;
            }
            if (buyQuantity < 1) {
                alert('Please enter a valid purchase quantity!');
                quantityInput.value = 1; 
                return false;
            } 
            alert(`Added to cart successfully!`);
        }


        function logout() {
            if (confirm('Are you sure to log out?')) {
                alert('Logout successful! Redirecting to login page shortly');
                window.location.href = 'customerLogin.html';
            }
        }

        function productPage(){
            window.location.href = 'prodecuList.html';
        }

        function AccountPage(){
            window.location.href = 'myAccount.html';
        }

        function orderPage(){
            window.location.href = 'order.html';
        }

        function cartPage(){
            window.location.href = 'cart.html'; 
        }

    </script>
</body>
</html>