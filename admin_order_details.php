<?php
session_start();
include 'db_conn.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: LOGIN-REGISTER.php");
    exit();
}

if (!isset($_GET['id'])) {
    header("Location: admin_orders.php");
    exit();
}
$order_id = mysqli_real_escape_string($conn, $_GET['id']);

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_status'])) {
    $new_status = $_POST['status'];
    $update_sql = "UPDATE orders SET status = '$new_status' WHERE order_id = '$order_id'";
    if ($conn->query($update_sql)) {
        echo "<script>alert('Status Updated!');</script>";
    }
}

$sql = "
    SELECT o.*, u.username, u.email 
    FROM orders o 
    JOIN users u ON o.user_id = u.user_id 
    WHERE o.order_id = '$order_id'
";
$result = $conn->query($sql);
$order = $result->fetch_assoc();

if (!$order) {
    echo "Order not found.";
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Order Details #<?php echo $order_id; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="admin_style.css">
</head>
<body>
    <div class="d-flex" id="wrapper">
        <?php include 'admin_sidebar.php'; ?>

        <div id="page-content-wrapper">
            <div class="container-fluid p-4">
                <a href="admin_orders.php" class="btn btn-outline-secondary mb-3">&larr; Back to Orders</a>
                
                <div class="row">
                    <div class="col-md-8">
                        <div class="card shadow-sm mb-4">
                            <div class="card-header bg-white">
                                <h5 class="m-0">Order Information</h5>
                            </div>
                            <div class="card-body">
                                <p><strong>Order ID:</strong> #<?php echo $order['order_id']; ?></p>
                                <p><strong>Date:</strong> <?php echo $order['order_date']; ?></p>
                                <p><strong>Total Amount:</strong> RM <?php echo number_format($order['total_amount'], 2); ?></p>
                                <p><strong>Customer:</strong> <?php echo $order['username']; ?> (<?php echo $order['email']; ?>)</p>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="card shadow-sm">
                            <div class="card-header bg-white">
                                <h5 class="m-0">Update Status</h5>
                            </div>
                            <div class="card-body">
                                <form method="POST">
                                    <div class="mb-3">
                                        <label class="form-label">Current Status</label>
                                        <select name="status" class="form-select">
                                            <option value="Pending" <?php if($order['status']=='Pending') echo 'selected'; ?>>Pending</option>
                                            <option value="Processing" <?php if($order['status']=='Processing') echo 'selected'; ?>>Processing</option>
                                            <option value="Shipped" <?php if($order['status']=='Shipped') echo 'selected'; ?>>Shipped</option>
                                            <option value="Delivered" <?php if($order['status']=='Delivered') echo 'selected'; ?>>Delivered</option>
                                            <option value="Cancelled" <?php if($order['status']=='Cancelled') echo 'selected'; ?>>Cancelled</option>
                                        </select>
                                    </div>
                                    <button type="submit" name="update_status" class="btn btn-primary w-100">Update Status</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>