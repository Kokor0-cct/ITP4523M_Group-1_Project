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

$sql = "SELECT * FROM customers WHERE cname='$userName'";
$rs = mysqli_query($conn, $sql);

$UserInfo = mysqli_fetch_assoc($rs);
if (!$UserInfo) {
    header("Location: productList.php");
    exit;
}
if (empty($UserInfo['company']))
    $Company="No Company!";
else
    $Company=$UserInfo['company'];
$UserPassword=$UserInfo['cpassword'];


$msg = "";
$msgColor ="red";




if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $editType = $_POST['editType'];
    $newPwd = trim($_POST['newPwd'] ?? '');
    $confirmPwd = trim($_POST['confirmPwd'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $address = trim($_POST['address'] ?? '');    

    if ($editType === 'password') {
        if (empty($newPwd) || empty($confirmPwd)) {
            $msg = "Password cannot be empty!";
        } elseif ($newPwd != $confirmPwd) {
            $msg = "The two passwords do not match!";
        } elseif (!preg_match('/^(?=.*[A-Za-z])(?=.*\d)[A-Za-z0-9]{7,12}$/', $newPwd)) {
            $msg = "Password must be 7-12 characters long and contain letters and numbers!";
        }else {
            $update_stmt = $conn->prepare("UPDATE customers SET cpassword = ? WHERE cname = ?");
            $update_stmt->bind_param("ss", $newPwd, $userName);
            if ($update_stmt->execute()) {
                 $msg = "Password changed successfully";
                 $msgColor ="green";

                 echo "<script>
                    setTimeout(function(){
                        location.href = 'myAccount.php';
                         }, 2000); 
                        </script>";
            } else {
                 $msg = "Failed to change password";
            }
        }
    }
    if ($editType === 'info') {
        if (empty($phone) || empty($address)) {
            $msg = "Info cannot be empty!";
        }  elseif (!preg_match('/^[1-9]\d{7}$/', $phone)) {
            $msg = "Phone number must be 8 numbers long and No other symbols are allowed!";
        }else {
            $update_stmt = $conn->prepare("UPDATE customers SET ctel=?, caddr=? WHERE cname=?");
            $update_stmt->bind_param("sss", $phone, $address,$userName);
            if ($update_stmt->execute()) {
                 $msg = "Info Update successfully";
                 $msgColor ="green";
                echo "<script>
                    setTimeout(function(){
                        location.href = 'myAccount.php';
                         }, 2000); 
                        </script>";
            } else {
                 $msg = "Failed to Update Info";
            }
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
    <title>My Account - Personal Profile Management</title> 
    <link rel="stylesheet" href="../CSS/style.css">

</head>
<body class="body-customer"> 

    <div class="nav">
        <a  href="productList.php">Browse products</a>
        <a href="#">shopping cart</a>
        <a href="#">My Orders</a>
        <a class="active" href="myAccount.php">My account</a>
    </div>


    <div class="container">
        <h1>Personal Profile Management</h1>
        <?php if ($msg): ?>
            <div class="alert alert-danger" style="color:<?php echo $msgColor; ?>"><?php echo $msg; ?></div>
        <?php endif; ?>

        <div class="form-card" >
            <h3>Customer Basic Information</h3>
        
            <div class="form-group">
                <label>Customer ID</label>
                <input type="text" id="customerId" value="CUS-<?php echo $UserInfo['cid'];?>" readonly>
            </div>
            <div class="form-group">
                <label>Contact Phone</label>
                <input type="text" id="phone" value="<?php echo $UserInfo['ctel'];?>" readonly>
            </div>
            <div class="form-group">
                <label>Customer Address</label>
                <input type="text" id="address" value="<?php echo $UserInfo['caddr'];?>" readonly>
            </div>
            <div class="form-group">
                <label>Affiliated Company</label>
                <input type="text" id="Company" value="<?php echo $Company;?>" readonly>
            </div>
            <button id="showEditInfoBtn" class="btn btn-primary" style="width:30%;">Edit Personal Profile</button>
            <button id="showEditPasswordBtn" class="btn btn-primary" style="width:30%;">Edit Password</button>

        </div>
        
        
        <div class="profileForm-card" id="editProfileCard" style="display: none;">
            <h3>Update Personal Profile</h3>
            <form id="profileForm" method="POST" action="">
                <input type="hidden" name="editType" id="editTypeInput" value="">
                <div class="form-group" id="PasswordEdit1"  style="display: none;">
                    <label for="password">New Password</label>
                    <input type="password" id="Newpassword" name="newPwd">
                </div>
                <div class="form-group" id="PasswordEdit2" style="display: none;">
                    <label for="confirmPassword">Confirm Password</label>
                    <input type="password" id="confirmPassword" name="confirmPwd" >
                </div>
                <div class="form-group" id="PhoneEdit" style="display: none;"> 
                    <label for="phone">Contact Phone</label>
                    <input type="text" id="modify-phone" name="phone" value="<?php echo $UserInfo['ctel'];?>">
                </div>
                <div class="form-group" id="AddressEdit" style="display: none;">
                    <label for="address">Customer Address</label>
                    <input type="text" id="modify-address" name="address" value="<?php echo $UserInfo['caddr'];?>">
                </div>
                
                    <button type="submit" class="btn btn-primary" name="SubmitData" style="width:30%;">Submit</button>
                     

                    <button type="reset" id="ResetFrom" class="btn btn-primary" style="background: #6c757d; margin: 2px;width:30%;">Cancel</button>
               
                <div id="message"> <?php echo $msg ?>></div> 
            </form>
        </div>
    </div>



    <script>
    const editCard = document.getElementById('editProfileCard');
    const PhoneEdit = document.getElementById('PhoneEdit');
    const AddressEdit = document.getElementById('AddressEdit');
    const PasswordEdit1 = document.getElementById('PasswordEdit1');
    const PasswordEdit2 = document.getElementById('PasswordEdit2');

    const closeBtn = document.getElementById('closeEditBtn');
    const showInfoBtn = document.getElementById('showEditInfoBtn');
    const showPassBtn = document.getElementById('showEditPasswordBtn');
    const ResetFrom = document.getElementById('ResetFrom');

    const editTypeInput = document.getElementById('editTypeInput');


    showInfoBtn.addEventListener('click', function(){
        PasswordEdit1.style.display = 'none';
        PasswordEdit2.style.display = 'none';
        clear();
        
            
        editTypeInput.value = 'info';
        editCard.style.display = 'block';
        PhoneEdit.style.display = 'block';
        AddressEdit.style.display = 'block';

    });

    showPassBtn.addEventListener('click', function(){
        PhoneEdit.style.display = 'none';
        AddressEdit.style.display = 'none';
        clear() ;
    
        editTypeInput.value = 'password';
        editCard.style.display = 'block';
        PasswordEdit1.style.display = 'block';
        PasswordEdit2.style.display = 'block';
    });

     ResetFrom.addEventListener('click', function(){
        editCard.style.display = 'none';
        clear();
    });

    function clear(){
        document.getElementById('profileForm').reset();
        document.getElementById('message').innerHTML = '';
    }

   
    </script>



</body>
</html>