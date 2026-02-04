<?php
session_start();
include 'db_conn.php';

// 1. 检查是否登录
if (!isset($_SESSION['user_id'])) {
    header("Location: LOGIN-REGISTER.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_SESSION['user_id'];
    
    // 获取选中的购物车 ID
    if (!isset($_POST['selected_cart_ids']) || empty($_POST['selected_cart_ids'])) {
        die("Error: No items selected for checkout.");
    }
    
    $selected_ids = $_POST['selected_cart_ids'];
    $id_list = implode(',', array_map('intval', $selected_ids));

    // 2. 接收地址和用户信息
    $fullname = $conn->real_escape_string($_POST['fullname']);
    $email = $conn->real_escape_string($_POST['email']);
    $phone = $conn->real_escape_string($_POST['phone']);
    
    $street_1 = $_POST['street_1'];
    $street_2 = $_POST['street_2'];
    $city = $_POST['city'];
    $state = $_POST['state']; 
    $postcode = $_POST['postcode'];

    $full_address_string = "$street_1, $street_2, $city, $postcode, $state";
    $address = $conn->real_escape_string($full_address_string);
    
    // 接收支付方式
    $payment_method = $conn->real_escape_string($_POST['payment_method']); 
    if ($payment_method === 'E-Wallet' && isset($_POST['ewallet_type'])) {
        $payment_method .= " (" . $conn->real_escape_string($_POST['ewallet_type']) . ")";
    } 
    if ($payment_method === 'Bank Transfer' && isset($_POST['bank_name'])) {
        $payment_method .= " (" . $conn->real_escape_string($_POST['bank_name']) . ")";
    }

    // ---------------------------------------------------
    // A. 后端重新计算总价 (防止黑客篡改前端价格)
    // ---------------------------------------------------
    
    // 1. 算商品总价
    $calc_subtotal = 0;
    $sql_check = "SELECT cart.quantity, products.price 
                  FROM cart 
                  JOIN products ON cart.product_id = products.product_id 
                  WHERE cart.user_id = $user_id AND cart.cart_id IN ($id_list)";
    $res_check = $conn->query($sql_check);
    
    while($row = $res_check->fetch_assoc()){
        $calc_subtotal += ($row['price'] * $row['quantity']);
    }

    // 2. 算运费 (规则：>2000 免邮)
    $calc_shipping = 0;
    if ($calc_subtotal > 2000) {
        $calc_shipping = 0; 
    } else {
        if ($state === 'Sabah' || $state === 'Sarawak' || $state === 'Labuan') {
            $calc_shipping = 200; // 东马
        } else {
            $calc_shipping = 100; // 西马
        }
    }

    // 3. 算 Sales Tax (10%)
    $calc_sst = $calc_subtotal * 0.10;

    // 4. 最终总价
    $final_grand_total = $calc_subtotal + $calc_shipping + $calc_sst;


    // ---------------------------------------------------
    // B. 创建订单
    // ---------------------------------------------------
    $order_str_id = "ORD-" . strtoupper(uniqid());

    $sql_order = "INSERT INTO orders (order_id, user_id, customer_name, email, phone, address, payment_method, grand_total) 
                  VALUES ('$order_str_id', '$user_id', '$fullname', '$email', '$phone', '$address', '$payment_method', '$final_grand_total')";

    if ($conn->query($sql_order) === TRUE) {
        $order_auto_id = $conn->insert_id;

        // C. 搬运商品 + ★★★ 扣减库存 ★★★
        $sql_cart = "SELECT cart.product_id, cart.quantity, products.price 
                     FROM cart 
                     JOIN products ON cart.product_id = products.product_id 
                     WHERE cart.user_id = $user_id AND cart.cart_id IN ($id_list)";
        
        $cart_result = $conn->query($sql_cart);

        if ($cart_result) {
            while($item = $cart_result->fetch_assoc()) {
                $pid = $item['product_id'];
                $qty = $item['quantity'];
                $price = $item['price'];

                // 1. 插入到 order_items 表
                $conn->query("INSERT INTO order_items (order_id, product_id, quantity, price) 
                              VALUES ('$order_auto_id', '$pid', '$qty', '$price')");

                // 2. ★★★ 新增：从 products 表扣减对应库存 ★★★
                // 逻辑：旧库存 - 购买数量 = 新库存
                $conn->query("UPDATE products SET stock_quantity = stock_quantity - $qty WHERE product_id = '$pid'");
            }
        }

        // D. 清空选中的购物车商品
        $conn->query("DELETE FROM cart WHERE user_id = $user_id AND cart_id IN ($id_list)");

        // E. 成功跳转
        header("Location: ORDER-SUCCESS.php?id=" . $order_str_id);
        exit;

    } else {
        echo "Error placing order: " . $conn->error;
    }
}
?>