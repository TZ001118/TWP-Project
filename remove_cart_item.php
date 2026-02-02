<?php
session_start();
include 'db_conn.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 检查登录
    if (!isset($_SESSION['user_id'])) {
        echo "Error: Not logged in";
        exit;
    }

    // 获取要删除的购物车 ID
    $cart_id = intval($_POST['cart_id']);

    // --- 核心步骤：从数据库彻底删除 ---
    // 务必加上 user_id 检查，防止删错别人的东西
    $sql = "DELETE FROM cart WHERE id = $cart_id AND user_id = " . $_SESSION['user_id'];

    if ($conn->query($sql) === TRUE) {
        echo "Success";
    } else {
        echo "Error deleting record: " . $conn->error;
    }
}
?>