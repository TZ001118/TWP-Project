<?php
session_start();
include 'db_conn.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: LOGIN-REGISTER.php");
    exit();
}

$sql_orders = "SELECT COUNT(DISTINCT order_id) as total_orders FROM orders"; 
$result_orders = $conn->query($sql_orders);
$total_orders = $result_orders->fetch_assoc()['total_orders'];

$sql_revenue = "SELECT SUM(total_amount) as total_revenue FROM orders WHERE status != 'Cancelled'";
$result_revenue = $conn->query($sql_revenue);
$total_revenue = $result_revenue->fetch_assoc()['total_revenue'] ?? 0;

$sql_users = "SELECT COUNT(*) as total_users FROM users WHERE role='customer'";
$result_users = $conn->query($sql_users);
$total_users = $result_users->fetch_assoc()['total_users'];

$sql_recent = "
    SELECT 
        o.order_id, 
        u.username as customer_name, 
        o.total_amount, 
        o.status, 
        o.order_date
    FROM orders o
    JOIN users u ON o.user_id = u.user_id
    ORDER BY o.order_date DESC 
    LIMIT 5
";
$recent_orders = $conn->query($sql_recent);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link rel="stylesheet" href="admin_style.css">
</head>
<body>
    <div class="d-flex" id="wrapper">
        <?php include 'admin_sidebar.php'; ?>
        <div id="page-content-wrapper">
            <div class="container-fluid p-4">
                <div class="row g-3 mb-5">
                    <div class="col-md-4">
                        <div class="card stat-card bg-custom-primary text-white p-3">
                            <p class="opacity-75">Total Orders</p>
                            <h3><?php echo $total_orders; ?></h3>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card stat-card bg-custom-success text-white p-3">
                            <p class="opacity-75">Total Revenue</p>
                            <h3>RM <?php echo number_format($total_revenue, 2); ?></h3>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card stat-card bg-custom-warning text-white p-3">
                            <p class="opacity-75">Total Customers</p>
                            <h3><?php echo $total_users; ?></h3>
                        </div>
                    </div>
                </div>

                <div class="card shadow-sm">
                    <div class="card-body">
                        <h5>Recent Orders</h5>
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Customer</th>
                                    <th>Total</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while($row = $recent_orders->fetch_assoc()): ?>
                                <tr>
                                    <td>#<?php echo $row['order_id']; ?></td>
                                    <td><?php echo $row['customer_name']; ?></td>
                                    <td>RM <?php echo number_format($row['total_amount'], 2); ?></td>
                                    <td><span class="badge bg-info"><?php echo $row['status']; ?></span></td>
                                    <td><a href="admin_order_details.php?id=<?php echo $row['order_id']; ?>" class="btn btn-sm btn-outline-primary">View</a></td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>