<?php
session_start();
include 'db_conn.php';

// ★★★ 核心修复：登录拦截 ★★★
// 如果没有 user_id (说明没登录)，直接跳转去 LOGIN-REGISTER.php
if (!isset($_SESSION['user_id'])) {
    header("Location: LOGIN-REGISTER.php"); 
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $uid = $_SESSION['user_id'];
    $pid = intval($_POST['product_id']);
    $qty = isset($_POST['quantity']) ? intval($_POST['quantity']) : 1;

    // 1. 检查购物车里是否已经有这个商品
    $check_sql = "SELECT * FROM cart WHERE user_id='$uid' AND product_id='$pid'";
    $result = $conn->query($check_sql);

    if ($result->num_rows > 0) {
        // 2. 如果有，就更新数量 (原有数量 + 新加数量)
        $conn->query("UPDATE cart SET quantity = quantity + $qty WHERE user_id='$uid' AND product_id='$pid'");
    } else {
        // 3. 如果没有，就插入一条新记录
        $conn->query("INSERT INTO cart (user_id, product_id, quantity) VALUES ('$uid', '$pid', '$qty')");
    }

    // 设置 Session 消息 (给非 AJAX 访问保留的兼容性)
    $_SESSION['swal'] = ['type' => 'success', 'title' => 'Added to Cart', 'text' => 'Item added successfully.'];
    
    //以此响应结束 (对于 AJAX，这里其实不重要，重要的是 status code，但为了兼容性保留跳转)
    header("Location: " . $_SERVER['HTTP_REFERER']);
    exit();
}
?>