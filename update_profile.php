<?php
session_start();
include 'db_conn.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: LOGIN-REGISTER.php");
    exit;
}

$user_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $conn->real_escape_string($_POST['username']);
    $phone = $conn->real_escape_string($_POST['phone']);
    
    // 接收新的详细地址字段
    $street_1 = $conn->real_escape_string($_POST['street_1']);
    $street_2 = $conn->real_escape_string($_POST['street_2']);
    $city = $conn->real_escape_string($_POST['city']);
    $state = $conn->real_escape_string($_POST['state']);
    $postcode = $conn->real_escape_string($_POST['postcode']);
    
    // 为了兼容旧逻辑，我们也拼一个完整的 address 字符串存进旧字段 (可选)
    $full_address = "$street_1, $street_2, $city, $postcode, $state";

    // 更新数据库
    $sql = "UPDATE users SET 
            username = '$username', 
            phone = '$phone',
            street_1 = '$street_1',
            street_2 = '$street_2',
            city = '$city',
            state = '$state',
            postcode = '$postcode',
            address = '$full_address' 
            WHERE user_id = '$user_id'";

    if ($conn->query($sql) === TRUE) {
        // 更新成功，跳回 Dashboard 并带上成功参数
        header("Location: USER-DASHBOARD.php?success=1");
        exit;
    } else {
        echo "Error updating record: " . $conn->error;
    }
}
?>