<?php
session_start();
include 'db_conn.php';

header('Content-Type: application/json');

// 1. 检查登录
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit;
}

// 2. 检查是否有传 ID 进来
if (isset($_POST['cart_id'])) {
    $cart_id = intval($_POST['cart_id']);
    $user_id = $_SESSION['user_id'];

    // 3. 执行删除 (确保只能删自己的)
    $sql = "DELETE FROM cart WHERE cart_id = $cart_id AND user_id = $user_id";

    if ($conn->query($sql) === TRUE) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $conn->error]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'No Cart ID provided']);
}
?>