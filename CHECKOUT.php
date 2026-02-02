<?php
session_start();
include 'db_conn.php';

// 检查是否登录
if (!isset($_SESSION['user_id'])) {
    header("Location: LOGIN-REGISTER.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// ★★★ 1. 获取用户详细资料 (用于自动填充表单) ★★★
$sql_user = "SELECT * FROM users WHERE user_id = '$user_id'";
$res_user = $conn->query($sql_user);
$user_data = $res_user->fetch_assoc();

// 2. 获取选中的购物车商品
$cart_subtotal = 0; 
$cart_items = [];
$selected_ids = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['selected_cart_ids'])) {
    $selected_ids = $_POST['selected_cart_ids'];
    $safe_ids = array_map('intval', $selected_ids);
    $id_list = implode(',', $safe_ids);

    $sql = "SELECT cart.cart_id, cart.quantity, products.product_id, products.product_name, products.price, products.product_image 
            FROM cart 
            JOIN products ON cart.product_id = products.product_id 
            WHERE cart.user_id = $user_id AND cart.cart_id IN ($id_list)";

    $result = $conn->query($sql);
    if ($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            $cart_items[] = $row;
            $cart_subtotal += ($row['price'] * $row['quantity']);
        }
    }
} else {
    echo "<script>alert('Please select items to checkout.'); window.location.href='CART.php';</script>";
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout | FurnitureDirect</title>
    <link rel="stylesheet" href="style.css">
    <style>
        body { background-color: #f8f9fa; }
        
        .checkout-container {
            max-width: 1200px;
            margin: 220px auto 100px auto;
            padding: 0 20px;
            display: flex;
            gap: 40px;
            flex-wrap: wrap;
            justify-content: center;
            align-items: flex-start;
        }

        .billing-section {
            flex: 2;
            min-width: 400px;
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.05);
        }

        .billing-section h2 { margin-top: 0; color: #2c3e50; border-bottom: 2px solid #eee; padding-bottom: 15px; margin-bottom: 25px; }

        .form-group { margin-bottom: 20px; }
        .form-group label { display: block; margin-bottom: 8px; font-weight: 600; color: #555; }
        .form-group input, .form-group textarea {
            width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 5px; font-size: 1rem;
        }

        .order-summary-section {
            flex: 1;
            min-width: 350px;
            position: sticky; 
            top: 240px;
        }

        .summary-card {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.05);
        }

        .summary-card h3 { margin-top: 0; color: #2c3e50; margin-bottom: 20px; border-bottom: 1px solid #eee; padding-bottom: 15px; }

        .order-item {
            display: flex; justify-content: space-between; margin-bottom: 15px; color: #666; font-size: 0.95rem; align-items: center;
        }
        
        .order-item img { width: 50px; height: 50px; object-fit: cover; border-radius: 5px; margin-right: 10px; border: 1px solid #eee; }
        
        /* 费用行样式 */
        .cost-row { display: flex; justify-content: space-between; margin-bottom: 10px; color: #555; font-size: 0.95rem; }
        .cost-row.shipping { color: #2c3e50; }
        .cost-row.sst { color: #e67e22; }

        .order-total {
            display: flex; justify-content: space-between; margin-top: 20px; padding-top: 20px;
            border-top: 2px solid #eee; font-size: 1.4rem; font-weight: 800; color: #20c997;
        }

        .place-order-btn {
            display: block; width: 100%; padding: 15px; background-color: #20c997; color: white;
            text-align: center; font-weight: bold; border: none; border-radius: 8px; 
            font-size: 1.1rem; cursor: pointer; margin-top: 25px; transition: 0.3s;
        }
        .place-order-btn:hover { background-color: #17a589; }

        /* --- 支付样式 --- */
        .payment-options { display: flex; flex-direction: column; gap: 15px; margin-bottom: 20px; }
        .payment-card { display: flex; align-items: center; padding: 20px; border: 2px solid #eee; border-radius: 10px; cursor: pointer; transition: all 0.3s ease; background: white; }
        .payment-card:hover { border-color: #20c997; background: #f9fdfc; }
        .payment-card.active { border-color: #20c997; background-color: #e6fffa; box-shadow: 0 4px 15px rgba(32, 201, 151, 0.15); }
        .payment-card input[type="radio"] { width: 20px; height: 20px; margin-right: 15px; accent-color: #20c997; }
        .p-title { font-weight: 700; font-size: 1.05rem; color: #2c3e50; }
        .p-desc { color: #888; font-size: 0.9rem; margin-left: auto; }
        .payment-info-box { background: #f8f9fa; padding: 20px; border-radius: 8px; border: 1px dashed #20c997; margin-bottom: 20px; font-size: 0.95rem; color: #555; animation: slideDown 0.3s ease; }
        @keyframes slideDown { from { opacity: 0; transform: translateY(-10px); } to { opacity: 1; transform: translateY(0); } }
        .sub-options-container { display: flex; gap: 15px; flex-wrap: wrap; }
        .sub-option-card { flex: 1; min-width: 120px; background: white; border: 1px solid #ddd; padding: 10px; border-radius: 6px; text-align: center; cursor: pointer; transition: 0.2s; display: flex; align-items: center; justify-content: center; gap: 8px; }
        .sub-option-card:hover { border-color: #20c997; background-color: #f0fdf9; }
        .sub-option-card input { accent-color: #20c997; }
        .sub-text { font-weight: 600; font-size: 0.9rem; color: #555; }
        .wallet-logo { height: 25px; width: auto; object-fit: contain; }
        .round-input { width: 100%; padding: 12px 15px; border: 1px solid #ccc; border-radius: 25px; font-size: 1rem; outline: none; transition: 0.3s; color: #555; }
        .round-input:focus { border-color: #20c997; box-shadow: 0 0 5px rgba(32, 201, 151, 0.2); }
    </style>
</head>
<body>

    <?php include 'navbar.php'; ?>

    <form action="place_order.php" method="POST" id="checkoutForm">
        
        <div class="checkout-container">
            
            <div class="billing-section">
                <h2 style="border-bottom:none; margin-bottom:10px;">Billing Details</h2>
                
                <div class="form-group">
                    <label>Full Name <span style="color:red">*</span></label>
                    <input type="text" name="fullname" value="<?php echo htmlspecialchars($user_data['username'] ?? ''); ?>" placeholder="John Doe" required class="round-input">
                </div>

                <div class="form-group">
                    <label>Phone <span style="color:red">*</span></label>
                    <input type="text" name="phone" value="<?php echo htmlspecialchars($user_data['phone'] ?? ''); ?>" placeholder="+60 12-345 6789" required class="round-input">
                </div>

                <div class="form-group">
                    <label>Email address <span style="color:red">*</span></label>
                    <input type="email" name="email" value="<?php echo htmlspecialchars($user_data['email'] ?? ''); ?>" placeholder="john@example.com" required class="round-input">
                </div>

                <div class="form-group">
                    <label>Street address <span style="color:red">*</span></label>
                    <input type="text" name="street_1" value="<?php echo htmlspecialchars($user_data['street_1'] ?? ''); ?>" placeholder="House number and street name" required class="round-input" style="margin-bottom: 10px;">
                    <input type="text" name="street_2" value="<?php echo htmlspecialchars($user_data['street_2'] ?? ''); ?>" placeholder="Apartment, suite, unit, etc. (optional)" class="round-input">
                </div>

                <div class="form-group">
                    <label>Town / City <span style="color:red">*</span></label>
                    <input type="text" name="city" value="<?php echo htmlspecialchars($user_data['city'] ?? ''); ?>" required class="round-input">
                </div>

                <div class="form-group">
                    <label>State <span style="color:red">*</span></label>
                    <select name="state" id="stateSelect" required class="round-input" style="background:white;" onchange="calculateFinalTotal()">
                        <option value="">Select State...</option>
                        <?php 
                        $states = ["Johor", "Kedah", "Kelantan", "Melaka", "Negeri Sembilan", "Pahang", "Penang", "Perak", "Perlis", "Sabah", "Sarawak", "Selangor", "Terengganu", "Kuala Lumpur", "Putrajaya", "Labuan"];
                        foreach($states as $st) {
                            // 如果数据库里的 state 和循环到的 $st 一样，就加上 selected
                            $selected = ($user_data['state'] ?? '') === $st ? 'selected' : '';
                            echo "<option value=\"$st\" $selected>$st</option>";
                        }
                        ?>
                    </select>
                </div>

                <div class="form-group">
                    <label>Postcode <span style="color:red">*</span></label>
                    <input type="text" name="postcode" value="<?php echo htmlspecialchars($user_data['postcode'] ?? ''); ?>" required class="round-input">
                </div>

                <h2 style="margin-top: 40px; border-bottom:none;">Payment Method</h2>
                
                <div class="payment-options">
                    <label class="payment-card">
                        <input type="radio" name="payment_method" value="E-Wallet" required onclick="showPaymentInfo('ewallet')">
                        <span class="p-title">📱 E-Wallet</span>
                        <span class="p-desc">Touch 'n Go / GrabPay</span>
                    </label>
                    <div id="info-ewallet" class="payment-info-box" style="display:none;">
                        <div class="sub-options-container">
                            <label class="sub-option-card"><input type="radio" name="ewallet_type" value="Touch 'n Go" checked><img src="img/tng.png" class="wallet-logo"><span class="sub-text">TNG</span></label>
                            <label class="sub-option-card"><input type="radio" name="ewallet_type" value="GrabPay"><img src="img/grab.png" class="wallet-logo"><span class="sub-text">Grab</span></label>
                        </div>
                    </div>

                    <label class="payment-card">
                        <input type="radio" name="payment_method" value="Bank Transfer" onclick="showPaymentInfo('bank')">
                        <span class="p-title">🏦 Online Banking</span>
                        <span class="p-desc">Maybank / CIMB</span>
                    </label>
                    <div id="info-bank" class="payment-info-box" style="display:none;">
                        <div class="sub-options-container"> 
                            <label class="sub-option-card"><input type="radio" name="bank_name" value="Maybank" checked><img src="img/maybank.png" class="wallet-logo"><span class="sub-text">Maybank</span></label>
                            <label class="sub-option-card"><input type="radio" name="bank_name" value="CIMB"><img src="img/cimb.png" class="wallet-logo"><span class="sub-text">CIMB</span></label>
                        </div>
                        <div style="border-top: 1px dashed #ccc; margin: 10px 0; padding-top: 10px;">
                            <p><strong>Merchant Bank:</strong> Maybank</p>
                            <p><strong>Account No:</strong> 5566-7788-9900</p>
                            <p><strong>Holder Name:</strong> Furniture Direct Sdn Bhd</p>
                            <small style="color: #e74c3c;">* Please upload the receipt after transfer.</small>
                        </div>
                    </div>
                </div>

                <?php foreach($selected_ids as $id): ?>
                    <input type="hidden" name="selected_cart_ids[]" value="<?php echo $id; ?>">
                <?php endforeach; ?>
                
                <input type="hidden" name="grand_total" id="inputGrandTotal" value="<?php echo $cart_subtotal; ?>">

            </div>

            <div class="order-summary-section">
                <div class="summary-card">
                    <h3>Order Summary</h3>
                    
                    <?php if (!empty($cart_items)): ?>
                        <?php foreach($cart_items as $item): ?>
                            <div class="order-item">
                                <div style="display:flex; align-items:center;">
                                    <img src="img/<?php echo $item['product_image']; ?>" alt="img">
                                    <div>
                                        <div style="font-weight:600; color:#333;"><?php echo $item['product_name']; ?></div>
                                        <div style="font-size:0.85rem;">Qty: <?php echo $item['quantity']; ?></div>
                                    </div>
                                </div>
                                <div style="font-weight:600;">RM <?php echo number_format($item['price'] * $item['quantity'], 2); ?></div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                    
                    <hr style="border:0; border-top:1px solid #eee; margin:15px 0;">

                    <div class="cost-row">
                        <span>Items Subtotal</span>
                        <span>RM <?php echo number_format($cart_subtotal, 2); ?></span>
                    </div>

                    <div class="cost-row sst">
                        <span>Sales Tax (10%)</span>
                        <span id="displaySST">RM 0.00</span>
                    </div>

                    <div class="cost-row shipping">
                        <span>Shipping Fee</span>
                        <span id="displayShipping">Calculated...</span>
                    </div>

                    <div class="order-total">
                        <span>Grand Total</span>
                        <span id="displayGrandTotal">RM <?php echo number_format($cart_subtotal, 2); ?></span>
                    </div>

                    <button type="submit" class="place-order-btn">PLACE ORDER</button>
                    <div style="text-align:center; margin-top:15px;">
                        <a href="CART.php" style="color:#666; text-decoration:none;">← Back to Cart</a>
                    </div>
                </div>
            </div>
            
        </div>
    </form>
    
    <script>
        const cartSubtotal = <?php echo $cart_subtotal; ?>;
        
        function calculateFinalTotal() {
            const state = document.getElementById('stateSelect').value;
            let shippingFee = 0;
            let sstAmount = 0;
            let grandTotal = 0;

            if (cartSubtotal > 2000) {
                shippingFee = 0;
            } else {
                if (state === 'Sabah' || state === 'Sarawak' || state === 'Labuan') {
                    shippingFee = 200;
                } else if (state === '') {
                    shippingFee = 0; 
                } else {
                    shippingFee = 100; // 西马
                }
            }

            sstAmount = cartSubtotal * 0.10; // Sales Tax 10%
            grandTotal = cartSubtotal + shippingFee + sstAmount;

            document.getElementById('displaySST').innerText = 'RM ' + sstAmount.toFixed(2);
            
            if (shippingFee === 0 && cartSubtotal > 2000) {
                document.getElementById('displayShipping').innerHTML = '<span style="color:#20c997; font-weight:bold;">FREE (Order > RM2000)</span>';
            } else if (state === '') {
                document.getElementById('displayShipping').innerText = 'Select State...';
            } else {
                document.getElementById('displayShipping').innerText = 'RM ' + shippingFee.toFixed(2);
            }

            document.getElementById('displayGrandTotal').innerText = 'RM ' + grandTotal.toFixed(2);
            document.getElementById('inputGrandTotal').value = grandTotal;
        }

        function showPaymentInfo(type) {
            document.querySelectorAll('.payment-info-box').forEach(box => box.style.display = 'none');
            const infoBox = document.getElementById('info-' + type);
            if(infoBox) infoBox.style.display = 'block';

            document.querySelectorAll('.payment-card').forEach(card => card.classList.remove('active'));
            const selectedRadio = document.querySelector('input[name="payment_method"]:checked');
            if (selectedRadio) selectedRadio.closest('.payment-card').classList.add('active');
        }

        // 页面加载完成后，立刻计算一次（因为现在会自动填入 State，所以需要立刻算出运费）
        window.onload = function() {
            calculateFinalTotal();
        };
    </script>
</body>
</html>