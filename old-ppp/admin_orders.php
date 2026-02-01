<?php
session_start();
include 'db_conn.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: LOGIN-REGISTER.php");
    exit();
}

$search = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : '';
$status_filter = isset($_GET['status']) ? $_GET['status'] : '';

$sql = "
    SELECT 
        o.order_id, 
        u.username as customer_name, 
        o.total_amount, 
        o.status, 
        o.order_date
    FROM orders o
    JOIN users u ON o.user_id = u.user_id
    WHERE 1=1
";

if (!empty($search)) {
    $sql .= " AND (u.username LIKE '%$search%' OR o.order_id LIKE '%$search%') ";
}

if (!empty($status_filter)) {
    $sql .= " AND o.status = '$status_filter' ";
}

$sql .= " ORDER BY o.order_date DESC";

$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Orders</title>
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
                    <h5 class="m-0 text-secondary">Manage Orders</h5>
                </div>
            </nav>

            <div class="container-fluid p-4">
                <div class="row mb-4">
                    <div class="col-md-12">
                        <form method="GET" action="admin_orders.php" class="d-flex gap-2">
                            <input type="text" name="search" class="form-control" placeholder="Search ID or Customer..." value="<?php echo htmlspecialchars($search); ?>" style="max-width: 300px;">
                            <select name="status" class="form-select" style="max-width: 150px;">
                                <option value="">All Status</option>
                                <option value="Pending" <?php if($status_filter == 'Pending') echo 'selected'; ?>>Pending</option>
                                <option value="Completed" <?php if($status_filter == 'Completed') echo 'selected'; ?>>Completed</option>
                                <option value="Cancelled" <?php if($status_filter == 'Cancelled') echo 'selected'; ?>>Cancelled</option>
                            </select>
                            <button type="submit" class="btn btn-dark">Filter</button>
                            <?php if(!empty($search) || !empty($status_filter)): ?>
                                <a href="admin_orders.php" class="btn btn-light border">Reset</a>
                            <?php endif; ?>
                        </form>
                    </div>
                </div>

                <div class="card shadow-sm border-0">
                    <div class="card-body p-0">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-4">Order ID</th>
                                    <th>Customer</th>
                                    <th>Total Price</th>
                                    <th>Date</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ($result && $result->num_rows > 0): ?>
                                    <?php while($row = $result->fetch_assoc()): 
                                        $badge_class = 'bg-secondary';
                                        if ($row['status'] == 'Completed') $badge_class = 'bg-success';
                                        if ($row['status'] == 'Pending') $badge_class = 'bg-warning text-dark';
                                        if ($row['status'] == 'Cancelled') $badge_class = 'bg-danger';
                                    ?>
                                    <tr>
                                        <td class="ps-4 fw-bold">#<?php echo $row['order_id']; ?></td>
                                        <td><?php echo $row['customer_name']; ?></td>
                                        <td class="fw-bold">RM <?php echo number_format($row['total_amount'], 2); ?></td>       
                                        <td class="text-muted small"><?php echo date("d M Y", strtotime($row['order_date'])); ?></td>
                                        <td><span class="badge <?php echo $badge_class; ?>"><?php echo $row['status']; ?></span></td>
                                        <td>
                                            <a href="admin_order_details.php?id=<?php echo $row['order_id']; ?>" class="btn btn-sm btn-outline-primary">View</a>
                                        </td>
                                    </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr><td colspan="6" class="text-center py-4 text-muted">No orders found.</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="admin_script.js"></script>
</body>
</html>