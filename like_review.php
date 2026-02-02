<?php
session_start();
include 'db_conn.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || !isset($_POST['review_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit;
}

$user_id = $_SESSION['user_id'];
$review_id = intval($_POST['review_id']);

// 1. 检查是否已经点过赞
$check_sql = "SELECT id FROM review_likes WHERE user_id = $user_id AND review_id = $review_id";
$check_result = $conn->query($check_sql);

if ($check_result->num_rows > 0) {
    // 2. 如果点过，就取消点赞 (删除记录)
    $conn->query("DELETE FROM review_likes WHERE user_id = $user_id AND review_id = $review_id");
    $action = 'unliked';
} else {
    // 3. 没点过，就添加点赞
    $conn->query("INSERT INTO review_likes (user_id, review_id) VALUES ($user_id, $review_id)");
    $action = 'liked';
}

// 4. 重新计算该评论的总赞数
$count_sql = "SELECT COUNT(*) as total FROM review_likes WHERE review_id = $review_id";
$count_res = $conn->query($count_sql);
$row = $count_res->fetch_assoc();
$new_count = $row['total'];

echo json_encode(['success' => true, 'action' => $action, 'new_count' => $new_count]);
?>