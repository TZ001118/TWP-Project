<?php
session_start();
include 'db_conn.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') { header("Location: LOGIN-REGISTER.php"); exit(); }
if (!isset($_GET['id'])) { header("Location: admin_customers.php"); exit(); }

$user_id = mysqli_real_escape_string($conn, $_GET['id']);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['update_profile'])) {
        $email = mysqli_real_escape_string($conn, $_POST['email']);
        $status = mysqli_real_escape_string($conn, $_POST['account_status']);
        
        $sql = "UPDATE users SET email='$email' WHERE user_id='$user_id'";
        $conn->query($sql);
    }

    if (isset($_POST['reset_password'])) {
        $new_pass = $_POST['new_password'];
        
        if (strlen($new_pass) < 12 || !preg_match("/[0-9]/", $new_pass) || !preg_match("/[A-Z]/", $new_pass) || !preg_match("/[!@#$%^&*()]/", $new_pass)) {
            echo "<script>alert('Password does not meet 12-char strong requirements!');</script>";
        } else {
            $hashed = password_hash($new_pass, PASSWORD_DEFAULT);
            $conn->query("UPDATE users SET password='$hashed' WHERE user_id='$user_id'");
            echo "<script>alert('Password updated!');</script>";
        }
    }
}

$user = $conn->query("SELECT * FROM users WHERE user_id='$user_id'")->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Customer</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="admin_style.css">
</head>
<body class="p-4">
    <div class="container">
        <h3>Edit User: <?php echo $user['username']; ?></h3>
        <form method="POST" class="card p-4 mb-4">
            <div class="mb-3">
                <label>Email</label>
                <input type="email" name="email" class="form-control" value="<?php echo $user['email']; ?>">
            </div>
            <button type="submit" name="update_profile" class="btn btn-primary">Update Profile</button>
        </form>

        <form method="POST" class="card p-4 border-danger">
            <h5 class="text-danger">Reset Password</h5>
            <input type="password" name="new_password" class="form-control mb-3" placeholder="New Strong Password (12+ chars)">
            <button type="submit" name="reset_password" class="btn btn-danger">Confirm Reset</button>
        </form>
    </div>
</body>
</html>