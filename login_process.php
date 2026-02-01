<?php
include 'db_conn.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $identifier = mysqli_real_escape_string($conn, $_POST['identifier']);
    $password = $_POST['password'];

    $sql = "SELECT * FROM users WHERE email = '$identifier' OR username = '$identifier'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        
        if (password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];

            if ($user['role'] == 'admin') {
                echo "<script>window.location='admin_dashboard.php';</script>";
            } else {
                echo "<script>window.location='USER-DASHBOARD.php';</script>";
            }
            exit();
        } else {
            echo "<script>alert('Wrong password!'); window.location='LOGIN-REGISTER.php';</script>";
        }
    } else {
        echo "<script>alert('Account not found!'); window.location='LOGIN-REGISTER.php';</script>";
    }
}
?>