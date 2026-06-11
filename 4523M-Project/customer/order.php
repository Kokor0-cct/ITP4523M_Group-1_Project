<?php
// 数据库连接配置（和你 customerOrder.php 保持一致）
$host = "127.0.0.1";
$username = "root";
$password = "";
$db = "itp4523m_projectdb";

// 建立连接
$conn = mysqli_connect($host, $username, $password, $db);
// 连接失败提示
if (!$conn) {
    die("Database connect error!" . mysqli_connect_error());
}

// 查询订单表
$sql = "SELECT * FROM orders";
$result = mysqli_query($conn, $sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Product Browsing</title>
    <link rel="stylesheet" href="../CSS/style.css">
</head>
<body>

<!-- 你的原有导航/按钮代码 放在这里 -->
<div class="nav">
        <a onclick="productPage()">Browse products</a>
        <a onclick="cartPage()">Shopping Cart</a>
        <a class="active" onclick="orderPage()">My Orders</a>
        <a onclick="AccountPage()">My Account</a>
        <a onclick="logout()">Logout</a>
    </div>

    <div class="table-card">
        <div class="header">
            <h1>Order List</h1>
        </div>
        <div class="sort-btn-group">
            <button class="sortable" onclick="sortTable(0)">Sort by Order ID</button>
            <button class="sortable" onclick="sortTable(1)">Sort by Date</button>
    </div>
<!-- 订单表格 -->
<table id="orderTable">
    <thead>
        <tr>
            <th>Order ID</th>
            <th>Order Date</th>
            <th>Total Amount</th>
            <th>Customer ID</th>
            <th>Delivery Date</th>
            <th>Order Address</th>
            <th>Order Status</th>
            <th>Order Info</th>
            <th>Cancel Order</th>
        </tr>
    </thead>
    <tbody>
        <?php
        // 循环输出每一行数据
        if (mysqli_num_rows($result) > 0) {
            while ($row = mysqli_fetch_assoc($result)) {
        ?>
        <tr>
            <td><?php echo $row['oid']; ?></td>
            <td><?php echo $row['odate']; ?></td>
            <td><?php echo $row['ototalamount']; ?></td>
            <td><?php echo $row['cid']; ?></td>
            <td><?php echo $row['odeliverydate']; ?></td>
            <td><?php echo $row['odeliveraddress']; ?></td>
            <td><?php echo $row['ostatus']; ?></td>
            <td>
                <button >Detail</button>
            </td>
            <td>
                <button class="button-orderdel">Delete Order</button>
            </td>
            
        </tr>
        <?php
            }
        } else {
            // 无数据时显示
        ?>
        <tr>
            <td colspan="8">No orders found</td>
        </tr>
        <?php } ?>
    </tbody>
</table>

<?php
// 关闭数据库连接
mysqli_close($conn);
?>
<p style="text-align: left; ">Today is: 2025-12-28</p>
</body>
</html>


    <script>
        function logout(){
            if(confirm('Are you sure you want to log out?')){
                alert('Logout successful! Returning to login page shortly');
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

       let sortAsc = true;

        function sortTable(columnIndex) {
            const table = document.getElementById("orderTable");
            const tbody = table.querySelector("tbody");
            const rows = Array.from(tbody.querySelectorAll("tr"));

            rows.sort((a, b) => {
                const cellA = a.cells[columnIndex].textContent.trim();
                const cellB = b.cells[columnIndex].textContent.trim();

                if (columnIndex === 0) {
                    return sortAsc ? cellA.localeCompare(cellB) : cellB.localeCompare(cellA);
                } else if (columnIndex === 2) {
                    const dateA = new Date(cellA);
                    const dateB = new Date(cellB);
                    return sortAsc ? dateA - dateB : dateB - dateA;
                }
                return 0;
            });

            tbody.innerHTML = "";
            rows.forEach(row => tbody.appendChild(row));
            sortAsc = !sortAsc;
        }

        function del(btn) {
            let row = btn.closest("tr");
            let deliveryDateStr = row.cells[7].textContent.trim(); 
            let deliveryDate = new Date(deliveryDateStr);

            let today = new Date("2025-12-28");
            today.setHours(0,0,0,0);

            let timeDiff = deliveryDate - today;
            let dayDiff = Math.ceil(timeDiff / (1000 * 60 * 60 * 24));  

            if (dayDiff < 2) {
                alert("Cannot delete! Orders can only be deleted 2 days before the delivery date");
                return;
            }

            if (confirm("Are you sure to delete?")) {
                row.remove(); 
                alert("Deleted successfully!");
            }
        }   

        
    </script>
</body>
</html>