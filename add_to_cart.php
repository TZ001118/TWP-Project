<?php
session_start();
include 'db_conn.php';

// 1. 检查是否登录
if (!isset($_SESSION['user_id'])) {
    echo "<script>alert('Please login to add items to cart.'); window.location.href='LOGIN-REGISTER.php';</script>";
    exit;
}

$user_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 2. 获取数据
    $product_id = intval($_POST['product_id']);
    $quantity = isset($_POST['quantity']) ? intval($_POST['quantity']) : 1;

    // 3. 检查购物车里是否已经有这个商品
    $check_sql = "SELECT * FROM cart WHERE user_id = '$user_id' AND product_id = '$product_id'";
    $check_result = $conn->query($check_sql);

    if ($check_result->num_rows > 0) {
        // A. 如果有，就增加数量
        $sql = "UPDATE cart SET quantity = quantity + $quantity WHERE user_id = '$user_id' AND product_id = '$product_id'";
    } else {
        // B. 如果没有，就插入新记录
        $sql = "INSERT INTO cart (user_id, product_id, quantity) VALUES ('$user_id', '$product_id', '$quantity')";
    }

    if ($conn->query($sql) === TRUE) {
        $_SESSION['swal'] = [
            'type' => 'success',
            'title' => 'Added to Cart!',
            'text' => 'Item added to cart successfully.'
        ];
        
        // 跳回上一页
        $referer = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : 'HOME.php';
        header("Location: $referer");
        exit;
    } else {
        echo "Error: " . $conn->error;
    }
} else {
    // 如果不是 POST 请求，跳回首页
    header("Location: HOME.php");
}
?>