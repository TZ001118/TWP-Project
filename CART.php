<?php
session_start();
include 'db_conn.php'; 

// 检查登录
if (!isset($_SESSION['user_id'])) {
    header("Location: LOGIN-REGISTER.php");
    exit;
}

$user_id = $_SESSION['user_id'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shopping Cart | FurnitureDirect</title>
    <link rel="stylesheet" href="style.css">
    
    <style>
        body { background-color: #f8f9fa; }
        .cart-container {
            max-width: 1200px; 
            margin: 220px auto 100px auto;
            padding: 0 20px;
            display: flex; gap: 30px; flex-wrap: wrap; justify-content: center;
        }
        .cart-items-section { flex: 2; min-width: 600px; }
        .section-title { font-size: 1.8rem; color: #2c3e50; margin-bottom: 25px; font-weight: 700; }
        
        .cart-table { width: 100%; border-collapse: separate; border-spacing: 0 15px; }
        .cart-table thead th { text-align: left; padding: 0 20px; color: #888; font-size: 0.9rem; font-weight: 700; text-transform: uppercase; border-bottom: 2px solid #eee; padding-bottom: 15px; }
        .cart-table thead th:nth-child(3), .cart-table thead th:nth-child(4), .cart-table thead th:nth-child(5) { text-align: center; }
        .cart-table tbody td:nth-child(3), .cart-table tbody td:nth-child(4), .cart-table tbody td:nth-child(5) { text-align: center; }
        .cart-table tbody tr { background-color: white; box-shadow: 0 5px 15px rgba(0,0,0,0.03); border-radius: 10px; transition: transform 0.2s; }
        .cart-table tbody tr:hover { transform: translateY(-2px); }
        .cart-table td { padding: 20px; vertical-align: middle; }
        .cart-table td:first-child { border-top-left-radius: 10px; border-bottom-left-radius: 10px; }
        .cart-table td:last-child { border-top-right-radius: 10px; border-bottom-right-radius: 10px; }
        
        /* 复选框样式 */
        .select-checkbox { width: 20px; height: 20px; accent-color: #20c997; cursor: pointer; }

        .product-col { display: flex; align-items: center; gap: 20px; }
        .product-img { width: 100px; height: 100px; object-fit: cover; border-radius: 8px; border: 1px solid #eee; }
        .product-info h4 { margin: 0 0 5px 0; color: #2c3e50; font-size: 1.1rem; }
        .product-info small { color: #999; }
        .qty-input { width: 50px; padding: 8px; text-align: center; border: 1px solid #ddd; border-radius: 5px; font-weight: bold; color: #333; }
        .price-text { font-weight: 600; color: #555; }
        .subtotal-text { font-weight: 700; color: #20c997; font-size: 1.1rem; }
        .remove-btn { color: #e74c3c; font-size: 1.5rem; text-decoration: none; opacity: 0.5; transition: 0.3s; }
        .remove-btn:hover { opacity: 1; transform: scale(1.1); }
        
        .cart-summary-section { flex: 1; min-width: 350px; }
        .summary-card { background: white; padding: 25px; border-radius: 10px; box-shadow: 0 5px 20px rgba(0,0,0,0.05); position: sticky; top: 240px; z-index: 10; }
        .summary-row { display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px; color: #666; font-size: 0.95rem; }
        .summary-divider { height: 1px; background-color: #eee; margin: 20px 0; }
        .total-row { font-size: 1.4rem; font-weight: 800; color: #2c3e50; margin-top: 20px; padding-top: 20px; border-top: 2px solid #eee; }
        
        .checkout-btn { display: block; width: 100%; padding: 15px; background-color: #20c997; color: white; text-align: center; text-decoration: none; font-weight: bold; border-radius: 8px; box-shadow: 0 4px 10px rgba(32, 201, 151, 0.2); transition: 0.3s; border: none; cursor: pointer; }
        .checkout-btn:hover { background-color: #17a589; transform: translateY(-2px); box-shadow: 0 6px 15px rgba(32, 201, 151, 0.3); }
        .checkout-btn:disabled { background-color: #ccc; cursor: not-allowed; transform: none; box-shadow: none; }
        
        .continue-link { display: block; text-align: center; margin-top: 15px; color: #888; text-decoration: none; font-size: 0.9rem; }
        .continue-link:hover { color: #20c997; text-decoration: underline; }
    </style>
</head>
<body>

    <?php include 'navbar.php'; ?>

    <div class="cart-container">
        
        <form id="cartForm" action="CHECKOUT.php" method="POST" class="cart-items-section">
            <h1 class="section-title">Shopping Cart</h1>

            <table class="cart-table">
                <thead>
                    <tr>
                        <th style="width: 5%; text-align: center;">
                            <input type="checkbox" id="selectAll" class="select-checkbox" onclick="toggleSelectAll()">
                        </th>
                        <th style="width: 45%;">Product Details</th>
                        <th style="width: 15%;">Quantity</th>
                        <th style="width: 15%;">Price</th>
                        <th style="width: 15%;">Total</th>
                        <th style="width: 5%;"></th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $sql = "SELECT 
                                cart.cart_id, 
                                cart.quantity, 
                                products.product_id, 
                                products.product_name, 
                                products.price, 
                                products.product_image, 
                                categories.category_name 
                            FROM cart 
                            JOIN products ON cart.product_id = products.product_id 
                            JOIN categories ON products.category_id = categories.category_id
                            WHERE cart.user_id = $user_id";
                    
                    $result = $conn->query($sql);

                    if ($result && $result->num_rows > 0) {
                        while($row = $result->fetch_assoc()) {
                            $subtotal = $row['price'] * $row['quantity'];
                            ?>
                            
                            <tr data-cart-id="<?php echo $row['cart_id']; ?>">
                                <td style="text-align: center;">
                                    <input type="checkbox" 
                                           name="selected_cart_ids[]" 
                                           value="<?php echo $row['cart_id']; ?>" 
                                           class="select-checkbox item-checkbox"
                                           onchange="calculateTotal()">
                                </td>
                                
                                <td>
                                    <div class="product-col">
                                        <a href="PRODUCT_DETAILS.php?id=<?php echo $row['product_id']; ?>&from=cart">
                                            <img src="img/<?php echo $row['product_image']; ?>" alt="<?php echo $row['product_name']; ?>" class="product-img">
                                        </a>
                                        
                                        <div class="product-info">
                                            <h4>
                                                <a href="PRODUCT_DETAILS.php?id=<?php echo $row['product_id']; ?>" style="text-decoration: none; color: inherit;">
                                                    <?php echo $row['product_name']; ?>
                                                </a>
                                            </h4>
                                            <small>Category: <?php echo $row['category_name']; ?></small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <input type="number" 
                                            value="<?php echo $row['quantity']; ?>" 
                                            min="1" 
                                            class="qty-input" 
                                            data-cart-id="<?php echo $row['cart_id']; ?>" 
                                            onchange="updateQuantity(this)">
                                </td>
                                <td class="price-text" data-price="<?php echo $row['price']; ?>">RM <?php echo number_format($row['price'], 2); ?></td>
                                <td class="subtotal-text">RM <?php echo number_format($subtotal, 2); ?></td>
                                
                                <td>
                                    <a href="javascript:void(0)" class="remove-btn" data-cart-id="<?php echo $row['cart_id']; ?>" onclick="removeItem(this)">&times;</a>
                                </td>
                            </tr>

                            <?php
                        }
                    } else {
                        echo "<tr><td colspan='6' style='text-align:center; padding:50px;'>Your cart is empty! <a href='FURNITURE.php' style='color:#20c997;'>Go Shop Now</a></td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </form>

        <div class="cart-summary-section">
            <div class="summary-card">
                <h3 style="margin-top:0; color:#2c3e50;">Order Summary</h3>
                <div class="summary-divider"></div>

                <div class="summary-row">
                    <span>Subtotal</span>
                    <span id="cart-subtotal">RM 0.00</span>
                </div>
                <div class="summary-row">
                    <span>Shipping</span>
                    <span style="color:#20c997; font-size:0.8rem;">Calculated at checkout</span>
                </div>

                <div class="summary-divider"></div>

                <div class="total-row">
                    <span>Total</span>
                    <span id="cart-grand-total">RM 0.00</span>
                </div>

                <button type="submit" form="cartForm" id="checkoutBtn" class="checkout-btn" disabled>Proceed to Checkout (0)</button>
                <a href="FURNITURE.php" class="continue-link">← Continue Shopping</a>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        // --- 1. 计算总价 (只算选中的) ---
        function calculateTotal() {
            let total = 0;
            let checkedCount = 0;
            const rows = document.querySelectorAll('.cart-table tbody tr');

            rows.forEach(row => {
                const checkbox = row.querySelector('.item-checkbox');
                
                // 只有被勾选的行才参与计算
                if (checkbox && checkbox.checked) {
                    checkedCount++;
                    const priceElement = row.querySelector('.price-text');
                    const quantityElement = row.querySelector('.qty-input');
                    const subtotalElement = row.querySelector('.subtotal-text');

                    let price = parseFloat(priceElement.getAttribute('data-price'));
                    let quantity = parseInt(quantityElement.value);

                    if (isNaN(quantity) || quantity < 1) quantity = 1;

                    let subtotal = price * quantity;
                    
                    // 更新这一行的显示
                    subtotalElement.innerText = 'RM ' + subtotal.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2});
                    
                    // 加到总价
                    total += subtotal;
                }
            });

            // 更新右边栏显示
            const subtotalEl = document.getElementById('cart-subtotal');
            const grandTotalEl = document.getElementById('cart-grand-total');
            const checkoutBtn = document.getElementById('checkoutBtn');
            
            const formattedTotal = 'RM ' + total.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2});
            
            subtotalEl.innerText = formattedTotal;
            grandTotalEl.innerText = formattedTotal;
            
            // 更新按钮状态
            checkoutBtn.innerText = `Proceed to Checkout (${checkedCount})`;
            checkoutBtn.disabled = checkedCount === 0; // 没选商品就禁用按钮
        }

        // --- 2. 全选/全不选 ---
        function toggleSelectAll() {
            const selectAllBox = document.getElementById('selectAll');
            const checkboxes = document.querySelectorAll('.item-checkbox');
            
            checkboxes.forEach(cb => {
                cb.checked = selectAllBox.checked;
            });
            
            calculateTotal(); // 重新计算
        }

        // --- 3. 更新数量 ---
        function updateQuantity(input) {
            let cartId = input.getAttribute('data-cart-id');
            let quantity = input.value;
            
            // 发送给后台更新 (保持后台逻辑，即使用户不刷新页面，下次进来数量也是对的)
            let formData = new FormData();
            formData.append('cart_id', cartId);
            formData.append('quantity', quantity);
            
            fetch('update_cart.php', { method: 'POST', body: formData });
            
            // 重新计算总价
            calculateTotal();
        }

        // --- 4. ★★★ 漂亮的删除功能 (SweetAlert2) ★★★ ---
        function removeItem(element) {
            var cartId = element.getAttribute('data-cart-id');
            
            if (!cartId) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Cart ID is missing!'
                });
                return;
            }

            // 使用 SweetAlert2 替代 confirm
            Swal.fire({
                title: 'Remove Item?',
                text: "Do you really want to remove this item from your cart?",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#e74c3c', // 红色确认按钮
                cancelButtonColor: '#95a5a6',  // 灰色取消按钮
                confirmButtonText: 'Yes, remove it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    // 用户点了 Yes，执行删除逻辑
                    var formData = new FormData();
                    formData.append('cart_id', cartId);

                    fetch('remove_from_cart.php', {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => response.text().then(text => {
                        try {
                            return JSON.parse(text);
                        } catch (e) {
                            throw new Error("Server returned non-JSON data: " + text.substring(0, 200));
                        }
                    }))
                    .then(data => {
                        if (data.success) {
                            // 成功提示，并在 1.5秒后刷新页面
                            Swal.fire({
                                icon: 'success',
                                title: 'Removed!',
                                text: 'Item has been removed from cart.',
                                timer: 1500,
                                showConfirmButton: false
                            }).then(() => {
                                location.reload(); 
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Oops...',
                                text: data.message || 'Unknown error'
                            });
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        Swal.fire({
                            icon: 'error',
                            title: 'System Error',
                            text: error.message
                        });
                    });
                }
            });
        }
        
        // 页面加载时先算一次(如果需要默认全选，可以在这里加逻辑)
        calculateTotal();
    </script>
</body>
</html>