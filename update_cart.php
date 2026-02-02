<?php
session_start();
include 'db_conn.php';

// 检查是否是通过 POST 方法发送过来的数据
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 检查用户是否登录
    if (!isset($_SESSION['user_id'])) {
        echo "Error: Not logged in";
        exit;
    }

    // 获取前端发来的数据
    $cart_id = intval($_POST['cart_id']);
    $quantity = intval($_POST['quantity']);

    // 简单的数据验证
    if ($quantity < 1) {
        $quantity = 1;
    }

    // --- 核心步骤：更新数据库 ---
    $sql = "UPDATE cart SET quantity = $quantity WHERE id = $cart_id AND user_id = " . $_SESSION['user_id'];

    if ($conn->query($sql) === TRUE) {
        echo "Success";
    } else {
        echo "Error updating record: " . $conn->error;
    }
}
?>