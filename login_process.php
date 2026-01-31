<?php
include 'db_conn.php';
session_start(); // 1. 开启储物柜

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $password = $_POST['password'];

    // 去数据库查询用户
    $sql = "SELECT * FROM users WHERE email = '$email' AND password = '$password'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        
        // 2. 将登录信息存入储物柜
        $_SESSION['user_id'] = $user['user_id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['role'] = $user['role'];

        // 3. 登录成功，跳回首页
        header("Location: HOME.php");
        exit();
    } else {
        echo "Invalid account!";
    }
}
?>