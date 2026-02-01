<?php
session_start();
include 'db_conn.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: LOGIN-REGISTER.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$message = "";
$message_type = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_info'])) {
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);

    $check = $conn->query("SELECT user_id FROM users WHERE email='$email' AND user_id != '$user_id'");
    if ($check->num_rows > 0) {
        $message = "Email is already taken by another user!";
        $message_type = "danger";
    } else {
        $sql = "UPDATE users SET username='$username', email='$email' WHERE user_id='$user_id'";
        if ($conn->query($sql)) {
            $_SESSION['username'] = $username;
            $message = "Profile updated successfully!";
            $message_type = "success";
        } else {
            $message = "Error updating profile.";
            $message_type = "danger";
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['change_password'])) {
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    $sql = "SELECT password FROM users WHERE user_id='$user_id'";
    $result = $conn->query($sql);
    $row = $result->fetch_assoc();

    if (!password_verify($current_password, $row['password'])) {
        $message = "Incorrect current password!";
        $message_type = "danger";
    } elseif ($new_password !== $confirm_password) {
        $message = "New passwords do not match!";
        $message_type = "danger";
    } elseif (strlen($new_password) < 6) {
        $message = "Password must be at least 6 characters!";
        $message_type = "danger";
    } else {
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        $update_pass = "UPDATE users SET password='$hashed_password' WHERE user_id='$user_id'";
        if ($conn->query($update_pass)) {
            $message = "Password changed successfully!";
            $message_type = "success";
        } else {
            $message = "Error changing password.";
            $message_type = "danger";
        }
    }
}

$sql_user = "SELECT * FROM users WHERE user_id='$user_id'";
$result_user = $conn->query($sql_user);
$user = $result_user->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Profile</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link rel="stylesheet" href="admin_style.css">
</head>
<body>

    <div class="d-flex" id="wrapper">
        <?php include 'admin_sidebar.php'; ?>

        <div id="page-content-wrapper">
            <nav class="navbar navbar-light border-bottom px-4 py-3 bg-white">
                <div class="d-flex align-items-center">
                    <button class="btn btn-light btn-sm me-3 border" id="sidebarToggle"><i class="bi bi-list fs-5"></i></button>
                    <h5 class="m-0 text-secondary">Admin Profile</h5>
                </div>
                
                <ul class="navbar-nav ms-auto flex-row align-items-center">
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <span class="fw-bold"><?php echo isset($_SESSION['username']) ? $_SESSION['username'] : 'Admin'; ?></span>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end shadow border-0 position-absolute">
                            <li><a class="dropdown-item active" href="admin_profile.php">Profile</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item text-danger" href="logout.php">Logout</a></li>
                        </ul>
                    </li>
                </ul>
            </nav>

            <div class="container-fluid p-4">
                
                <?php if($message): ?>
                    <div class="alert alert-<?php echo $message_type; ?> alert-dismissible fade show" role="alert">
                        <?php echo $message; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>

                <div class="row">
                    <div class="col-lg-6 mb-4">
                        <div class="card border-0 shadow-sm rounded-3">
                            <div class="card-header bg-white py-3 border-bottom">
                                <h5 class="m-0 fw-bold">Edit Profile</h5>
                            </div>
                            <div class="card-body">
                                <form method="POST" action="">
                                    <div class="mb-3">
                                        <label class="form-label text-muted small fw-bold">Username</label>
                                        <input type="text" name="username" class="form-control" value="<?php echo htmlspecialchars($user['username']); ?>" required>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label text-muted small fw-bold">Email Address</label>
                                        <input type="email" name="email" class="form-control" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                                    </div>
                                    <button type="submit" name="update_info" class="btn btn-dark">Update Info</button>
                                </form>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-6">
                        <div class="card border-0 shadow-sm rounded-3">
                            <div class="card-header bg-white py-3 border-bottom">
                                <h5 class="m-0 fw-bold">Change Password</h5>
                            </div>
                            <div class="card-body">
                                <form method="POST" action="">
                                    <div class="mb-3">
                                        <label class="form-label text-muted small fw-bold">Current Password</label>
                                        <input type="password" name="current_password" class="form-control" required>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label text-muted small fw-bold">New Password</label>
                                        <input type="password" name="new_password" class="form-control" required>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label text-muted small fw-bold">Confirm New Password</label>
                                        <input type="password" name="confirm_password" class="form-control" required>
                                    </div>
                                    <button type="submit" name="change_password" class="btn btn-danger text-white">Change Password</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="admin_script.js"></script>
</body>
</html>