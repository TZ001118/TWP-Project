<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include 'db_conn.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = $_POST['password'];

    $username_parts = explode("@", $email);
    $username = $username_parts[0]; 

    $check_sql = "SELECT * FROM users WHERE email = '$email'";
    $result = $conn->query($check_sql);

    if ($result->num_rows > 0) {
        echo "<script>alert('Error: Email already registered!'); window.location='LOGIN-REGISTER.php';</script>";
    } else {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $role = 'customer';

        $sql = "INSERT INTO users (username, email, password, role) VALUES ('$username', '$email', '$hashed_password', '$role')";

        if ($conn->query($sql) === TRUE) {
            $_SESSION['user_id'] = $conn->insert_id;
            $_SESSION['username'] = $username;
            $_SESSION['role'] = $role;

            echo "<script>alert('Registration Successful!'); window.location='HOME.php';</script>";//Finish register will go
        } else {
            echo "<script>alert('Error: " . $conn->error . "'); window.location='LOGIN-REGISTER.php';</script>";
        }
    }
}
?>